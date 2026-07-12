<?php

namespace App\Domain\Moderation\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\Moderation\Services\ReportTargetRegistry;
use App\Enums\ContentRestrictionType;
use App\Enums\ModerationActionType;
use App\Enums\ModerationAssignmentStatus;
use App\Enums\ModerationCaseStatus;
use App\Enums\PermissionName;
use App\Enums\ReportStatus;
use App\Enums\RestrictionScope;
use App\Enums\RestrictionStatus;
use App\Enums\UserRestrictionType;
use App\Events\ContentRestrictionApplied;
use App\Events\ContentRestrictionLifted;
use App\Events\ModerationActionApplied;
use App\Events\ModerationCaseAssigned;
use App\Events\ReportClosed;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Events\UserRestrictionApplied;
use App\Events\UserRestrictionLifted;
use App\Models\ContentRestriction;
use App\Models\ModerationAction;
use App\Models\ModerationCase;
use App\Models\ModerationCaseAssignment;
use App\Models\Report;
use App\Models\User;
use App\Models\UserRestriction;
use App\Support\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageModerationCases
{
    public function __construct(private readonly ReportTargetRegistry $targets, private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function open(User $actor, array $data): ModerationCase
    {
        $target = $this->targets->resolve($data['target_type'], (int) $data['target_id']);

        return DB::transaction(function () use ($actor, $data, $target): ModerationCase {
            $case = ModerationCase::query()->create(['public_id' => (string) Str::ulid(), 'target_type' => $target->getMorphClass(), 'target_id' => $target->getKey(), 'subject_user_id' => $data['subject_user_id'] ?? ($target instanceof User ? $target->id : null), 'status' => ModerationCaseStatus::Open, 'priority' => $data['priority'], 'opened_by_user_id' => $actor->id, 'opened_at' => now(), 'safe_metadata' => [], 'lock_version' => 0]);

            $reportIds = array_values(array_unique(array_map('intval', $data['report_ids'] ?? [])));
            if ($reportIds !== []) {
                $reports = Report::query()->whereIn('id', $reportIds)->lockForUpdate()->get();
                if ($reports->count() !== count($reportIds) || $reports->contains(fn (Report $report): bool => $report->target_type !== $case->target_type || $report->target_id !== $case->target_id)) {
                    throw new InvalidModerationOperation('All linked reports must exist and share the case target.', 'invalid_case_reports');
                }
                Report::query()->whereIn('id', $reportIds)->update(['moderation_case_id' => $case->id, 'status' => ReportStatus::Triaged->value]);
            }

            $this->auditLogger->record('moderation.case_opened', $case, ['target_type' => $case->target_type, 'report_count' => count($reportIds)], $actor);

            return $case->fresh(['reports.category', 'assignments']);
        });
    }

    public function assign(ModerationCase $case, User $assignee, User $actor, ?string $note = null): ModerationCaseAssignment
    {
        if ($case->subject_user_id === $assignee->id || $case->reports()->where('reporter_user_id', $assignee->id)->exists()) {
            throw new InvalidModerationOperation('A conflicted user cannot be assigned to this case.', 'moderation_assignment_conflict');
        }
        if (! $assignee->hasPermission(PermissionName::ModerationCasesInvestigate)) {
            throw new InvalidModerationOperation('The assignee lacks the required moderation permission.', 'moderation_assignee_unqualified');
        }
        if ($case->reports()->whereHas('category', fn ($query) => $query->where('rights_review_required', true))->exists() && ! $assignee->hasPermission(PermissionName::EditorialRightsReview)) {
            throw new InvalidModerationOperation('Rights-review cases require rights authority.', 'rights_authority_required');
        }

        return DB::transaction(function () use ($case, $assignee, $actor, $note): ModerationCaseAssignment {
            ModerationCaseAssignment::query()->where('moderation_case_id', $case->id)->where('active_primary_key', 'primary')->update(['status' => ModerationAssignmentStatus::Cancelled->value, 'active_primary_key' => null, 'cancelled_at' => now()]);
            $assignment = ModerationCaseAssignment::query()->create(['moderation_case_id' => $case->id, 'moderator_user_id' => $assignee->id, 'assigned_by_user_id' => $actor->id, 'role' => 'primary', 'status' => ModerationAssignmentStatus::Assigned, 'active_primary_key' => 'primary', 'assigned_at' => now(), 'private_note' => $note === null ? null : trim(strip_tags($note))]);
            $this->auditLogger->record('moderation.case_assigned', $case, ['assignment_id' => $assignment->id, 'moderator_user_id' => $assignee->id], $actor);
            ModerationCaseAssigned::dispatch($case->id, $assignment->id, $assignee->id);

            return $assignment;
        });
    }

    /** @param array<string, mixed> $data */
    public function transition(ModerationCase $case, ModerationCaseStatus $status, int $expectedVersion, User $actor, array $data = []): ModerationCase
    {
        return DB::transaction(function () use ($case, $status, $expectedVersion, $actor, $data): ModerationCase {
            $locked = ModerationCase::query()->lockForUpdate()->findOrFail($case->id);
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }
            $allowed = match ($status) {
                ModerationCaseStatus::Triaged => $locked->status === ModerationCaseStatus::Open,
                ModerationCaseStatus::Investigating => in_array($locked->status, [ModerationCaseStatus::Open, ModerationCaseStatus::Triaged, ModerationCaseStatus::AwaitingInformation], true),
                ModerationCaseStatus::AwaitingInformation => $locked->status === ModerationCaseStatus::Investigating,
                ModerationCaseStatus::Dismissed, ModerationCaseStatus::Actioned => in_array($locked->status, [ModerationCaseStatus::Triaged, ModerationCaseStatus::Investigating, ModerationCaseStatus::AwaitingInformation], true),
                ModerationCaseStatus::Closed => in_array($locked->status, [ModerationCaseStatus::Actioned, ModerationCaseStatus::Dismissed], true),
                ModerationCaseStatus::Open => in_array($locked->status, [ModerationCaseStatus::Closed, ModerationCaseStatus::Dismissed], true) && $actor->hasPermission(PermissionName::ModerationCasesReopen),
            };
            if (! $allowed) {
                throw new InvalidModerationOperation('The requested case transition is not valid.', 'invalid_case_transition');
            }
            if (in_array($status, [ModerationCaseStatus::Dismissed, ModerationCaseStatus::Closed], true) && empty($data['resolution_code'])) {
                throw new InvalidModerationOperation('Closing or dismissing a case requires a resolution.', 'case_resolution_required');
            }

            $locked->update(['status' => $status, 'triaged_at' => $status === ModerationCaseStatus::Triaged ? now() : $locked->triaged_at, 'investigation_started_at' => $status === ModerationCaseStatus::Investigating ? now() : $locked->investigation_started_at, 'decision_at' => in_array($status, [ModerationCaseStatus::Actioned, ModerationCaseStatus::Dismissed], true) ? now() : $locked->decision_at, 'closed_at' => $status === ModerationCaseStatus::Closed ? now() : ($status === ModerationCaseStatus::Open ? null : $locked->closed_at), 'resolution_code' => $data['resolution_code'] ?? $locked->resolution_code, 'user_visible_summary' => isset($data['user_visible_summary']) ? trim(strip_tags((string) $data['user_visible_summary'])) : $locked->user_visible_summary, 'private_internal_summary' => $data['private_internal_summary'] ?? $locked->private_internal_summary, 'lock_version' => $expectedVersion + 1]);
            if ($status === ModerationCaseStatus::Closed) {
                $reports = $locked->reports()->whereNotNull('reporter_user_id')->whereNotIn('status', [ReportStatus::Withdrawn->value, ReportStatus::Closed->value])->get();
                $locked->reports()->whereIn('id', $reports->pluck('id'))->update(['status' => ReportStatus::Closed->value, 'closed_at' => now()]);
                foreach ($reports as $report) {
                    ReportClosed::dispatch($report->id, (int) $report->reporter_user_id, (string) $locked->resolution_code);
                }
            }
            $this->auditLogger->record('moderation.case_'.str($status->value)->replace('_', '_')->toString(), $locked, ['previous_status' => $case->status->value, 'new_status' => $status->value, 'lock_version' => $locked->lock_version], $actor);

            return $locked->fresh(['reports.category', 'assignments']);
        });
    }

    /** @param array<string, mixed> $data */
    public function applyAction(ModerationCase $case, User $actor, array $data): ModerationAction
    {
        return DB::transaction(function () use ($case, $actor, $data): ModerationAction {
            $type = ModerationActionType::from($data['type']);
            $targetUserId = isset($data['target_user_id']) ? (int) $data['target_user_id'] : $case->subject_user_id;
            $targetContent = isset($data['target_type'], $data['target_id']) ? $this->targets->resolve($data['target_type'], (int) $data['target_id']) : null;
            $expiresAt = isset($data['expires_at']) ? CarbonImmutable::parse($data['expires_at']) : null;
            if ($expiresAt === null && in_array($type, [ModerationActionType::UserRestricted, ModerationActionType::PlatformSuspended], true) && ! $actor->hasPermission(PermissionName::ModerationPermanentRestrictionsApply)) {
                throw new InvalidModerationOperation('Permanent restrictions require stronger authority.', 'permanent_restriction_forbidden');
            }

            $action = ModerationAction::query()->create(['moderation_case_id' => $case->id, 'actor_user_id' => $actor->id, 'type' => $type, 'target_user_id' => $targetUserId, 'target_content_type' => $targetContent?->getMorphClass(), 'target_content_id' => $targetContent?->getKey(), 'reason_code' => $data['reason_code'], 'user_visible_explanation' => trim(strip_tags($data['user_visible_explanation'])), 'private_moderator_note' => $data['private_moderator_note'] ?? null, 'effective_at' => now(), 'expires_at' => $expiresAt, 'supersedes_action_id' => $data['supersedes_action_id'] ?? null, 'safe_metadata' => []]);

            if (in_array($type, [ModerationActionType::UserRestricted, ModerationActionType::PlatformSuspended], true)) {
                if ($targetUserId === null || ! $actor->hasPermission(PermissionName::ModerationUserRestrictionsApply)) {
                    throw new InvalidModerationOperation('A permitted target user is required.', 'user_restriction_forbidden');
                }
                $scopes = $type === ModerationActionType::PlatformSuspended ? [RestrictionScope::PlatformAccess] : array_map(fn (string $scope): RestrictionScope => RestrictionScope::from($scope), $data['restriction_scopes'] ?? []);
                if ($scopes === []) {
                    throw new InvalidModerationOperation('At least one restriction scope is required.', 'restriction_scope_required');
                }
                $restriction = UserRestriction::query()->create(['user_id' => $targetUserId, 'moderation_action_id' => $action->id, 'type' => $type === ModerationActionType::PlatformSuspended ? UserRestrictionType::PlatformAccess : UserRestrictionType::Capability, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => $expiresAt, 'user_visible_reason' => $action->user_visible_explanation]);
                foreach ($scopes as $scope) {
                    $restriction->scopes()->create(['scope' => $scope]);
                }
                UserRestrictionApplied::dispatch($restriction->id, $targetUserId, $action->id);
                $this->auditLogger->record('moderation.user_restriction_applied', $restriction, ['action_id' => $action->id, 'scopes' => array_map(fn (RestrictionScope $scope): string => $scope->value, $scopes)], $actor);
            }

            if (in_array($type, [ModerationActionType::ContentHidden, ModerationActionType::ContentEditingFrozen, ModerationActionType::MediaRestricted, ModerationActionType::TakedownApplied], true)) {
                if (! $targetContent instanceof Model || ! $actor->hasPermission(PermissionName::ModerationContentRestrictionsApply)) {
                    throw new InvalidModerationOperation('A permitted content target is required.', 'content_restriction_forbidden');
                }
                $restrictionType = isset($data['content_restriction_type']) ? ContentRestrictionType::from($data['content_restriction_type']) : match ($type) {
                    ModerationActionType::ContentHidden => ContentRestrictionType::HiddenFromPublic, ModerationActionType::ContentEditingFrozen => ContentRestrictionType::EditingFrozen, ModerationActionType::TakedownApplied => ContentRestrictionType::TakedownRestricted, default => ContentRestrictionType::HiddenFromPublic
                };
                if (in_array($restrictionType, [ContentRestrictionType::TakedownRestricted, ContentRestrictionType::RightsReviewRequired], true) && ! $actor->hasPermission(PermissionName::EditorialRightsReview)) {
                    throw new InvalidModerationOperation('This restriction requires rights-review authority.', 'rights_authority_required');
                }
                $restriction = ContentRestriction::query()->create(['target_type' => $targetContent->getMorphClass(), 'target_id' => $targetContent->getKey(), 'moderation_action_id' => $action->id, 'type' => $restrictionType, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => $expiresAt, 'reason_code' => $action->reason_code, 'public_explanation' => $action->user_visible_explanation]);
                ContentRestrictionApplied::dispatch($restriction->id, $targetContent->getMorphClass(), (int) $targetContent->getKey(), $action->id);
                SearchProjectionRemovalRequested::dispatch($targetContent->getMorphClass(), (int) $targetContent->getKey());
                $this->auditLogger->record('moderation.content_restriction_applied', $restriction, ['action_id' => $action->id, 'type' => $restrictionType->value], $actor);
            }

            $case->update(['status' => ModerationCaseStatus::Actioned, 'decision_at' => now(), 'lock_version' => $case->lock_version + 1]);
            $this->auditLogger->record('moderation.action_applied', $action, ['case_id' => $case->id, 'type' => $type->value, 'target_user_id' => $targetUserId, 'target_type' => $targetContent?->getMorphClass()], $actor);
            ModerationActionApplied::dispatch($action->id, $case->id, $targetUserId);

            return $action;
        });
    }

    public function liftUserRestriction(UserRestriction $restriction, User $actor): UserRestriction
    {
        $restriction->update(['status' => RestrictionStatus::Lifted, 'lifted_at' => now(), 'lifted_by_user_id' => $actor->id]);
        $this->auditLogger->record('moderation.user_restriction_lifted', $restriction, ['action_id' => $restriction->moderation_action_id], $actor);
        UserRestrictionLifted::dispatch($restriction->id, $restriction->user_id, $restriction->moderation_action_id);

        return $restriction;
    }

    public function liftContentRestriction(ContentRestriction $restriction, User $actor): ContentRestriction
    {
        $restriction->update(['status' => RestrictionStatus::Lifted, 'lifted_at' => now(), 'lifted_by_user_id' => $actor->id]);
        $this->auditLogger->record('moderation.content_restriction_lifted', $restriction, ['action_id' => $restriction->moderation_action_id, 'type' => $restriction->type->value], $actor);
        ContentRestrictionLifted::dispatch($restriction->id, $restriction->target_type, $restriction->target_id, $restriction->moderation_action_id);
        SearchProjectionRequested::dispatch($restriction->target_type, $restriction->target_id);

        return $restriction;
    }
}

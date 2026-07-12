<?php

namespace App\Domain\Moderation\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\AppealDecisionType;
use App\Enums\AppealStatus;
use App\Enums\ModerationActionType;
use App\Enums\RestrictionStatus;
use App\Events\AppealDecided;
use App\Events\AppealSubmitted;
use App\Events\ContentRestrictionLifted;
use App\Events\SearchProjectionRequested;
use App\Events\UserRestrictionLifted;
use App\Models\Appeal;
use App\Models\AppealDecision;
use App\Models\ContentRestriction;
use App\Models\ModerationAction;
use App\Models\User;
use App\Models\UserRestriction;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class ManageAppeals
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function submit(User $appellant, ModerationAction $action, string $explanation): Appeal
    {
        if ($action->target_user_id !== $appellant->id || in_array($action->type, [ModerationActionType::NoAction, ModerationActionType::CaseDismissed], true)) {
            throw new InvalidModerationOperation('This moderation action is not eligible for appeal by this user.', 'appeal_ineligible');
        }
        if ($action->created_at?->lt(now()->subDays((int) config('moderation.appeal_window_days', 30)))) {
            throw new InvalidModerationOperation('The appeal window has closed.', 'appeal_window_closed');
        }

        return DB::transaction(function () use ($appellant, $action, $explanation): Appeal {
            if (Appeal::query()->where('appellant_user_id', $appellant->id)->where('moderation_action_id', $action->id)->where('active_key', 'active')->exists()) {
                throw new InvalidModerationOperation('An active appeal already exists for this action.', 'duplicate_active_appeal');
            }
            $appeal = Appeal::query()->create(['appellant_user_id' => $appellant->id, 'moderation_case_id' => $action->moderation_case_id, 'moderation_action_id' => $action->id, 'user_restriction_id' => $action->hasOne(UserRestriction::class)->value('id'), 'content_restriction_id' => $action->hasOne(ContentRestriction::class)->value('id'), 'status' => AppealStatus::Submitted, 'active_key' => 'active', 'explanation' => trim(strip_tags($explanation)), 'submitted_at' => now(), 'lock_version' => 0]);
            $this->auditLogger->record('moderation.appeal_submitted', $appeal, ['action_id' => $action->id, 'case_id' => $action->moderation_case_id], $appellant);
            AppealSubmitted::dispatch($appeal->id, $appellant->id, $action->id);

            return $appeal;
        });
    }

    public function withdraw(Appeal $appeal, User $appellant, int $expectedVersion): Appeal
    {
        if ($appeal->appellant_user_id !== $appellant->id || $appeal->status !== AppealStatus::Submitted) {
            throw new InvalidModerationOperation('This appeal can no longer be withdrawn.', 'appeal_not_withdrawable');
        }
        if ($appeal->lock_version !== $expectedVersion) {
            throw new OptimisticLockConflict;
        }
        $appeal->update(['status' => AppealStatus::Withdrawn, 'active_key' => null, 'withdrawn_at' => now(), 'lock_version' => $expectedVersion + 1]);
        $this->auditLogger->record('moderation.appeal_withdrawn', $appeal, ['action_id' => $appeal->moderation_action_id], $appellant);

        return $appeal;
    }

    /** @param array<string, mixed> $data */
    public function decide(Appeal $appeal, User $reviewer, array $data): AppealDecision
    {
        $action = $appeal->moderationAction;
        if ($appeal->appellant_user_id === $reviewer->id || $action->actor_user_id === $reviewer->id || $action->target_user_id === $reviewer->id) {
            throw new InvalidModerationOperation('The appeal reviewer has a conflict of interest.', 'appeal_reviewer_conflict');
        }

        return DB::transaction(function () use ($appeal, $reviewer, $data): AppealDecision {
            $locked = Appeal::query()->lockForUpdate()->findOrFail($appeal->id);
            if ($locked->status !== AppealStatus::Submitted || $locked->decision()->exists()) {
                throw new InvalidModerationOperation('This appeal has already been decided.', 'appeal_already_decided');
            }
            $type = AppealDecisionType::from($data['type']);
            $decision = AppealDecision::query()->create(['appeal_id' => $locked->id, 'reviewer_user_id' => $reviewer->id, 'type' => $type, 'user_visible_explanation' => trim(strip_tags($data['user_visible_explanation'])), 'private_reviewer_note' => $data['private_reviewer_note'] ?? null, 'replacement_action_id' => $data['replacement_action_id'] ?? null, 'decided_at' => now()]);

            if (in_array($type, [AppealDecisionType::Modified, AppealDecisionType::Overturned], true)) {
                if ($locked->user_restriction_id !== null) {
                    $restriction = $locked->belongsTo(UserRestriction::class, 'user_restriction_id')->first();
                    if ($restriction !== null && $restriction->status === RestrictionStatus::Active) {
                        $restriction->update(['status' => RestrictionStatus::Lifted, 'lifted_at' => now(), 'lifted_by_user_id' => $reviewer->id]);
                        UserRestrictionLifted::dispatch($restriction->id, $restriction->user_id, $restriction->moderation_action_id);
                    }
                }
                if ($locked->content_restriction_id !== null) {
                    $restriction = $locked->belongsTo(ContentRestriction::class, 'content_restriction_id')->first();
                    if ($restriction !== null && $restriction->status === RestrictionStatus::Active) {
                        $restriction->update(['status' => RestrictionStatus::Lifted, 'lifted_at' => now(), 'lifted_by_user_id' => $reviewer->id]);
                        ContentRestrictionLifted::dispatch($restriction->id, $restriction->target_type, $restriction->target_id, $restriction->moderation_action_id);
                        SearchProjectionRequested::dispatch($restriction->target_type, $restriction->target_id);
                    }
                }
            }

            $locked->update(['status' => AppealStatus::Decided, 'active_key' => null, 'decided_at' => now(), 'lock_version' => $locked->lock_version + 1]);
            $this->auditLogger->record('moderation.appeal_decided', $locked, ['action_id' => $locked->moderation_action_id, 'decision' => $type->value, 'decision_id' => $decision->id], $reviewer);
            AppealDecided::dispatch($locked->id, $locked->appellant_user_id, $decision->id, $type->value);

            return $decision;
        });
    }
}

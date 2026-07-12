<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Services\EditorialEvidenceService;
use App\Enums\EditorialActionType;
use App\Enums\EditorialRevisionStatus;
use App\Enums\ReviewAssignmentStatus;
use App\Enums\ReviewCheckResult;
use App\Events\EditorialRevisionApproved;
use App\Models\EditorialRevision;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class DecideEditorialRevision
{
    public function __construct(
        private readonly EditorialEvidenceService $evidence,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function beginReview(EditorialRevision $revision, User $reviewer): EditorialRevision
    {
        $this->ensureReviewer($revision, $reviewer);
        if ($revision->status !== EditorialRevisionStatus::Submitted) {
            throw new InvalidEditorialOperation('Only a submitted revision may enter review.');
        }

        return DB::transaction(function () use ($revision, $reviewer): EditorialRevision {
            $revision->update(['status' => EditorialRevisionStatus::UnderReview, 'review_started_at' => now()]);
            $revision->assignments()->where('reviewer_user_id', $reviewer->id)->whereNotNull('active_primary_key')->update([
                'status' => ReviewAssignmentStatus::Accepted,
                'accepted_at' => now(),
            ]);
            $revision->actions()->create(['actor_user_id' => $reviewer->id, 'type' => EditorialActionType::ReviewStarted, 'acted_at' => now()]);
            $this->auditLogger->record('editorial.review_started', $revision, [], $reviewer);

            return $revision->fresh();
        });
    }

    /** @param array<string, mixed> $findings */
    public function requestChanges(EditorialRevision $revision, User $reviewer, string $explanation, ?string $privateNote = null, array $findings = []): EditorialRevision
    {
        return $this->decide($revision, $reviewer, EditorialRevisionStatus::ChangesRequested, EditorialActionType::ChangesRequested, $explanation, $privateNote, $findings);
    }

    /** @param array<string, mixed> $findings */
    public function reject(EditorialRevision $revision, User $reviewer, string $explanation, ?string $privateNote = null, array $findings = []): EditorialRevision
    {
        return $this->decide($revision, $reviewer, EditorialRevisionStatus::Rejected, EditorialActionType::Rejected, $explanation, $privateNote, $findings);
    }

    /** @param array<string, mixed> $findings */
    public function approve(EditorialRevision $revision, User $reviewer, string $explanation, ?string $privateNote = null, array $findings = []): EditorialRevision
    {
        $source = $this->evidence->sourceResult($revision);
        $rights = $this->evidence->rightsResult($revision);
        $spoiler = $this->evidence->spoilerResult($revision);

        foreach (['source' => $source, 'rights' => $rights, 'spoiler' => $spoiler] as $check => $result) {
            if ($result === ReviewCheckResult::Failed) {
                throw new InvalidEditorialOperation("The {$check} requirements are incomplete.", 'editorial_checks_incomplete');
            }
        }

        $approved = $this->decide(
            $revision,
            $reviewer,
            EditorialRevisionStatus::Approved,
            EditorialActionType::Approved,
            $explanation,
            $privateNote,
            $findings,
            $source,
            $rights,
            $spoiler,
        );
        EditorialRevisionApproved::dispatch($approved->id, $reviewer->id);

        return $approved;
    }

    /**
     * @param  array<string, mixed>  $findings
     */
    private function decide(
        EditorialRevision $revision,
        User $reviewer,
        EditorialRevisionStatus $status,
        EditorialActionType $type,
        string $explanation,
        ?string $privateNote,
        array $findings,
        ReviewCheckResult $source = ReviewCheckResult::NotRequired,
        ReviewCheckResult $rights = ReviewCheckResult::NotRequired,
        ReviewCheckResult $spoiler = ReviewCheckResult::NotRequired,
    ): EditorialRevision {
        $this->ensureReviewer($revision, $reviewer);
        if (! in_array($revision->status, [EditorialRevisionStatus::Submitted, EditorialRevisionStatus::UnderReview], true)) {
            throw new InvalidEditorialOperation('This revision is not awaiting a review decision.');
        }

        return DB::transaction(function () use ($revision, $reviewer, $status, $type, $explanation, $privateNote, $findings, $source, $rights, $spoiler): EditorialRevision {
            $locked = EditorialRevision::query()->lockForUpdate()->findOrFail($revision->id);
            $locked->update(['status' => $status, 'decided_at' => now()]);
            $locked->actions()->create([
                'actor_user_id' => $reviewer->id,
                'type' => $type,
                'public_explanation' => trim($explanation),
                'private_note' => $privateNote,
                'source_result' => $source,
                'rights_result' => $rights,
                'spoiler_result' => $spoiler,
                'findings' => $findings,
                'acted_at' => now(),
            ]);
            $locked->assignments()->where('reviewer_user_id', $reviewer->id)->whereNotNull('active_primary_key')->update([
                'status' => ReviewAssignmentStatus::Completed,
                'active_primary_key' => null,
                'completed_at' => now(),
            ]);
            $this->auditLogger->record('editorial.revision_'.$type->value, $locked, [
                'status' => $status->value,
                'source_result' => $source->value,
                'rights_result' => $rights->value,
                'spoiler_result' => $spoiler->value,
            ], $reviewer);

            return $locked->fresh(['actions', 'assignments']);
        });
    }

    private function ensureReviewer(EditorialRevision $revision, User $reviewer): void
    {
        if ($revision->author_user_id === $reviewer->id) {
            throw new InvalidEditorialOperation('An author cannot review their own revision.', 'self_review_forbidden');
        }

        if (! $revision->assignments()->where('reviewer_user_id', $reviewer->id)->whereNotNull('active_primary_key')->exists()) {
            throw new InvalidEditorialOperation('An active review assignment is required.', 'review_assignment_required');
        }
    }
}

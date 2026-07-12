<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\EditorialActionType;
use App\Enums\EditorialRevisionStatus;
use App\Enums\PermissionName;
use App\Enums\ReviewAssignmentStatus;
use App\Models\EditorialRevision;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class AssignEditorialReview
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(EditorialRevision $revision, User $reviewer, User $assignedBy, ?string $note = null, ?string $dueAt = null): ReviewAssignment
    {
        if (! $reviewer->hasPermission(PermissionName::EditorialRevisionsReview)) {
            throw new InvalidEditorialOperation('The selected user does not have editorial review permission.', 'invalid_reviewer');
        }
        if ($reviewer->is($revision->author)) {
            throw new InvalidEditorialOperation('An author cannot review their own revision.', 'self_review_forbidden');
        }
        if (! in_array($revision->status, [EditorialRevisionStatus::Submitted, EditorialRevisionStatus::UnderReview], true)) {
            throw new InvalidEditorialOperation('Only submitted or under-review revisions may be assigned.');
        }

        return DB::transaction(function () use ($revision, $reviewer, $assignedBy, $note, $dueAt): ReviewAssignment {
            $locked = EditorialRevision::query()->lockForUpdate()->findOrFail($revision->id);
            $existing = $locked->assignments()->whereNotNull('active_primary_key')->lockForUpdate()->first();
            if ($existing !== null) {
                $existing->update([
                    'status' => ReviewAssignmentStatus::Cancelled,
                    'active_primary_key' => null,
                    'cancelled_at' => now(),
                ]);
            }

            $assignment = $locked->assignments()->create([
                'reviewer_user_id' => $reviewer->id,
                'assigned_by_user_id' => $assignedBy->id,
                'status' => ReviewAssignmentStatus::Assigned,
                'is_primary' => true,
                'active_primary_key' => 'primary',
                'assigned_at' => now(),
                'due_at' => $dueAt,
                'internal_note' => $note,
            ]);
            $locked->actions()->create(['actor_user_id' => $assignedBy->id, 'type' => EditorialActionType::Assigned, 'acted_at' => now()]);
            $this->auditLogger->record($existing === null ? 'editorial.reviewer_assigned' : 'editorial.reviewer_reassigned', $locked, [
                'reviewer_user_id' => $reviewer->id,
                'assignment_id' => $assignment->id,
            ], $assignedBy);

            return $assignment;
        });
    }

    public function cancel(ReviewAssignment $assignment, User $actor): ReviewAssignment
    {
        if (! $assignment->status->isActive()) {
            throw new InvalidEditorialOperation('The review assignment is not active.');
        }

        return DB::transaction(function () use ($assignment, $actor): ReviewAssignment {
            $assignment->update(['status' => ReviewAssignmentStatus::Cancelled, 'active_primary_key' => null, 'cancelled_at' => now()]);
            $assignment->revision->actions()->create(['actor_user_id' => $actor->id, 'type' => EditorialActionType::AssignmentCancelled, 'acted_at' => now()]);
            $this->auditLogger->record('editorial.assignment_cancelled', $assignment->revision, ['assignment_id' => $assignment->id], $actor);

            return $assignment->fresh();
        });
    }
}

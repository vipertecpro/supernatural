<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\EditorialActionType;
use App\Enums\EditorialRevisionStatus;
use App\Events\EditorialRevisionSubmitted;
use App\Models\EditorialRevision;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class TransitionEditorialRevision
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function submit(EditorialRevision $revision, User $actor): EditorialRevision
    {
        if ($revision->status !== EditorialRevisionStatus::Draft || ! $revision->items()->exists() && ! $revision->blocks()->exists()) {
            throw new InvalidEditorialOperation('A draft revision with at least one change is required for submission.');
        }

        return $this->transition($revision, $actor, EditorialRevisionStatus::Submitted, EditorialActionType::Submitted, ['submitted_at' => now()]);
    }

    public function resubmit(EditorialRevision $revision, User $actor): EditorialRevision
    {
        if ($revision->status !== EditorialRevisionStatus::ChangesRequested) {
            throw new InvalidEditorialOperation('Only a changes-requested revision may be resubmitted.');
        }

        return $this->transition($revision, $actor, EditorialRevisionStatus::Submitted, EditorialActionType::Resubmitted, ['submitted_at' => now(), 'decided_at' => null]);
    }

    public function withdraw(EditorialRevision $revision, User $actor): EditorialRevision
    {
        if (! in_array($revision->status, [EditorialRevisionStatus::Draft, EditorialRevisionStatus::Submitted, EditorialRevisionStatus::ChangesRequested], true)) {
            throw new InvalidEditorialOperation('This revision can no longer be withdrawn.');
        }

        return $this->transition($revision, $actor, EditorialRevisionStatus::Withdrawn, EditorialActionType::Withdrawn, ['withdrawn_at' => now()]);
    }

    /** @param array<string, mixed> $timestamps */
    private function transition(EditorialRevision $revision, User $actor, EditorialRevisionStatus $status, EditorialActionType $type, array $timestamps): EditorialRevision
    {
        return DB::transaction(function () use ($revision, $actor, $status, $type, $timestamps): EditorialRevision {
            $locked = EditorialRevision::query()->lockForUpdate()->findOrFail($revision->id);
            $locked->update(['status' => $status, ...$timestamps]);
            $locked->actions()->create(['actor_user_id' => $actor->id, 'type' => $type, 'acted_at' => now()]);
            $this->auditLogger->record('editorial.revision_'.$type->value, $locked, ['status' => $status->value], $actor);

            if (in_array($type, [EditorialActionType::Submitted, EditorialActionType::Resubmitted], true)) {
                EditorialRevisionSubmitted::dispatch($locked->id, $actor->id);
            }

            return $locked->fresh(['items', 'blocks', 'actions']);
        });
    }
}

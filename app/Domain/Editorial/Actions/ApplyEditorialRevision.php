<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Editorial\Services\CatalogEditorialFieldRegistry;
use App\Domain\Editorial\Services\EditorialEvidenceService;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\EditorialActionType;
use App\Enums\EditorialRevisionStatus;
use App\Enums\ReviewCheckResult;
use App\Enums\RevisionOperation;
use App\Enums\WorkType;
use App\Events\EditorialRevisionApplied;
use App\Models\EditorialRevision;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreRelationship;
use App\Models\Season;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\User;
use App\Models\Work;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApplyEditorialRevision
{
    public function __construct(
        private readonly CatalogEditorialFieldRegistry $registry,
        private readonly EditorialEvidenceService $evidence,
        private readonly LoreIntegrityService $loreIntegrity,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(EditorialRevision $revision, User $actor): EditorialRevision
    {
        return DB::transaction(function () use ($revision, $actor): EditorialRevision {
            $locked = EditorialRevision::query()
                ->with(['items', 'blocks', 'revisable'])
                ->lockForUpdate()
                ->findOrFail($revision->id);

            if ($locked->status !== EditorialRevisionStatus::Approved) {
                throw new InvalidEditorialOperation('Only an approved revision may be applied.');
            }

            $target = $locked->revisable()->lockForUpdate()->firstOrFail();
            if ((int) $target->getAttribute('lock_version') !== $locked->base_version) {
                $this->auditLogger->record('editorial.optimistic_lock_conflict', $locked, [
                    'base_version' => $locked->base_version,
                    'current_version' => (int) $target->getAttribute('lock_version'),
                ], $actor);
                throw new OptimisticLockConflict;
            }

            foreach ([$this->evidence->sourceResult($locked), $this->evidence->rightsResult($locked), $this->evidence->spoilerResult($locked)] as $result) {
                if ($result === ReviewCheckResult::Failed) {
                    throw new InvalidEditorialOperation('The approved revision no longer satisfies its evidence requirements.', 'editorial_checks_incomplete');
                }
            }

            $changes = [];
            foreach ($locked->items as $item) {
                $current = $target->getAttribute($item->field);
                $current = $current instanceof \BackedEnum ? $current->value : $current;
                if (! hash_equals((string) $item->previous_value_hash, $this->registry->fingerprint($current))) {
                    throw new OptimisticLockConflict('A revised field changed after the proposal was created.');
                }

                $value = $item->operation === RevisionOperation::Remove ? null : ($item->proposed_value['value'] ?? null);
                $changes[$item->field] = $item->operation === RevisionOperation::Remove
                    ? null
                    : $this->registry->normalize($target, $item->field, $value);
            }

            foreach ($locked->blocks as $block) {
                $current = $target->getAttribute($block->field);
                if (! hash_equals((string) $block->original_text_checksum, $this->registry->fingerprint($current))) {
                    throw new OptimisticLockConflict('A revised text field changed after the proposal was created.');
                }
                $changes[$block->field] = $this->registry->normalize($target, $block->field, $block->proposed_text);
            }

            $this->validateRelationships($target, $changes);
            $target->fill($changes);
            if ($target->isFillable('updated_by')) {
                $target->setAttribute('updated_by', $actor->id);
            }
            $target->setAttribute('lock_version', $locked->base_version + 1);
            $target->save();

            $locked->update(['status' => EditorialRevisionStatus::Applied, 'applied_at' => now()]);
            $locked->actions()->create([
                'actor_user_id' => $actor->id,
                'type' => EditorialActionType::Applied,
                'source_result' => $this->evidence->sourceResult($locked),
                'rights_result' => $this->evidence->rightsResult($locked),
                'spoiler_result' => $this->evidence->spoilerResult($locked),
                'acted_at' => now(),
            ]);
            $this->auditLogger->record('editorial.revision_applied', $locked, [
                'target_type' => $target->getMorphClass(),
                'target_id' => $target->getKey(),
                'new_version' => $locked->base_version + 1,
            ], $actor);

            EditorialRevisionApplied::dispatch($locked->id, (int) $target->getKey(), $target->getMorphClass(), $actor->id);

            return $locked->fresh(['revisable', 'items', 'blocks', 'actions']);
        }, attempts: 3);
    }

    /** @param array<string, mixed> $changes */
    private function validateRelationships(Model $target, array $changes): void
    {
        if ($target instanceof Work && array_key_exists('franchise_id', $changes) && $changes['franchise_id'] !== null) {
            $valid = Franchise::query()->whereKey($changes['franchise_id'])->where('universe_id', $target->universe_id)->exists();
            if (! $valid) {
                throw new InvalidEditorialOperation('The franchise must belong to the work universe.');
            }
        }

        if ($target instanceof Work && array_key_exists('type', $changes)) {
            $type = WorkType::from((string) $changes['type']);
            if ($type !== $target->type && ($target->seriesDetail()->exists() || $target->seasons()->exists() || $target->episodes()->exists())) {
                throw new InvalidEditorialOperation('A work type cannot change while type-specific records exist.');
            }
        }

        if ($target instanceof Episode && array_key_exists('season_id', $changes) && $changes['season_id'] !== null) {
            $valid = Season::query()->whereKey($changes['season_id'])->where('work_id', $target->work_id)->exists();
            if (! $valid) {
                throw new InvalidEditorialOperation('The episode season must belong to the episode work.');
            }
        }

        if ($target instanceof EntityAppearance) {
            $target->fill($changes);
            $target->unsetRelation('work')->unsetRelation('season')->unsetRelation('episode')->unsetRelation('loreEntity');
            $this->loreIntegrity->assertAppearance($target->load(['loreEntity', 'work', 'season', 'episode']));
        }

        if ($target instanceof LoreRelationship) {
            $this->loreIntegrity->assertRelationshipBoundaries($target->sourceEntity, $target->relationshipType, [...$target->getAttributes(), ...$changes]);
        }

        if ($target instanceof Timeline) {
            $this->loreIntegrity->assertTimelineOwner($target, [...$target->getAttributes(), ...$changes]);
        }

        if ($target instanceof TimelineEntry) {
            $this->loreIntegrity->assertTimelineEntry($target->timeline, [...$target->getAttributes(), ...$changes]);
        }
    }
}

<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Illuminate\Support\Facades\DB;

class MutateTimeline
{
    public function __construct(private readonly LoreIntegrityService $integrity) {}

    /** @param array<string, mixed> $attributes */
    public function createTimeline(Universe $universe, array $attributes, User $actor): Timeline
    {
        $this->assertOwnerUniverse($universe, $attributes);

        return $universe->timelines()->create([...$attributes, 'status' => PublicationStatus::Draft, 'visibility' => LoreVisibility::Restricted, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
    }

    /** @param array<string, mixed> $attributes */
    public function updateTimeline(Timeline $timeline, array $attributes, User $actor): Timeline
    {
        return DB::transaction(function () use ($timeline, $attributes, $actor): Timeline {
            $locked = Timeline::query()->lockForUpdate()->findOrFail($timeline->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            $this->assertOwnerUniverse($locked->universe, $attributes);
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);

            return $locked->fresh();
        });
    }

    /** @param array<string, mixed> $attributes */
    public function createEntry(Timeline $timeline, array $attributes, User $actor): TimelineEntry
    {
        return DB::transaction(function () use ($timeline, $attributes, $actor): TimelineEntry {
            $entityIds = array_values(array_unique($attributes['entity_ids'] ?? []));
            unset($attributes['entity_ids']);
            $this->integrity->assertTimelineEntry($timeline, $attributes);
            $entry = $timeline->entries()->create([...$attributes, 'status' => PublicationStatus::Draft, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
            $this->syncEntities($entry, $timeline, $entityIds);

            return $entry->fresh(['entities']);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function updateEntry(TimelineEntry $entry, array $attributes, User $actor): TimelineEntry
    {
        return DB::transaction(function () use ($entry, $attributes, $actor): TimelineEntry {
            $locked = TimelineEntry::query()->lockForUpdate()->findOrFail($entry->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version']);
            $entityIds = array_values(array_unique($attributes['entity_ids'] ?? $locked->entities()->pluck('lore_entities.id')->all()));
            unset($attributes['entity_ids']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            $this->integrity->assertTimelineEntry($locked->timeline, [...$locked->getAttributes(), ...$attributes]);
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);
            $this->syncEntities($locked, $locked->timeline, $entityIds);

            return $locked->fresh(['entities']);
        });
    }

    /** @param array<string, mixed> $attributes */
    private function assertOwnerUniverse(Universe $universe, array $attributes): void
    {
        if (! empty($attributes['lore_entity_id']) && ! LoreEntity::query()->whereKey($attributes['lore_entity_id'])->where('universe_id', $universe->id)->exists()) {
            throw new InvalidLoreOperation('The timeline owner entity must belong to the timeline universe.', 'cross_universe_lore_reference');
        }
        if (! empty($attributes['work_id']) && ! Work::query()->whereKey($attributes['work_id'])->where('universe_id', $universe->id)->exists()) {
            throw new InvalidLoreOperation('The timeline work must belong to the timeline universe.', 'cross_universe_lore_reference');
        }
    }

    /** @param list<int> $entityIds */
    private function syncEntities(TimelineEntry $entry, Timeline $timeline, array $entityIds): void
    {
        if (LoreEntity::query()->whereKey($entityIds)->where('universe_id', $timeline->universe_id)->count() !== count($entityIds)) {
            throw new InvalidLoreOperation('Timeline entry entities must belong to the timeline universe.', 'cross_universe_lore_reference');
        }
        $entry->entities()->sync(collect($entityIds)->mapWithKeys(fn (int $id, int $position): array => [$id => ['role' => 'subject', 'position' => $position]])->all());
    }
}

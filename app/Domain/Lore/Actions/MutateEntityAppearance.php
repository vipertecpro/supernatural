<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\PublicationStatus;
use App\Models\EntityAppearance;
use App\Models\LoreEntity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MutateEntityAppearance
{
    public function __construct(private readonly LoreIntegrityService $integrity) {}

    /** @param array<string, mixed> $attributes */
    public function create(LoreEntity $entity, array $attributes, User $actor): EntityAppearance
    {
        return DB::transaction(function () use ($entity, $attributes, $actor): EntityAppearance {
            if ($this->duplicateExists($entity, $attributes)) {
                throw new InvalidLoreOperation('This appearance already exists for the selected Catalog target.', 'duplicate_entity_appearance');
            }
            $appearance = EntityAppearance::query()->create([...$attributes, 'lore_entity_id' => $entity->id, 'status' => PublicationStatus::Draft, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
            $this->integrity->assertAppearance($appearance->load(['loreEntity', 'work', 'season', 'episode']));

            return $appearance->fresh(['work', 'season', 'episode']);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function update(EntityAppearance $appearance, array $attributes, User $actor): EntityAppearance
    {
        return DB::transaction(function () use ($appearance, $attributes, $actor): EntityAppearance {
            $locked = EntityAppearance::query()->lockForUpdate()->findOrFail($appearance->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);
            $this->integrity->assertAppearance($locked->fresh(['loreEntity', 'work', 'season', 'episode']));

            return $locked->fresh(['work', 'season', 'episode']);
        });
    }

    /** @param array<string, mixed> $attributes */
    private function duplicateExists(LoreEntity $entity, array $attributes): bool
    {
        return $entity->appearances()->where('work_id', $attributes['work_id'])->where('kind', $attributes['kind'])
            ->where(fn ($query) => isset($attributes['season_id']) ? $query->where('season_id', $attributes['season_id']) : $query->whereNull('season_id'))
            ->where(fn ($query) => isset($attributes['episode_id']) ? $query->where('episode_id', $attributes['episode_id']) : $query->whereNull('episode_id'))
            ->exists();
    }
}

<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\PublicationStatus;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MutateLoreAlias
{
    public function __construct(private readonly LoreIntegrityService $integrity) {}

    /** @param array<string, mixed> $attributes */
    public function create(LoreEntity $entity, array $attributes, User $actor): LoreAlias
    {
        $attributes['locale'] = $this->integrity->normalizeLocale($attributes['locale'] ?? null);
        $attributes['normalized_name'] = $this->integrity->normalizeAlias($attributes['name']);
        if ($entity->aliases()->where('normalized_name', $attributes['normalized_name'])->where('type', $attributes['type'])->where(fn ($query) => isset($attributes['locale']) ? $query->where('locale', $attributes['locale']) : $query->whereNull('locale'))->exists()) {
            throw new InvalidLoreOperation('This alias already exists for the Lore entity.', 'duplicate_lore_alias');
        }

        return LoreAlias::query()->create([...$attributes, 'lore_entity_id' => $entity->id, 'status' => PublicationStatus::Draft, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
    }

    /** @param array<string, mixed> $attributes */
    public function update(LoreAlias $alias, array $attributes, User $actor): LoreAlias
    {
        return DB::transaction(function () use ($alias, $attributes, $actor): LoreAlias {
            $locked = LoreAlias::query()->lockForUpdate()->findOrFail($alias->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            if (array_key_exists('locale', $attributes)) {
                $attributes['locale'] = $this->integrity->normalizeLocale($attributes['locale']);
            }
            if (isset($attributes['name'])) {
                $attributes['normalized_name'] = $this->integrity->normalizeAlias($attributes['name']);
            }
            $normalized = $attributes['normalized_name'] ?? $locked->normalized_name;
            $type = $attributes['type'] ?? $locked->type;
            $locale = array_key_exists('locale', $attributes) ? $attributes['locale'] : $locked->locale;
            if (LoreAlias::query()->where('lore_entity_id', $locked->lore_entity_id)->where('normalized_name', $normalized)->where('type', $type)->whereKeyNot($locked->id)->where(fn ($query) => $locale !== null ? $query->where('locale', $locale) : $query->whereNull('locale'))->exists()) {
                throw new InvalidLoreOperation('This alias already exists for the Lore entity.', 'duplicate_lore_alias');
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);

            return $locked->fresh();
        });
    }
}

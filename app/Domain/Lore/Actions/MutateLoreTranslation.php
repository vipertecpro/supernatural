<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MutateLoreTranslation
{
    public function __construct(private readonly LoreIntegrityService $integrity) {}

    /** @param array<string, mixed> $attributes */
    public function create(LoreEntity $entity, array $attributes, User $actor): LoreEntityTranslation
    {
        $attributes['locale'] = $this->integrity->normalizeLocale($attributes['locale']);
        $attributes['source_locale'] = $this->integrity->normalizeLocale($attributes['source_locale'] ?? null);
        if ($entity->translations()->where('locale', $attributes['locale'])->exists()) {
            throw new InvalidLoreOperation('A translation for this locale already exists.', 'duplicate_lore_translation');
        }

        return LoreEntityTranslation::query()->create([...$attributes, 'lore_entity_id' => $entity->id, 'status' => PublicationStatus::Draft, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
    }

    /** @param array<string, mixed> $attributes */
    public function update(LoreEntityTranslation $translation, array $attributes, User $actor): LoreEntityTranslation
    {
        return DB::transaction(function () use ($translation, $attributes, $actor): LoreEntityTranslation {
            $locked = LoreEntityTranslation::query()->lockForUpdate()->findOrFail($translation->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version'], $attributes['locale']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            if (array_key_exists('source_locale', $attributes)) {
                $attributes['source_locale'] = $this->integrity->normalizeLocale($attributes['source_locale']);
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);

            return $locked->fresh();
        });
    }
}

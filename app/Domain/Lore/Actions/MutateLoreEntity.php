<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\LoreEntityType;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MutateLoreEntity
{
    /** @param array<string, mixed> $attributes */
    public function create(Universe $universe, array $attributes, User $actor): LoreEntity
    {
        return DB::transaction(function () use ($universe, $attributes, $actor): LoreEntity {
            $details = $attributes['details'] ?? null;
            unset($attributes['details']);
            $entity = $universe->loreEntities()->create([...$attributes, 'status' => PublicationStatus::Draft, 'visibility' => LoreVisibility::Restricted, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
            $this->upsertDetails($entity, $details);

            return $entity->fresh($this->extensionRelations());
        });
    }

    /** @param array<string, mixed> $attributes */
    public function update(LoreEntity $entity, array $attributes, User $actor): LoreEntity
    {
        return DB::transaction(function () use ($entity, $attributes, $actor): LoreEntity {
            $locked = LoreEntity::query()->lockForUpdate()->findOrFail($entity->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            $details = $attributes['details'] ?? null;
            unset($attributes['details']);
            if (isset($attributes['type']) && LoreEntityType::from((string) $attributes['type']) !== $locked->type && $this->hasAnyExtension($locked)) {
                throw new InvalidLoreOperation('An entity type cannot change while a typed extension exists.', 'incompatible_lore_entity_type');
            }
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);
            $this->upsertDetails($locked, $details);

            return $locked->fresh($this->extensionRelations());
        });
    }

    /** @param array<string, mixed>|null $details */
    private function upsertDetails(LoreEntity $entity, ?array $details): void
    {
        if ($details === null) {
            return;
        }
        $relation = match ($entity->type) {
            LoreEntityType::Character => 'characterDetail',
            LoreEntityType::Performer => 'performerDetail',
            LoreEntityType::Location => 'locationDetail',
            LoreEntityType::Artifact, LoreEntityType::Weapon => 'artifactDetail',
            LoreEntityType::Organization => 'organizationDetail',
            LoreEntityType::Event => 'loreEventDetail',
            LoreEntityType::Concept => 'conceptDetail',
            default => null,
        };
        if ($relation === null) {
            throw new InvalidLoreOperation('This entity type has no approved extension table.', 'incompatible_lore_entity_type');
        }
        $entity->{$relation}()->updateOrCreate([], $details);
    }

    private function hasAnyExtension(LoreEntity $entity): bool
    {
        return collect($this->extensionRelations())->contains(fn (string $relation): bool => $entity->{$relation}()->exists());
    }

    /** @return list<string> */
    private function extensionRelations(): array
    {
        return ['characterDetail', 'performerDetail', 'locationDetail', 'artifactDetail', 'organizationDetail', 'loreEventDetail', 'conceptDetail'];
    }
}

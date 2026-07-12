<?php

namespace App\Domain\Lore\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Lore\Services\LoreIntegrityService;
use App\Enums\LoreRelationshipStatus;
use App\Models\LoreEntity;
use App\Models\LoreRelationship;
use App\Models\RelationshipType;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class MutateLoreRelationship
{
    public function __construct(private readonly LoreIntegrityService $integrity, private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes, User $actor): LoreRelationship
    {
        return DB::transaction(function () use ($attributes, $actor): LoreRelationship {
            $source = LoreEntity::query()->findOrFail((int) $attributes['source_entity_id']);
            $target = LoreEntity::query()->findOrFail((int) $attributes['target_entity_id']);
            $type = RelationshipType::query()->with('rules')->findOrFail((int) $attributes['relationship_type_id']);
            [$source, $target] = $this->integrity->assertRelationship($source, $target, $type);
            $attributes['source_entity_id'] = $source->id;
            $attributes['target_entity_id'] = $target->id;
            $this->integrity->assertRelationshipBoundaries($source, $type, $attributes);
            if (! $type->allows_duplicates && LoreRelationship::query()->where('source_entity_id', $source->id)->where('target_entity_id', $target->id)->where('relationship_type_id', $type->id)->whereNotIn('status', [LoreRelationshipStatus::Archived, LoreRelationshipStatus::Rejected])->exists()) {
                throw new InvalidLoreOperation('An active relationship with these endpoints already exists.', 'duplicate_lore_relationship');
            }
            $relationship = LoreRelationship::query()->create([...$attributes, 'status' => LoreRelationshipStatus::Draft, 'created_by' => $actor->id, 'updated_by' => $actor->id, 'lock_version' => 0]);
            $this->auditLogger->record('lore.relationship_created', $relationship, ['type_key' => $type->key, 'source_entity_id' => $source->id, 'target_entity_id' => $target->id], $actor);

            return $relationship->fresh(['sourceEntity', 'targetEntity', 'relationshipType']);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function update(LoreRelationship $relationship, array $attributes, User $actor): LoreRelationship
    {
        return DB::transaction(function () use ($relationship, $attributes, $actor): LoreRelationship {
            $locked = LoreRelationship::query()->lockForUpdate()->findOrFail($relationship->id);
            $expected = (int) $attributes['expected_version'];
            unset($attributes['expected_version'], $attributes['source_entity_id'], $attributes['target_entity_id'], $attributes['relationship_type_id']);
            if ($locked->lock_version !== $expected) {
                throw new OptimisticLockConflict;
            }
            $source = $locked->sourceEntity;
            $type = $locked->relationshipType;
            $this->integrity->assertRelationshipBoundaries($source, $type, [...$locked->getAttributes(), ...$attributes]);
            $locked->update([...$attributes, 'updated_by' => $actor->id, 'lock_version' => $locked->lock_version + 1]);

            return $locked->fresh(['sourceEntity', 'targetEntity', 'relationshipType']);
        });
    }
}

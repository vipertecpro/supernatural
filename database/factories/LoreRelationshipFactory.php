<?php

namespace Database\Factories;

use App\Enums\CanonClassification;
use App\Enums\LoreRelationshipStatus;
use App\Enums\RelationshipConfidence;
use App\Models\LoreEntity;
use App\Models\LoreRelationship;
use App\Models\RelationshipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoreRelationship>
 */
class LoreRelationshipFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (LoreRelationship $relationship): void {
            $relationship->targetEntity->update(['universe_id' => $relationship->sourceEntity->universe_id, 'type' => $relationship->sourceEntity->type]);
            $relationship->relationshipType->rules()->firstOrCreate(['source_entity_type' => $relationship->sourceEntity->type, 'target_entity_type' => $relationship->targetEntity->fresh()->type]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['source_entity_id' => LoreEntity::factory(), 'target_entity_id' => LoreEntity::factory(), 'relationship_type_id' => RelationshipType::factory(), 'canon_classification' => CanonClassification::Unknown, 'confidence' => RelationshipConfidence::Unknown, 'status' => LoreRelationshipStatus::Draft, 'start_work_id' => null, 'start_season_id' => null, 'start_episode_id' => null, 'end_work_id' => null, 'end_season_id' => null, 'end_episode_id' => null, 'starts_on' => null, 'ends_on' => null, 'date_precision' => null, 'qualifier' => null, 'editorial_note' => null, 'dispute_reason' => null, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }
}

<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\RelationshipType;
use App\Models\RelationshipTypeRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RelationshipTypeRule>
 */
class RelationshipTypeRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['relationship_type_id' => RelationshipType::factory(), 'source_entity_type' => LoreEntityType::Character, 'target_entity_type' => LoreEntityType::Character];
    }
}

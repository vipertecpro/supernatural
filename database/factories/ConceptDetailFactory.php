<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\ConceptDetail;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConceptDetail>
 */
class ConceptDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Concept), 'category' => 'cosmology', 'classification' => 'abstract'];
    }
}

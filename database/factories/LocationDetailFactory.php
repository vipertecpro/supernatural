<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\LocationDetail;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocationDetail>
 */
class LocationDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Location), 'location_type' => 'settlement', 'parent_location_entity_id' => null, 'classification' => 'fictional'];
    }
}

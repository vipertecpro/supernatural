<?php

namespace Database\Factories;

use App\Enums\LoreEntityType;
use App\Models\CharacterDetail;
use App\Models\LoreEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterDetail>
 */
class CharacterDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory()->type(LoreEntityType::Character), 'category' => 'traveler', 'lifecycle_status' => 'unknown', 'birth_or_origin' => 'Hollow Meridian', 'pronouns' => null, 'species_entity_id' => null];
    }
}

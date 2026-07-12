<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoreEntityTranslation>
 */
class LoreEntityTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['lore_entity_id' => LoreEntity::factory(), 'locale' => 'fr', 'name' => 'Signal de Cendre '.fake()->unique()->numberBetween(1, 999999), 'short_name' => null, 'short_description' => fake()->sentence(), 'summary' => fake()->paragraph(), 'source_locale' => 'en', 'status' => PublicationStatus::Draft, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'lock_version' => 0];
    }
}

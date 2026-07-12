<?php

namespace Database\Factories;

use App\Enums\CanonClassification;
use App\Enums\LoreEntityType;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Models\LoreEntity;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoreEntity>
 */
class LoreEntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Mara Vey', 'The Ashbound', 'Ember Archive', 'Hollow Meridian', 'The First Signal', 'Order of the Glass Moon']).' '.fake()->unique()->numberBetween(10, 999999);

        return ['universe_id' => Universe::factory(), 'type' => LoreEntityType::Character, 'slug' => str($name)->slug(), 'canonical_name' => $name, 'short_description' => fake()->sentence(), 'summary' => fake()->paragraph(), 'original_language' => 'en', 'status' => PublicationStatus::Draft, 'canon_classification' => CanonClassification::Unknown, 'visibility' => LoreVisibility::Restricted, 'metadata' => [], 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }

    public function type(LoreEntityType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => PublicationStatus::Published, 'visibility' => LoreVisibility::Public, 'published_at' => now(), 'archived_at' => null]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => PublicationStatus::Archived, 'visibility' => LoreVisibility::Restricted, 'archived_at' => now()]);
    }
}

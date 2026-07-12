<?php

namespace Database\Factories;

use App\Enums\CanonClassification;
use App\Enums\LoreVisibility;
use App\Enums\PublicationStatus;
use App\Enums\TimelineType;
use App\Models\Timeline;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timeline>
 */
class TimelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Hollow Meridian Chronology '.fake()->unique()->numberBetween(1, 999999);

        return ['universe_id' => Universe::factory(), 'lore_entity_id' => null, 'work_id' => null, 'name' => $name, 'slug' => str($name)->slug(), 'type' => TimelineType::Universe, 'description' => fake()->paragraph(), 'canon_classification' => CanonClassification::Unknown, 'status' => PublicationStatus::Draft, 'visibility' => LoreVisibility::Restricted, 'created_by' => null, 'updated_by' => null, 'published_at' => null, 'archived_at' => null, 'lock_version' => 0];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => PublicationStatus::Published, 'visibility' => LoreVisibility::Public, 'published_at' => now()]);
    }
}

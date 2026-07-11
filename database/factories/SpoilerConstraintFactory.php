<?php

namespace Database\Factories;

use App\Enums\SpoilerSeverity;
use App\Models\Source;
use App\Models\SpoilerConstraint;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpoilerConstraint>
 */
class SpoilerConstraintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'universe_id' => Universe::factory(),
            'spoilerable_type' => Source::class,
            'spoilerable_id' => Source::factory(),
            'severity' => fake()->randomElement(SpoilerSeverity::cases()),
            'earliest_progress' => ['type' => 'ordinal', 'value' => fake()->numberBetween(1, 100)],
            'warning' => fake()->optional()->sentence(),
            'metadata' => [],
        ];
    }
}

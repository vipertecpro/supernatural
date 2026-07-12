<?php

namespace Database\Factories;

use App\Models\SpoilerConstraint;
use App\Models\SpoilerCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpoilerCorrection>
 */
class SpoilerCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'spoiler_constraint_id' => SpoilerConstraint::factory(),
            'corrected_by_user_id' => User::factory(),
            'reason' => fake()->sentence(),
            'previous_classification' => ['severity' => 'minor', 'classification_status' => 'draft'],
            'corrected_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\SpoilerBoundary;
use App\Models\SpoilerConstraint;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpoilerBoundary>
 */
class SpoilerBoundaryFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (SpoilerBoundary $boundary): void {
            $boundary->constraint->update(['universe_id' => $boundary->work->universe_id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'spoiler_constraint_id' => SpoilerConstraint::factory(),
            'work_id' => Work::factory(),
            'season_id' => null,
            'episode_id' => null,
        ];
    }
}

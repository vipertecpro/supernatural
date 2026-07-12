<?php

namespace Database\Factories;

use App\Models\Universe;
use App\Models\User;
use App\Models\ViewingProgress;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViewingProgress>
 */
class ViewingProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'universe_id' => Universe::factory(),
            'work_id' => fn (array $attributes): int => Work::factory()->create(['universe_id' => $attributes['universe_id']])->id,
            'season_id' => null,
            'episode_id' => null,
        ];
    }
}

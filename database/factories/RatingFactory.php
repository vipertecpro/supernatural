<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'target_type' => 'work', 'target_id' => Work::factory(), 'universe_id' => fn (array $attributes): int => (int) Work::query()->whereKey($attributes['target_id'])->value('universe_id'), 'rating' => 4];
    }
}

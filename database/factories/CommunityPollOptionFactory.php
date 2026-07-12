<?php

namespace Database\Factories;

use App\Models\CommunityPoll;
use App\Models\CommunityPollOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPollOption>
 */
class CommunityPollOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['poll_id' => CommunityPoll::factory(), 'text' => fake()->words(3, true), 'position' => fake()->unique()->numberBetween(1, 1000)];
    }
}

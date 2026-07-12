<?php

namespace Database\Factories;

use App\Enums\BunkerJoinRequestStatus;
use App\Models\Bunker;
use App\Models\BunkerJoinRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerJoinRequest>
 */
class BunkerJoinRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['bunker_id' => Bunker::factory(), 'user_id' => User::factory(), 'status' => BunkerJoinRequestStatus::Pending, 'active_key' => fake()->uuid(), 'message' => fake()->sentence(), 'submitted_at' => now()];
    }
}

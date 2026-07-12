<?php

namespace Database\Factories;

use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerMembershipStatus;
use App\Models\Bunker;
use App\Models\BunkerMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerMembership>
 */
class BunkerMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['bunker_id' => Bunker::factory(), 'user_id' => User::factory(), 'role' => BunkerMembershipRole::Member, 'status' => BunkerMembershipStatus::Active, 'approved_by' => null, 'active_key' => fake()->uuid(), 'lock_version' => 0, 'joined_at' => now()];
    }
}

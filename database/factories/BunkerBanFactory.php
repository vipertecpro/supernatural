<?php

namespace Database\Factories;

use App\Enums\BunkerBanStatus;
use App\Models\Bunker;
use App\Models\BunkerBan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerBan>
 */
class BunkerBanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['bunker_id' => Bunker::factory(), 'user_id' => User::factory(), 'issued_by' => User::factory(), 'reason_code' => 'conduct_violation', 'user_visible_explanation' => 'Access is temporarily restricted after a conduct review.', 'private_note' => null, 'status' => BunkerBanStatus::Active, 'active_key' => fake()->uuid(), 'effective_at' => now(), 'expires_at' => now()->addWeek()];
    }
}

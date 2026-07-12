<?php

namespace Database\Factories;

use App\Enums\BunkerInvitationStatus;
use App\Enums\BunkerMembershipRole;
use App\Models\Bunker;
use App\Models\BunkerInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BunkerInvitation>
 */
class BunkerInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['bunker_id' => Bunker::factory(), 'invited_user_id' => User::factory(), 'inviter_user_id' => User::factory(), 'proposed_role' => BunkerMembershipRole::Member, 'token_hash' => hash('sha256', fake()->uuid()), 'status' => BunkerInvitationStatus::Pending, 'active_key' => fake()->uuid(), 'sent_at' => now(), 'expires_at' => now()->addWeek()];
    }
}

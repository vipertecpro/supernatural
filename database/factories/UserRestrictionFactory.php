<?php

namespace Database\Factories;

use App\Enums\RestrictionStatus;
use App\Enums\UserRestrictionType;
use App\Models\ModerationAction;
use App\Models\User;
use App\Models\UserRestriction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserRestriction> */
class UserRestrictionFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'moderation_action_id' => ModerationAction::factory(), 'type' => UserRestrictionType::Capability, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => now()->addDays(7), 'user_visible_reason' => 'A temporary capability restriction was applied.'];
    }

    public function lifted(): static
    {
        return $this->state(fn (): array => ['status' => RestrictionStatus::Lifted, 'lifted_at' => now()]);
    }
}

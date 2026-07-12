<?php

namespace Database\Factories;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboardingState;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserOnboardingState> */
class UserOnboardingStateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'current_step' => OnboardingStep::Introduction,
            'started_at' => null,
            'last_activity_at' => null,
            'completed_at' => null,
            'lock_version' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'current_step' => OnboardingStep::Completed,
            'started_at' => now(),
            'last_activity_at' => now(),
            'completed_at' => now(),
        ]);
    }
}

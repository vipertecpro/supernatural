<?php

namespace App\Domain\Onboarding;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboardingState;
use Illuminate\Http\RedirectResponse;

class OnboardingStateResolver
{
    public function forUser(User $user): UserOnboardingState
    {
        return UserOnboardingState::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_step' => OnboardingStep::Completed,
                'started_at' => $user->created_at ?? now(),
                'last_activity_at' => now(),
                'completed_at' => now(),
                'lock_version' => 0,
            ],
        );
    }

    public function redirectToCurrent(UserOnboardingState $state): RedirectResponse
    {
        return to_route($state->current_step->routeName());
    }

    public function canVisit(UserOnboardingState $state, OnboardingStep $step): bool
    {
        return $step->position() <= $state->current_step->position();
    }

    public function destination(User $user): RedirectResponse
    {
        if (! $user->hasVerifiedEmail()) {
            return to_route('verification.notice');
        }

        $state = $this->forUser($user);

        return $state->isCompleted()
            ? to_route('dashboard')
            : $this->redirectToCurrent($state);
    }
}

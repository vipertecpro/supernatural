<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Onboarding\Exceptions\InvalidOnboardingTransition;
use App\Enums\OnboardingStep;
use App\Events\OnboardingCompleted;
use App\Models\User;
use App\Models\UserOnboardingState;
use Illuminate\Support\Facades\DB;

class CompleteOnboarding
{
    public function handle(User $user, UserOnboardingState $state, int $expectedVersion): UserOnboardingState
    {
        return DB::transaction(function () use ($user, $state, $expectedVersion): UserOnboardingState {
            $locked = UserOnboardingState::query()->lockForUpdate()->findOrFail($state->id);
            if ($locked->user_id !== $user->id || $locked->current_step !== OnboardingStep::Review) {
                throw new InvalidOnboardingTransition('Onboarding must reach review before completion.');
            }
            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            $locked->forceFill([
                'current_step' => OnboardingStep::Completed,
                'last_activity_at' => now(),
                'completed_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            OnboardingCompleted::dispatch($user->id, $locked->id);

            return $locked->fresh();
        }, attempts: 3);
    }
}

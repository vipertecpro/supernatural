<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Onboarding\Exceptions\InvalidOnboardingTransition;
use App\Enums\OnboardingStep;
use App\Models\UserOnboardingState;
use Closure;
use Illuminate\Support\Facades\DB;

class AdvanceOnboarding
{
    /**
     * Persist a step and advance the workflow only when that step is current.
     *
     * @param  Closure(UserOnboardingState): void|null  $mutation
     */
    public function handle(
        UserOnboardingState $state,
        OnboardingStep $submittedStep,
        int $expectedVersion,
        ?Closure $mutation = null,
    ): UserOnboardingState {
        return DB::transaction(function () use ($state, $submittedStep, $expectedVersion, $mutation): UserOnboardingState {
            $locked = UserOnboardingState::query()->lockForUpdate()->findOrFail($state->id);

            if ($locked->lock_version !== $expectedVersion) {
                throw new OptimisticLockConflict;
            }

            if ($locked->isCompleted()) {
                throw new InvalidOnboardingTransition('Completed onboarding cannot be changed.');
            }

            if ($submittedStep->position() > $locked->current_step->position()) {
                throw new InvalidOnboardingTransition('A future onboarding step cannot be submitted.');
            }

            $mutation?->__invoke($locked);

            $nextStep = $locked->current_step === $submittedStep
                ? $submittedStep->next()
                : $locked->current_step;

            $locked->forceFill([
                'current_step' => $nextStep,
                'started_at' => $locked->started_at ?? now(),
                'last_activity_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            return $locked->fresh();
        }, attempts: 3);
    }
}

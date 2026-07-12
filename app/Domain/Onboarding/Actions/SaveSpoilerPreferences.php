<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use App\Models\UserSpoilerPreference;

class SaveSpoilerPreferences
{
    public function __construct(
        private readonly AdvanceOnboarding $advance,
        private readonly ManagePersonalLibrary $library,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $user, UserOnboardingState $state, array $attributes): UserOnboardingState
    {
        return $this->advance->handle(
            $state,
            OnboardingStep::SpoilerPreferences,
            (int) $attributes['expected_version'],
            function () use ($user, $attributes): void {
                $universeIds = UserFandomPreference::query()->where('user_id', $user->id)->pluck('universe_id');
                foreach ($universeIds as $universeId) {
                    $current = UserSpoilerPreference::query()
                        ->where('user_id', $user->id)
                        ->where('universe_id', $universeId)
                        ->first();
                    $this->library->updateSpoilerPreferences($user, (int) $universeId, [
                        'tolerance' => $attributes['tolerance'],
                        'show_warnings' => $attributes['show_warnings'],
                        'rewatch_behavior' => $attributes['rewatch_behavior'],
                        'expected_version' => $current->lock_version ?? 0,
                    ]);
                }
            },
        );
    }
}

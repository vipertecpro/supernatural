<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Enums\OnboardingStep;
use App\Enums\PersonalVisibility;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;

class SavePrivacyDefaults
{
    public function __construct(
        private readonly AdvanceOnboarding $advance,
        private readonly ManagePersonalLibrary $library,
    ) {}

    public function handle(User $user, UserOnboardingState $state, int $expectedVersion): UserOnboardingState
    {
        return $this->advance->handle(
            $state,
            OnboardingStep::PrivacyDefaults,
            $expectedVersion,
            function () use ($user): void {
                $preferences = UserFandomPreference::query()->where('user_id', $user->id)->get();
                foreach ($preferences as $preference) {
                    $this->library->updatePreferences($user, $preference->universe_id, [
                        'continue_watching_visibility' => PersonalVisibility::Private->value,
                        'rating_visibility' => PersonalVisibility::Private->value,
                        'favourite_visibility' => PersonalVisibility::Private->value,
                        'journey_visibility' => PersonalVisibility::Private->value,
                        'expected_version' => $preference->lock_version,
                    ]);
                }
            },
        );
    }
}

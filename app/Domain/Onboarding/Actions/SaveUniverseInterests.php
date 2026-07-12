<?php

namespace App\Domain\Onboarding\Actions;

use App\Enums\OnboardingStep;
use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use Illuminate\Validation\ValidationException;

class SaveUniverseInterests
{
    public function __construct(private readonly AdvanceOnboarding $advance) {}

    /** @param list<int> $universeIds */
    public function handle(User $user, UserOnboardingState $state, array $universeIds, int $expectedVersion): UserOnboardingState
    {
        $availableIds = Universe::query()
            ->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->whereIn('id', $universeIds)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (count($availableIds) !== count(array_unique($universeIds))) {
            throw ValidationException::withMessages(['universe_ids' => 'One or more selected universes are no longer available.']);
        }

        $hasPublishedUniverses = Universe::query()
            ->where('status', PublicationStatus::Published)
            ->where('is_public', true)
            ->exists();
        if ($hasPublishedUniverses && $availableIds === []) {
            throw ValidationException::withMessages(['universe_ids' => 'Select at least one universe to continue.']);
        }

        return $this->advance->handle(
            $state,
            OnboardingStep::UniverseInterests,
            $expectedVersion,
            function () use ($user, $availableIds): void {
                UserFandomPreference::query()
                    ->where('user_id', $user->id)
                    ->when($availableIds !== [], fn ($query) => $query->whereNotIn('universe_id', $availableIds))
                    ->delete();

                foreach ($availableIds as $universeId) {
                    UserFandomPreference::query()->firstOrCreate(
                        ['user_id' => $user->id, 'universe_id' => $universeId],
                        [
                            'default_locale' => app()->getLocale(),
                            'auto_complete_progress' => false,
                            'auto_remove_completed_watchlist_items' => false,
                            'continue_watching_visibility' => PersonalVisibility::Private,
                            'rating_visibility' => PersonalVisibility::Private,
                            'favourite_visibility' => PersonalVisibility::Private,
                            'journey_visibility' => PersonalVisibility::Private,
                            'lock_version' => 0,
                        ],
                    );
                }
            },
        );
    }
}

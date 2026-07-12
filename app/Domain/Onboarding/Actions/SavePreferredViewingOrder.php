<?php

namespace App\Domain\Onboarding\Actions;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use App\Models\ViewingOrder;
use Illuminate\Validation\ValidationException;

class SavePreferredViewingOrder
{
    public function __construct(
        private readonly AdvanceOnboarding $advance,
        private readonly ManagePersonalLibrary $library,
    ) {}

    public function handle(User $user, UserOnboardingState $state, ?int $viewingOrderId, int $expectedVersion): UserOnboardingState
    {
        return $this->advance->handle(
            $state,
            OnboardingStep::ViewingOrder,
            $expectedVersion,
            function () use ($user, $viewingOrderId): void {
                $preferences = UserFandomPreference::query()->where('user_id', $user->id)->get();

                if ($viewingOrderId === null) {
                    foreach ($preferences as $preference) {
                        $this->library->updatePreferences($user, $preference->universe_id, [
                            'preferred_viewing_order_id' => null,
                            'expected_version' => $preference->lock_version,
                        ]);
                    }

                    return;
                }

                $order = ViewingOrder::query()->visibleToPublic()->find($viewingOrderId);
                if ($order === null) {
                    throw ValidationException::withMessages(['viewing_order_id' => 'That viewing order is no longer available.']);
                }

                $preference = $preferences->firstWhere('universe_id', $order->universe_id);
                if ($preference === null) {
                    throw ValidationException::withMessages(['viewing_order_id' => 'Choose an order for one of your selected universes.']);
                }

                $this->library->updatePreferences($user, $order->universe_id, [
                    'preferred_viewing_order_id' => $order->id,
                    'expected_version' => $preference->lock_version,
                ]);
            },
        );
    }
}

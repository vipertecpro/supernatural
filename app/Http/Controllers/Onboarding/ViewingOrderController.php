<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\SavePreferredViewingOrder;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreViewingOrderRequest;
use App\Models\UserFandomPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ViewingOrderController extends Controller
{
    public function edit(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::ViewingOrder)) {
            return $states->redirectToCurrent($state);
        }

        return Inertia::render('onboarding/viewing-order', [
            'onboarding' => $data->shared($state, OnboardingStep::ViewingOrder),
            'orders' => $data->viewingOrders($request->user()),
            'selectedOrderId' => UserFandomPreference::query()
                ->where('user_id', $request->user()->id)
                ->whereNotNull('preferred_viewing_order_id')
                ->value('preferred_viewing_order_id'),
        ]);
    }

    public function update(StoreViewingOrderRequest $request, OnboardingStateResolver $states, SavePreferredViewingOrder $action): RedirectResponse
    {
        $state = $action->handle(
            $request->user(),
            $states->forUser($request->user()),
            $request->validated('viewing_order_id') === null ? null : (int) $request->validated('viewing_order_id'),
            (int) $request->validated('expected_version'),
        );

        return $states->redirectToCurrent($state);
    }
}

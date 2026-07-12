<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\SaveUniverseInterests;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreUniverseInterestsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UniverseInterestsController extends Controller
{
    public function edit(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::UniverseInterests)) {
            return $states->redirectToCurrent($state);
        }

        return Inertia::render('onboarding/universe-interests', [
            'onboarding' => $data->shared($state, OnboardingStep::UniverseInterests),
            'universes' => $data->universes($request->user()),
        ]);
    }

    public function update(StoreUniverseInterestsRequest $request, OnboardingStateResolver $states, SaveUniverseInterests $action): RedirectResponse
    {
        $state = $action->handle(
            $request->user(),
            $states->forUser($request->user()),
            $request->universeIds(),
            (int) $request->validated('expected_version'),
        );

        return $states->redirectToCurrent($state);
    }
}

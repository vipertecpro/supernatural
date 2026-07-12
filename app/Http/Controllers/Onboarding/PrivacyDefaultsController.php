<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\SavePrivacyDefaults;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StorePrivacyDefaultsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyDefaultsController extends Controller
{
    public function edit(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::PrivacyDefaults)) {
            return $states->redirectToCurrent($state);
        }

        return Inertia::render('onboarding/privacy-defaults', [
            'onboarding' => $data->shared($state, OnboardingStep::PrivacyDefaults),
        ]);
    }

    public function update(StorePrivacyDefaultsRequest $request, OnboardingStateResolver $states, SavePrivacyDefaults $action): RedirectResponse
    {
        $state = $action->handle(
            $request->user(),
            $states->forUser($request->user()),
            (int) $request->validated('expected_version'),
        );

        return $states->redirectToCurrent($state);
    }
}

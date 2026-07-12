<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\CompleteOnboarding;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use Illuminate\Http\RedirectResponse;

class CompleteOnboardingController extends Controller
{
    public function __invoke(CompleteOnboardingRequest $request, OnboardingStateResolver $states, CompleteOnboarding $action): RedirectResponse
    {
        $action->handle(
            $request->user(),
            $states->forUser($request->user()),
            (int) $request->validated('expected_version'),
        );

        return to_route('dashboard')->with('onboarding_completed', true);
    }
}

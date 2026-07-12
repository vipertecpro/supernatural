<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\AdvanceOnboarding;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\AdvanceIntroductionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntroductionController extends Controller
{
    public function show(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response
    {
        $state = $states->forUser($request->user());

        return Inertia::render('onboarding/introduction', [
            'onboarding' => $data->shared($state, OnboardingStep::Introduction),
        ]);
    }

    public function update(AdvanceIntroductionRequest $request, OnboardingStateResolver $states, AdvanceOnboarding $advance): RedirectResponse
    {
        $state = $advance->handle(
            $states->forUser($request->user()),
            OnboardingStep::Introduction,
            (int) $request->validated('expected_version'),
        );

        return $states->redirectToCurrent($state);
    }
}

<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function show(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::Review)) {
            return $states->redirectToCurrent($state);
        }

        return Inertia::render('onboarding/review', [
            'onboarding' => $data->shared($state, OnboardingStep::Review),
            'summary' => $data->review($request->user()),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\SaveInitialViewingProgress;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreViewingProgressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ViewingProgressController extends Controller
{
    public function edit(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::ViewingProgress)) {
            return $states->redirectToCurrent($state);
        }

        return Inertia::render('onboarding/viewing-progress', [
            'onboarding' => $data->shared($state, OnboardingStep::ViewingProgress),
            'works' => $data->progressCatalog($request->user()),
        ]);
    }

    public function update(StoreViewingProgressRequest $request, OnboardingStateResolver $states, SaveInitialViewingProgress $action): RedirectResponse
    {
        $state = $action->handle(
            $request->user(),
            $states->forUser($request->user()),
            $request->validated(),
        );

        return $states->redirectToCurrent($state);
    }
}

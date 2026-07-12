<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Onboarding\Actions\SaveSpoilerPreferences;
use App\Domain\Onboarding\OnboardingPageData;
use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreSpoilerPreferencesRequest;
use App\Models\UserSpoilerPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpoilerPreferencesController extends Controller
{
    public function edit(Request $request, OnboardingStateResolver $states, OnboardingPageData $data): Response|RedirectResponse
    {
        $state = $states->forUser($request->user());
        if (! $states->canVisit($state, OnboardingStep::SpoilerPreferences)) {
            return $states->redirectToCurrent($state);
        }
        $preference = UserSpoilerPreference::query()->where('user_id', $request->user()->id)->first();

        return Inertia::render('onboarding/spoiler-preferences', [
            'onboarding' => $data->shared($state, OnboardingStep::SpoilerPreferences),
            'preference' => [
                'tolerance' => $preference === null ? 'strict' : $preference->tolerance->value,
                'showWarnings' => $preference === null ? true : $preference->show_warnings,
                'rewatchBehavior' => $preference === null ? 'historical' : $preference->rewatch_behavior,
            ],
        ]);
    }

    public function update(StoreSpoilerPreferencesRequest $request, OnboardingStateResolver $states, SaveSpoilerPreferences $action): RedirectResponse
    {
        $state = $action->handle($request->user(), $states->forUser($request->user()), $request->validated());

        return $states->redirectToCurrent($state);
    }
}

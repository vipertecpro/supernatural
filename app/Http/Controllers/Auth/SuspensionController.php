<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\RestrictionScope;
use App\Http\Controllers\Controller;
use App\Models\UserRestriction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuspensionController extends Controller
{
    public function __invoke(Request $request, OnboardingStateResolver $states): Response|RedirectResponse
    {
        $restriction = UserRestriction::query()
            ->currentlyActive()
            ->where('user_id', $request->user()->id)
            ->whereHas('scopes', fn ($query) => $query->where('scope', RestrictionScope::PlatformAccess->value))
            ->latest('effective_at')
            ->first();

        if ($restriction === null) {
            return $states->destination($request->user());
        }

        return Inertia::render('auth/suspended', [
            'reason' => $restriction->user_visible_reason,
            'effectiveAt' => $restriction->effective_at?->toAtomString(),
            'expiresAt' => $restriction->expires_at?->toAtomString(),
        ]);
    }
}

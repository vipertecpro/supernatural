<?php

namespace App\Http\Middleware;

use App\Domain\Onboarding\OnboardingStateResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function __construct(private readonly OnboardingStateResolver $states) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $state = $this->states->forUser($user);
        if (! $state->isCompleted()) {
            return $this->states->redirectToCurrent($state);
        }

        return $next($request);
    }
}

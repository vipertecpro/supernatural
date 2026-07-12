<?php

namespace App\Http\Middleware;

use App\Domain\Onboarding\OnboardingStateResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIncomplete
{
    public function __construct(private readonly OnboardingStateResolver $states) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if ($this->states->forUser($user)->isCompleted()) {
            return to_route('dashboard');
        }

        return $next($request);
    }
}

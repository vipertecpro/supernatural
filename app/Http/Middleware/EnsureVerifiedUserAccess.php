<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        $route = $request->route();

        if (! $route instanceof Route || $this->isVerificationRoute($route)) {
            return $next($request);
        }

        $requiresAuthentication = collect($route->gatherMiddleware())->contains(
            fn (string $middleware): bool => $middleware === 'auth'
                || str_starts_with($middleware, 'auth:')
                || str_contains($middleware, Authenticate::class),
        );

        if (! $requiresAuthentication) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Your email address is not verified.');
        }

        return redirect()->guest(route('verification.notice'));
    }

    /** Determine whether an unverified user needs this route to verify or leave. */
    private function isVerificationRoute(Route $route): bool
    {
        return in_array($route->getName(), [
            'verification.notice',
            'verification.send',
            'verification.verify',
            'logout',
        ], true);
    }
}

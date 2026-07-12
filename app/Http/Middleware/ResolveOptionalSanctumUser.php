<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveOptionalSanctumUser
{
    /** Resolve a valid Sanctum identity when present without rejecting public guests. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();
        if ($user !== null) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}

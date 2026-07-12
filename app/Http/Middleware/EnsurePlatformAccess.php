<?php

namespace App\Http\Middleware;

use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\RestrictionScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAccess
{
    public function __construct(private readonly RestrictionEvaluator $restrictions) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && $this->restrictions->hasUserScope($user, RestrictionScope::PlatformAccess)) {
            return to_route('account.suspended');
        }

        return $next($request);
    }
}

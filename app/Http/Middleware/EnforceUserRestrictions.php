<?php

namespace App\Http\Middleware;

use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\RestrictionScope;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceUserRestrictions
{
    public function __construct(private readonly RestrictionEvaluator $restrictions) {}

    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        $platformException = str_starts_with($routeName, 'api.v1.me.notifications') || str_starts_with($routeName, 'api.v1.me.notification-preferences') || str_starts_with($routeName, 'api.v1.me.appeals');

        if (! $platformException && $this->restrictions->hasUserScope($user, RestrictionScope::PlatformAccess)) {
            return ApiResponse::error($request, 'platform_access_restricted', 'Platform access is temporarily restricted.', 403);
        }

        $scope ??= $this->inferredScope($request)?->value;
        if ($scope !== null) {
            $restrictionScope = RestrictionScope::tryFrom($scope);
            if ($restrictionScope !== null && $this->restrictions->hasUserScope($user, $restrictionScope)) {
                return ApiResponse::error($request, 'capability_restricted', 'This capability is temporarily restricted.', 403);
            }
        }

        return $next($request);
    }

    private function inferredScope(Request $request): ?RestrictionScope
    {
        if ($request->isMethodSafe()) {
            return null;
        }

        $name = (string) $request->route()?->getName();

        return match (true) {
            $name === 'api.v1.reports.store' => RestrictionScope::ReportSubmission,
            str_starts_with($name, 'api.v1.media.attachments.') => RestrictionScope::MediaAttachment,
            str_starts_with($name, 'api.v1.media.assets.'), str_starts_with($name, 'api.v1.media.embeds.') => RestrictionScope::MediaSubmission,
            str_starts_with($name, 'api.v1.lore.'), str_starts_with($name, 'api.v1.lore-'), str_starts_with($name, 'api.v1.timelines.'), str_starts_with($name, 'api.v1.timeline-entries.'), str_starts_with($name, 'api.v1.universes.lore.'), str_starts_with($name, 'api.v1.universes.timelines.') => RestrictionScope::LoreContribution,
            in_array($name, ['api.v1.editorial.revisions.store', 'api.v1.editorial.revisions.submit', 'api.v1.editorial.revisions.resubmit'], true) => RestrictionScope::EditorialSubmission,
            str_starts_with($name, 'api.v1.franchises.'), str_starts_with($name, 'api.v1.works.'), str_starts_with($name, 'api.v1.seasons.'), str_starts_with($name, 'api.v1.episodes.'), str_starts_with($name, 'api.v1.universes.franchises.'), str_starts_with($name, 'api.v1.universes.works.') => RestrictionScope::CatalogContribution,
            default => null,
        };
    }
}

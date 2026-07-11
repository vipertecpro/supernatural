<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateSeason;
use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Actions\UpdateSeason;
use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CatalogIndexRequest;
use App\Http\Requests\Api\V1\PublishCatalogRequest;
use App\Http\Requests\Api\V1\StoreSeasonRequest;
use App\Http\Requests\Api\V1\UpdateSeasonRequest;
use App\Http\Resources\Api\V1\SeasonResource;
use App\Models\Season;
use App\Models\Work;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class SeasonController extends Controller
{
    public function index(CatalogIndexRequest $request, Work $work): JsonResponse
    {
        $this->ensureParentVisible($request, $work);
        $query = $work->seasons()->with('spoilerConstraints');
        if (! $request->user()?->can('viewAny', Season::class)) {
            $query->visibleToPublic();
        } elseif ($request->filled('filter.status')) {
            $query->where('status', $request->string('filter.status'));
        } else {
            $query->whereNull('archived_at');
        }
        $paginator = $query->cursorPaginate($request->pageSize());

        return ApiResponse::cursor($request, SeasonResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreSeasonRequest $request, Work $work, CreateSeason $action): JsonResponse
    {
        $season = $action->handle($work, $request->validated(), $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new SeasonResource($season))->resolve($request), status: 201);
    }

    public function show(Request $request, Season $season): JsonResponse
    {
        if (! Season::query()->visibleToPublic()->whereKey($season)->exists() && ! $request->user()?->can('view', $season)) {
            abort(404);
        }
        $season->load('spoilerConstraints');

        return ApiResponse::success($request, (new SeasonResource($season))->resolve($request));
    }

    public function update(UpdateSeasonRequest $request, Season $season, UpdateSeason $action): JsonResponse
    {
        $season = $action->handle($season, $request->validated(), $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new SeasonResource($season))->resolve($request));
    }

    public function publish(PublishCatalogRequest $request, Season $season, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('publish', $season);
        $season = $action->publish($season, $request->user(), $request->isPublic())->load('spoilerConstraints');

        return ApiResponse::success($request, (new SeasonResource($season))->resolve($request));
    }

    public function archive(Request $request, Season $season, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('archive', $season);
        $season = $action->archive($season, $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new SeasonResource($season))->resolve($request));
    }

    public function destroy(Request $request, Season $season, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('delete', $season);
        if ($season->episodes()->exists()) {
            throw new InvalidCatalogOperation('A season with episodes must be archived instead of deleted.');
        }
        $auditLogger->record('catalog.season_deleted', $season, ['slug' => $season->slug], $request->user());
        $season->delete();

        return Response::json(null, 204);
    }

    private function ensureParentVisible(Request $request, Work $work): void
    {
        if (! Work::query()->visibleToPublic()->whereKey($work)->exists() && ! $request->user()?->can('view', $work)) {
            abort(404);
        }
    }
}

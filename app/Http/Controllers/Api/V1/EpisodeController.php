<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateEpisode;
use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Actions\UpdateEpisode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CatalogIndexRequest;
use App\Http\Requests\Api\V1\PublishCatalogRequest;
use App\Http\Requests\Api\V1\StoreEpisodeRequest;
use App\Http\Requests\Api\V1\UpdateEpisodeRequest;
use App\Http\Resources\Api\V1\EpisodeResource;
use App\Models\Episode;
use App\Models\Season;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class EpisodeController extends Controller
{
    public function index(CatalogIndexRequest $request, Season $season): JsonResponse
    {
        if (! Season::query()->visibleToPublic()->whereKey($season)->exists() && ! $request->user()?->can('view', $season)) {
            abort(404);
        }
        $query = $season->episodes()->with('spoilerConstraints');
        if (! $request->user()?->can('viewAny', Episode::class)) {
            $query->visibleToPublic();
        } elseif ($request->filled('filter.status')) {
            $query->where('status', $request->string('filter.status'));
        } else {
            $query->whereNull('archived_at');
        }
        $paginator = $query->cursorPaginate($request->pageSize());

        return ApiResponse::cursor($request, EpisodeResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreEpisodeRequest $request, Season $season, CreateEpisode $action): JsonResponse
    {
        $episode = $action->handle($season, $request->validated(), $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new EpisodeResource($episode))->resolve($request), status: 201);
    }

    public function show(Request $request, Episode $episode): JsonResponse
    {
        if (! Episode::query()->visibleToPublic()->whereKey($episode)->exists() && ! $request->user()?->can('view', $episode)) {
            abort(404);
        }
        $episode->load('spoilerConstraints');

        return ApiResponse::success($request, (new EpisodeResource($episode))->resolve($request));
    }

    public function update(UpdateEpisodeRequest $request, Episode $episode, UpdateEpisode $action): JsonResponse
    {
        $episode = $action->handle($episode, $request->validated(), $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new EpisodeResource($episode))->resolve($request));
    }

    public function publish(PublishCatalogRequest $request, Episode $episode, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('publish', $episode);
        $episode = $action->publish($episode, $request->user(), $request->isPublic())->load('spoilerConstraints');

        return ApiResponse::success($request, (new EpisodeResource($episode))->resolve($request));
    }

    public function archive(Request $request, Episode $episode, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('archive', $episode);
        $episode = $action->archive($episode, $request->user())->load('spoilerConstraints');

        return ApiResponse::success($request, (new EpisodeResource($episode))->resolve($request));
    }

    public function destroy(Request $request, Episode $episode, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('delete', $episode);
        $auditLogger->record('catalog.episode_deleted', $episode, ['slug' => $episode->slug], $request->user());
        $episode->delete();

        return Response::json(null, 204);
    }
}

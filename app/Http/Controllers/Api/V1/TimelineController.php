<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateTimeline;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoreIndexRequest;
use App\Http\Requests\Api\V1\StoreTimelineRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateTimelineRequest;
use App\Http\Resources\Api\V1\TimelineResource;
use App\Models\Timeline;
use App\Models\Universe;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimelineController extends Controller
{
    public function index(LoreIndexRequest $request, Universe $universe): JsonResponse
    {
        $query = $universe->timelines()->with('spoilerConstraints.boundaries');
        if (! $request->user()?->can('viewAny', Timeline::class)) {
            $query->visibleToPublic();
        } else {
            $query->whereNull('archived_at');
        }
        $paginator = $query->orderBy('name')->orderBy('id')->cursorPaginate($request->pageSize());
        $items = collect($paginator->items())->reject(fn (Timeline $timeline): bool => app(SpoilerVisibilityService::class)->decide($timeline, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::cursor($request, TimelineResource::collection($items)->resolve($request), $paginator);
    }

    public function show(Request $request, Timeline $timeline): JsonResponse
    {
        if (! Timeline::query()->visibleToPublic()->whereKey($timeline)->exists() && ! $request->user()?->can('view', $timeline)) {
            abort(404);
        }
        if (app(SpoilerVisibilityService::class)->decide($timeline, $request->user()) === SpoilerVisibility::Hidden) {
            abort(404);
        }

        return ApiResponse::success($request, (new TimelineResource($timeline->load('spoilerConstraints.boundaries')))->resolve($request));
    }

    public function store(StoreTimelineRequest $request, Universe $universe, MutateTimeline $action): JsonResponse
    {
        $timeline = $action->createTimeline($universe, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new TimelineResource($timeline))->resolve($request), status: 201);
    }

    public function update(UpdateTimelineRequest $request, Timeline $timeline, MutateTimeline $action): JsonResponse
    {
        $timeline = $action->updateTimeline($timeline, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new TimelineResource($timeline))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, Timeline $timeline, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $timeline);

        return ApiResponse::success($request, (new TimelineResource($action->publish($timeline, $request->user(), $request->expectedVersion(), $request->isPublic())))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, Timeline $timeline, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $timeline);

        return ApiResponse::success($request, (new TimelineResource($action->archive($timeline, $request->user(), $request->expectedVersion())))->resolve($request));
    }
}

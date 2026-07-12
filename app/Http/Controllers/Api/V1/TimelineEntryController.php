<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateTimeline;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoreIndexRequest;
use App\Http\Requests\Api\V1\StoreTimelineEntryRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateTimelineEntryRequest;
use App\Http\Resources\Api\V1\TimelineEntryResource;
use App\Models\LoreEntity;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TimelineEntryController extends Controller
{
    public function index(LoreIndexRequest $request, Timeline $timeline): JsonResponse
    {
        if (! Timeline::query()->visibleToPublic()->whereKey($timeline)->exists() && ! $request->user()?->can('view', $timeline)) {
            abort(404);
        }
        $query = $timeline->entries()->with(['entities', 'spoilerConstraints.boundaries']);
        if (! $request->user()?->can('viewAny', TimelineEntry::class)) {
            $query->where('status', PublicationStatus::Published)->whereNull('archived_at');
        }
        $paginator = $query->cursorPaginate($request->pageSize());
        $items = collect($paginator->items())->reject(fn (TimelineEntry $entry): bool => app(SpoilerVisibilityService::class)->decide($entry, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::cursor($request, TimelineEntryResource::collection($items)->resolve($request), $paginator);
    }

    public function forEntity(LoreIndexRequest $request, LoreEntity $entity): JsonResponse
    {
        $query = TimelineEntry::query()->with(['timeline', 'entities', 'spoilerConstraints.boundaries'])->whereHas('entities', fn ($entities) => $entities->whereKey($entity->id))->whereHas('timeline', fn ($timelines) => $timelines->where('status', PublicationStatus::Published)->where('visibility', 'public')->whereNull('archived_at'))->where('status', PublicationStatus::Published)->whereNull('archived_at');
        $paginator = $query->orderBy('sort_key')->orderBy('id')->cursorPaginate($request->pageSize());
        $items = collect($paginator->items())->reject(fn (TimelineEntry $entry): bool => app(SpoilerVisibilityService::class)->decide($entry, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::cursor($request, TimelineEntryResource::collection($items)->resolve($request), $paginator);
    }

    public function store(StoreTimelineEntryRequest $request, Timeline $timeline, MutateTimeline $action): JsonResponse
    {
        Gate::authorize('update', $timeline);
        $entry = $action->createEntry($timeline, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new TimelineEntryResource($entry))->resolve($request), status: 201);
    }

    public function update(UpdateTimelineEntryRequest $request, TimelineEntry $entry, MutateTimeline $action): JsonResponse
    {
        $entry = $action->updateEntry($entry, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new TimelineEntryResource($entry))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, TimelineEntry $entry, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $entry);

        return ApiResponse::success($request, (new TimelineEntryResource($action->publish($entry, $request->user(), $request->expectedVersion())))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, TimelineEntry $entry, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $entry);

        return ApiResponse::success($request, (new TimelineEntryResource($action->archive($entry, $request->user(), $request->expectedVersion())))->resolve($request));
    }
}

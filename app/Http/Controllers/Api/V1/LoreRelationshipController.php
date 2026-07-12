<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateLoreRelationship;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Enums\LoreRelationshipStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoreIndexRequest;
use App\Http\Requests\Api\V1\StoreLoreRelationshipRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateLoreRelationshipRequest;
use App\Http\Resources\Api\V1\LoreRelationshipResource;
use App\Models\LoreEntity;
use App\Models\LoreRelationship;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LoreRelationshipController extends Controller
{
    public function index(LoreIndexRequest $request, LoreEntity $entity): JsonResponse
    {
        $query = LoreRelationship::query()->with(['sourceEntity.spoilerConstraints.boundaries', 'targetEntity.spoilerConstraints.boundaries', 'relationshipType', 'spoilerConstraints.boundaries'])
            ->where(fn ($edges) => $edges->where('source_entity_id', $entity->id)->orWhere('target_entity_id', $entity->id));
        if (! $request->user()?->can('viewAny', LoreRelationship::class)) {
            $query->where('status', LoreRelationshipStatus::Published)->whereNull('archived_at')
                ->whereHas('sourceEntity', fn ($source) => $source->where('status', 'published')->where('visibility', 'public')->whereNull('archived_at'))
                ->whereHas('targetEntity', fn ($target) => $target->where('status', 'published')->where('visibility', 'public')->whereNull('archived_at'));
        }
        $query->when($request->filled('filter.relationship_type_id'), fn ($edges) => $edges->where('relationship_type_id', $request->integer('filter.relationship_type_id')));
        $paginator = $query->orderByDesc('published_at')->orderBy('id')->cursorPaginate($request->pageSize());
        $items = collect($paginator->items())->reject(function (LoreRelationship $edge) use ($request): bool {
            $service = app(SpoilerVisibilityService::class);

            return $service->decide($edge, $request->user()) === SpoilerVisibility::Hidden || $service->decide($edge->targetEntity, $request->user()) === SpoilerVisibility::Hidden;
        })->values();

        return ApiResponse::cursor($request, LoreRelationshipResource::collection($items)->resolve($request), $paginator);
    }

    public function store(StoreLoreRelationshipRequest $request, MutateLoreRelationship $action): JsonResponse
    {
        $relationship = $action->create($request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new LoreRelationshipResource($relationship))->resolve($request), status: 201);
    }

    public function update(UpdateLoreRelationshipRequest $request, LoreRelationship $relationship, MutateLoreRelationship $action): JsonResponse
    {
        $relationship = $action->update($relationship, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new LoreRelationshipResource($relationship))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, LoreRelationship $relationship, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $relationship);
        $relationship = $action->publish($relationship, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new LoreRelationshipResource($relationship->load(['sourceEntity', 'targetEntity', 'relationshipType', 'spoilerConstraints.boundaries'])))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, LoreRelationship $relationship, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $relationship);
        $relationship = $action->archive($relationship, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new LoreRelationshipResource($relationship->load(['sourceEntity', 'targetEntity', 'relationshipType', 'spoilerConstraints.boundaries'])))->resolve($request));
    }
}

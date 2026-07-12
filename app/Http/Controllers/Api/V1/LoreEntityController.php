<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateLoreEntity;
use App\Domain\Lore\Actions\MutateLoreTranslation;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\PermissionName;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoreIndexRequest;
use App\Http\Requests\Api\V1\StoreLoreEntityRequest;
use App\Http\Requests\Api\V1\StoreLoreTranslationRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateLoreEntityRequest;
use App\Http\Requests\Api\V1\UpdateLoreTranslationRequest;
use App\Http\Resources\Api\V1\LoreEntityResource;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\Universe;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class LoreEntityController extends Controller
{
    public function index(LoreIndexRequest $request, Universe $universe): JsonResponse
    {
        $query = $universe->loreEntities()->with(['translations', 'spoilerConstraints.boundaries']);
        if (! $request->user()?->can('viewAny', LoreEntity::class)) {
            $query->visibleToPublic();
        } else {
            $query->whereNull('archived_at');
        }
        $query->when($request->filled('filter.type'), fn ($items) => $items->where('type', $request->string('filter.type')))
            ->when($request->filled('filter.status') && $request->user()?->can('viewAny', LoreEntity::class), fn ($items) => $items->where('status', $request->string('filter.status')));
        $sort = $request->string('sort', 'name')->toString();
        $column = str_contains($sort, 'published_at') ? 'published_at' : 'canonical_name';
        $paginator = $query->orderBy($column, str_starts_with($sort, '-') ? 'desc' : 'asc')->orderBy('id')->cursorPaginate($request->pageSize());
        $items = collect($paginator->items())->reject(fn (LoreEntity $entity): bool => app(SpoilerVisibilityService::class)->decide($entity, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::cursor($request, LoreEntityResource::collection($items)->resolve($request), $paginator);
    }

    public function show(Request $request, LoreEntity $entity): JsonResponse
    {
        $this->ensureVisible($request, $entity);
        $entity->load(['translations', 'spoilerConstraints.boundaries']);

        return ApiResponse::success($request, (new LoreEntityResource($entity))->resolve($request));
    }

    public function store(StoreLoreEntityRequest $request, Universe $universe, MutateLoreEntity $action): JsonResponse
    {
        $entity = $action->create($universe, $request->validated(), $request->user())->load(['translations', 'spoilerConstraints.boundaries']);

        return ApiResponse::success($request, (new LoreEntityResource($entity))->resolve($request), status: 201);
    }

    public function update(UpdateLoreEntityRequest $request, LoreEntity $entity, MutateLoreEntity $action): JsonResponse
    {
        $entity = $action->update($entity, $request->validated(), $request->user())->load(['translations', 'spoilerConstraints.boundaries']);

        return ApiResponse::success($request, (new LoreEntityResource($entity))->resolve($request));
    }

    public function storeTranslation(StoreLoreTranslationRequest $request, LoreEntity $entity, MutateLoreTranslation $action): JsonResponse
    {
        $action->create($entity, $request->validated(), $request->user());

        return ApiResponse::success($request, (new LoreEntityResource($entity->fresh()->load(['translations', 'spoilerConstraints.boundaries'])))->resolve($request), status: 201);
    }

    public function updateTranslation(UpdateLoreTranslationRequest $request, LoreEntityTranslation $translation, MutateLoreTranslation $action): JsonResponse
    {
        $translation = $action->update($translation, $request->validated(), $request->user());

        return ApiResponse::success($request, (new LoreEntityResource($translation->loreEntity->load(['translations', 'spoilerConstraints.boundaries'])))->resolve($request));
    }

    public function publishTranslation(TransitionLoreRequest $request, LoreEntityTranslation $translation, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize(PermissionName::LorePublish->value);
        $action->publish($translation, $request->user(), $request->expectedVersion());
        $entity = LoreEntityTranslation::query()->findOrFail($translation->id)->loreEntity;

        return ApiResponse::success($request, (new LoreEntityResource($entity->load(['translations', 'spoilerConstraints.boundaries'])))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, LoreEntity $entity, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $entity);
        $entity = $action->publish($entity, $request->user(), $request->expectedVersion(), $request->isPublic())->load(['translations', 'spoilerConstraints.boundaries']);

        return ApiResponse::success($request, (new LoreEntityResource($entity))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, LoreEntity $entity, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $entity);
        $entity = $action->archive($entity, $request->user(), $request->expectedVersion())->load(['translations', 'spoilerConstraints.boundaries']);

        return ApiResponse::success($request, (new LoreEntityResource($entity))->resolve($request));
    }

    public function destroy(Request $request, LoreEntity $entity): JsonResponse
    {
        Gate::authorize('delete', $entity);
        if ($entity->aliases()->exists() || $entity->appearances()->exists() || $entity->outgoingRelationships()->exists() || $entity->incomingRelationships()->exists() || $entity->timelines()->exists() || $entity->timelineEntries()->exists() || $entity->citations()->exists() || $entity->editorialRevisions()->exists()) {
            throw new InvalidLoreOperation('A Lore entity with durable history or references must be archived instead of deleted.', 'protected_lore_deletion');
        }
        $entity->delete();

        return Response::json(null, 204);
    }

    private function ensureVisible(Request $request, LoreEntity $entity): void
    {
        if (! LoreEntity::query()->visibleToPublic()->whereKey($entity)->exists() && ! $request->user()?->can('view', $entity)) {
            abort(404);
        }
        if (app(SpoilerVisibilityService::class)->decide($entity, $request->user()) === SpoilerVisibility::Hidden) {
            abort(404);
        }
    }
}

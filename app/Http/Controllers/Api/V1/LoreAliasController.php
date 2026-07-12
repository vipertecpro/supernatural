<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateLoreAlias;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLoreAliasRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateLoreAliasRequest;
use App\Http\Resources\Api\V1\LoreAliasResource;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LoreAliasController extends Controller
{
    public function index(Request $request, LoreEntity $entity): JsonResponse
    {
        $query = $entity->aliases()->with('spoilerConstraints.boundaries')->orderBy('name')->orderBy('id');
        if (! $request->user()?->can('viewAny', LoreAlias::class)) {
            $query->where('status', PublicationStatus::Published)->whereNull('archived_at');
        }
        $items = $query->get()->reject(fn (LoreAlias $alias): bool => app(SpoilerVisibilityService::class)->decide($alias, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::success($request, LoreAliasResource::collection($items)->resolve($request));
    }

    public function store(StoreLoreAliasRequest $request, LoreEntity $entity, MutateLoreAlias $action): JsonResponse
    {
        Gate::authorize('update', $entity);
        $alias = $action->create($entity, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new LoreAliasResource($alias))->resolve($request), status: 201);
    }

    public function update(UpdateLoreAliasRequest $request, LoreAlias $alias, MutateLoreAlias $action): JsonResponse
    {
        $alias = $action->update($alias, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new LoreAliasResource($alias))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, LoreAlias $alias, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $alias);

        return ApiResponse::success($request, (new LoreAliasResource($action->publish($alias, $request->user(), $request->expectedVersion())))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, LoreAlias $alias, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $alias);

        return ApiResponse::success($request, (new LoreAliasResource($action->archive($alias, $request->user(), $request->expectedVersion())))->resolve($request));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Lore\Actions\MutateEntityAppearance;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEntityAppearanceRequest;
use App\Http\Requests\Api\V1\TransitionLoreRequest;
use App\Http\Requests\Api\V1\UpdateEntityAppearanceRequest;
use App\Http\Resources\Api\V1\EntityAppearanceResource;
use App\Models\EntityAppearance;
use App\Models\LoreEntity;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EntityAppearanceController extends Controller
{
    public function index(Request $request, LoreEntity $entity): JsonResponse
    {
        $query = $entity->appearances()->with(['work', 'season', 'episode', 'spoilerConstraints.boundaries'])->orderBy('position')->orderBy('id');
        if (! $request->user()?->can('viewAny', EntityAppearance::class)) {
            $query->where('status', PublicationStatus::Published)->whereNull('archived_at')->whereHas('work', fn ($work) => $work->where('status', PublicationStatus::Published)->where('is_public', true)->whereNull('archived_at'));
        }
        $items = $query->get()->reject(fn (EntityAppearance $appearance): bool => app(SpoilerVisibilityService::class)->decide($appearance, $request->user()) === SpoilerVisibility::Hidden)->values();

        return ApiResponse::success($request, EntityAppearanceResource::collection($items)->resolve($request));
    }

    public function store(StoreEntityAppearanceRequest $request, LoreEntity $entity, MutateEntityAppearance $action): JsonResponse
    {
        Gate::authorize('update', $entity);
        $appearance = $action->create($entity, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new EntityAppearanceResource($appearance))->resolve($request), status: 201);
    }

    public function update(UpdateEntityAppearanceRequest $request, EntityAppearance $appearance, MutateEntityAppearance $action): JsonResponse
    {
        $appearance = $action->update($appearance, $request->validated(), $request->user())->load('spoilerConstraints.boundaries');

        return ApiResponse::success($request, (new EntityAppearanceResource($appearance))->resolve($request));
    }

    public function publish(TransitionLoreRequest $request, EntityAppearance $appearance, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('publish', $appearance);

        return ApiResponse::success($request, (new EntityAppearanceResource($action->publish($appearance, $request->user(), $request->expectedVersion())))->resolve($request));
    }

    public function archive(TransitionLoreRequest $request, EntityAppearance $appearance, TransitionLoreRecord $action): JsonResponse
    {
        Gate::authorize('archive', $appearance);

        return ApiResponse::success($request, (new EntityAppearanceResource($action->archive($appearance, $request->user(), $request->expectedVersion())))->resolve($request));
    }
}

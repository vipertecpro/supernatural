<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateFranchise;
use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Actions\UpdateFranchise;
use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CatalogIndexRequest;
use App\Http\Requests\Api\V1\PublishCatalogRequest;
use App\Http\Requests\Api\V1\StoreFranchiseRequest;
use App\Http\Requests\Api\V1\UpdateFranchiseRequest;
use App\Http\Resources\Api\V1\FranchiseResource;
use App\Models\Franchise;
use App\Models\Universe;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class FranchiseController extends Controller
{
    public function index(CatalogIndexRequest $request, Universe $universe): JsonResponse
    {
        $query = $universe->franchises()->orderBy('position')->orderBy('id');
        if (! $request->user()?->can('viewAny', Franchise::class)) {
            $query->visibleToPublic();
        } elseif ($request->filled('filter.status')) {
            $query->where('status', $request->string('filter.status'));
        } else {
            $query->whereNull('archived_at');
        }

        $paginator = $query->cursorPaginate($request->pageSize());

        return ApiResponse::cursor($request, FranchiseResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreFranchiseRequest $request, Universe $universe, CreateFranchise $action): JsonResponse
    {
        $franchise = $action->handle($universe, $request->validated(), $request->user());

        return ApiResponse::success($request, (new FranchiseResource($franchise))->resolve($request), status: 201);
    }

    public function show(Request $request, Franchise $franchise): JsonResponse
    {
        $this->ensureVisible($request, $franchise);

        return ApiResponse::success($request, (new FranchiseResource($franchise))->resolve($request));
    }

    public function update(UpdateFranchiseRequest $request, Franchise $franchise, UpdateFranchise $action): JsonResponse
    {
        $franchise = $action->handle($franchise, $request->validated(), $request->user());

        return ApiResponse::success($request, (new FranchiseResource($franchise))->resolve($request));
    }

    public function publish(PublishCatalogRequest $request, Franchise $franchise, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('publish', $franchise);

        return ApiResponse::success($request, (new FranchiseResource($action->publish($franchise, $request->user(), $request->isPublic())))->resolve($request));
    }

    public function archive(Request $request, Franchise $franchise, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('archive', $franchise);

        return ApiResponse::success($request, (new FranchiseResource($action->archive($franchise, $request->user())))->resolve($request));
    }

    public function destroy(Request $request, Franchise $franchise, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('delete', $franchise);
        if ($franchise->works()->exists()) {
            throw new InvalidCatalogOperation('A franchise with works must be archived instead of deleted.');
        }
        $auditLogger->record('catalog.franchise_deleted', $franchise, ['slug' => $franchise->slug], $request->user());
        $franchise->delete();

        return Response::json(null, 204);
    }

    private function ensureVisible(Request $request, Franchise $franchise): void
    {
        if (! Franchise::query()->visibleToPublic()->whereKey($franchise)->exists()
            && ! $request->user()?->can('view', $franchise)) {
            abort(404);
        }
    }
}

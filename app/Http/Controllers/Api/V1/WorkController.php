<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateWork;
use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Actions\UpdateWork;
use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CatalogIndexRequest;
use App\Http\Requests\Api\V1\PublishCatalogRequest;
use App\Http\Requests\Api\V1\StoreWorkRequest;
use App\Http\Requests\Api\V1\UpdateWorkRequest;
use App\Http\Resources\Api\V1\WorkResource;
use App\Models\Universe;
use App\Models\Work;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class WorkController extends Controller
{
    public function index(CatalogIndexRequest $request, Universe $universe): JsonResponse
    {
        $query = $universe->works()->with(['translations', 'seriesDetail', 'spoilerConstraints']);
        if (! $request->user()?->can('viewAny', Work::class)) {
            $query->visibleToPublic();
        } elseif ($request->filled('filter.status')) {
            $query->where('status', $request->string('filter.status'));
        } else {
            $query->whereNull('archived_at');
        }
        $query->when($request->filled('filter.type'), fn (Builder $items) => $items->where('type', $request->string('filter.type')))
            ->when($request->filled('filter.franchise_id'), fn (Builder $items) => $items->where('franchise_id', $request->integer('filter.franchise_id')));

        $sort = $request->string('sort', '-published_at')->toString();
        $column = ltrim($sort, '-') === 'title' ? 'original_title' : ltrim($sort, '-');
        $query->orderBy($column, str_starts_with($sort, '-') ? 'desc' : 'asc')->orderBy('id');
        $paginator = $query->cursorPaginate($request->pageSize());

        return ApiResponse::cursor($request, WorkResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreWorkRequest $request, Universe $universe, CreateWork $action): JsonResponse
    {
        $work = $action->handle($universe, $request->validated(), $request->user())->load(['translations', 'seriesDetail', 'spoilerConstraints']);

        return ApiResponse::success($request, (new WorkResource($work))->resolve($request), status: 201);
    }

    public function show(Request $request, Work $work): JsonResponse
    {
        $this->ensureVisible($request, $work);
        $work->load(['translations', 'seriesDetail', 'spoilerConstraints']);

        return ApiResponse::success($request, (new WorkResource($work))->resolve($request));
    }

    public function update(UpdateWorkRequest $request, Work $work, UpdateWork $action): JsonResponse
    {
        $work = $action->handle($work, $request->validated(), $request->user())->load(['translations', 'seriesDetail', 'spoilerConstraints']);

        return ApiResponse::success($request, (new WorkResource($work))->resolve($request));
    }

    public function publish(PublishCatalogRequest $request, Work $work, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('publish', $work);
        $work = $action->publish($work, $request->user(), $request->isPublic())->load(['translations', 'seriesDetail', 'spoilerConstraints']);

        return ApiResponse::success($request, (new WorkResource($work))->resolve($request));
    }

    public function archive(Request $request, Work $work, TransitionCatalogRecord $action): JsonResponse
    {
        Gate::authorize('archive', $work);
        $work = $action->archive($work, $request->user())->load(['translations', 'seriesDetail', 'spoilerConstraints']);

        return ApiResponse::success($request, (new WorkResource($work))->resolve($request));
    }

    public function destroy(Request $request, Work $work, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('delete', $work);
        if ($work->translations()->exists() || $work->seriesDetail()->exists() || $work->seasons()->exists() || $work->episodes()->exists()) {
            throw new InvalidCatalogOperation('A work with catalog children must be archived instead of deleted.');
        }
        $auditLogger->record('catalog.work_deleted', $work, ['slug' => $work->slug], $request->user());
        $work->delete();

        return Response::json(null, 204);
    }

    private function ensureVisible(Request $request, Work $work): void
    {
        if (! Work::query()->visibleToPublic()->whereKey($work)->exists() && ! $request->user()?->can('view', $work)) {
            abort(404);
        }
    }
}

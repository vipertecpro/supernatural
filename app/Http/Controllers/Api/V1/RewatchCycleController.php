<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManageRewatchCycles;
use App\Enums\RewatchStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRewatchCycleRequest;
use App\Http\Resources\Api\V1\RewatchCycleResource;
use App\Models\RewatchCycle;
use App\Models\ViewingOrder;
use App\Models\Work;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RewatchCycleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', RewatchCycle::class);
        $paginator = RewatchCycle::query()->where('user_id', $request->user()->id)->latest('started_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, RewatchCycleResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreRewatchCycleRequest $request, ManageRewatchCycles $action): JsonResponse
    {
        Gate::authorize('create', RewatchCycle::class);
        $work = Work::query()->findOrFail($request->integer('work_id'));
        $order = $request->filled('viewing_order_id') ? ViewingOrder::query()->findOrFail($request->integer('viewing_order_id')) : null;
        $cycle = $action->start($request->user(), $work, $order);

        return ApiResponse::success($request, (new RewatchCycleResource($cycle))->resolve($request), status: 201);
    }

    public function complete(Request $request, RewatchCycle $rewatch, ManageRewatchCycles $action): JsonResponse
    {
        abort_unless($rewatch->user_id === $request->user()->id, 404);
        Gate::authorize('update', $rewatch);

        return ApiResponse::success($request, (new RewatchCycleResource($action->transition($request->user(), $rewatch, RewatchStatus::Completed)))->resolve($request));
    }

    public function abandon(Request $request, RewatchCycle $rewatch, ManageRewatchCycles $action): JsonResponse
    {
        abort_unless($rewatch->user_id === $request->user()->id, 404);
        Gate::authorize('update', $rewatch);

        return ApiResponse::success($request, (new RewatchCycleResource($action->transition($request->user(), $rewatch, RewatchStatus::Abandoned)))->resolve($request));
    }
}

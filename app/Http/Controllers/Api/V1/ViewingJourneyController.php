<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManageViewingJourneys;
use App\Enums\JourneyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreViewingJourneyRequest;
use App\Http\Requests\Api\V1\TransitionJourneyRequest;
use App\Http\Resources\Api\V1\ViewingJourneyResource;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ViewingJourneyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UserViewingJourney::class);
        $paginator = UserViewingJourney::query()->where('user_id', $request->user()->id)->latest('updated_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, ViewingJourneyResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreViewingJourneyRequest $request, ManageViewingJourneys $action): JsonResponse
    {
        Gate::authorize('create', UserViewingJourney::class);
        $order = ViewingOrder::query()->findOrFail($request->integer('viewing_order_id'));
        $journey = $action->start($request->user(), $order, $request->validated('rewatch_cycle_id'));

        return ApiResponse::success($request, (new ViewingJourneyResource($journey))->resolve($request), status: 201);
    }

    public function show(Request $request, UserViewingJourney $journey): JsonResponse
    {
        $this->owned($request, $journey);

        return ApiResponse::success($request, (new ViewingJourneyResource($journey))->resolve($request));
    }

    public function pause(TransitionJourneyRequest $request, UserViewingJourney $journey, ManageViewingJourneys $action): JsonResponse
    {
        return $this->transition($request, $journey, $action, JourneyStatus::Paused);
    }

    public function resume(TransitionJourneyRequest $request, UserViewingJourney $journey, ManageViewingJourneys $action): JsonResponse
    {
        return $this->transition($request, $journey, $action, JourneyStatus::Active);
    }

    public function complete(TransitionJourneyRequest $request, UserViewingJourney $journey, ManageViewingJourneys $action): JsonResponse
    {
        return $this->transition($request, $journey, $action, JourneyStatus::Completed);
    }

    public function abandon(TransitionJourneyRequest $request, UserViewingJourney $journey, ManageViewingJourneys $action): JsonResponse
    {
        return $this->transition($request, $journey, $action, JourneyStatus::Abandoned);
    }

    private function transition(TransitionJourneyRequest $request, UserViewingJourney $journey, ManageViewingJourneys $action, JourneyStatus $status): JsonResponse
    {
        $this->owned($request, $journey);
        Gate::authorize('update', $journey);
        $journey = $action->transition($journey, $status, $request->integer('expected_version'));

        return ApiResponse::success($request, (new ViewingJourneyResource($journey))->resolve($request));
    }

    private function owned(Request $request, UserViewingJourney $journey): void
    {
        abort_unless($journey->user_id === $request->user()->id, 404);
        Gate::authorize('view', $journey);
    }
}

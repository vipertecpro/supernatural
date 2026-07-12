<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManageViewingSessions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreViewingSessionRequest;
use App\Http\Requests\Api\V1\UpdateViewingSessionRequest;
use App\Http\Resources\Api\V1\ViewingSessionResource;
use App\Models\ViewingSession;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ViewingSessionController extends Controller
{
    public function store(StoreViewingSessionRequest $request, ManageViewingSessions $action): JsonResponse
    {
        Gate::authorize('create', ViewingSession::class);
        $session = $action->start($request->user(), $request->validated());

        return ApiResponse::success($request, (new ViewingSessionResource($session))->resolve($request), status: 201);
    }

    public function update(UpdateViewingSessionRequest $request, ViewingSession $session, ManageViewingSessions $action): JsonResponse
    {
        abort_unless($session->user_id === $request->user()->id, 404);
        Gate::authorize('update', $session);
        $session = $action->update($request->user(), $session, $request->validated());

        return ApiResponse::success($request, (new ViewingSessionResource($session))->resolve($request));
    }

    public function end(UpdateViewingSessionRequest $request, ViewingSession $session, ManageViewingSessions $action): JsonResponse
    {
        abort_unless($session->user_id === $request->user()->id, 404);
        Gate::authorize('update', $session);
        $session = $action->update($request->user(), $session, $request->validated(), true);

        return ApiResponse::success($request, (new ViewingSessionResource($session))->resolve($request));
    }
}

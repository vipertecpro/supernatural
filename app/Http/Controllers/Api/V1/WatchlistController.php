<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWatchlistItemRequest;
use App\Http\Requests\Api\V1\StoreWatchlistRequest;
use App\Http\Resources\Api\V1\WatchlistResource;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WatchlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Watchlist::class);
        $paginator = Watchlist::query()->with('items')->where('user_id', $request->user()->id)->orderBy('position')->orderBy('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, WatchlistResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreWatchlistRequest $request, ManagePersonalLibrary $action): JsonResponse
    {
        Gate::authorize('create', Watchlist::class);
        $watchlist = $action->createWatchlist($request->user(), $request->validated());

        return ApiResponse::success($request, (new WatchlistResource($watchlist))->resolve($request), status: 201);
    }

    public function show(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->owned($request, $watchlist);

        return ApiResponse::success($request, (new WatchlistResource($watchlist->load('items')))->resolve($request));
    }

    public function update(StoreWatchlistRequest $request, Watchlist $watchlist, ManagePersonalLibrary $action): JsonResponse
    {
        $this->owned($request, $watchlist);
        Gate::authorize('update', $watchlist);
        $watchlist = $action->updateWatchlist($request->user(), $watchlist, $request->validated());

        return ApiResponse::success($request, (new WatchlistResource($watchlist))->resolve($request));
    }

    public function destroy(Request $request, Watchlist $watchlist): JsonResponse
    {
        $this->owned($request, $watchlist);
        Gate::authorize('delete', $watchlist);
        $watchlist->delete();

        return ApiResponse::success($request, null);
    }

    public function addItem(StoreWatchlistItemRequest $request, Watchlist $watchlist, ManagePersonalLibrary $action): JsonResponse
    {
        $this->owned($request, $watchlist);
        Gate::authorize('update', $watchlist);
        $action->addWatchlistItem($request->user(), $watchlist, $request->validated());

        return ApiResponse::success($request, (new WatchlistResource($watchlist->fresh('items')))->resolve($request), status: 201);
    }

    public function removeItem(Request $request, WatchlistItem $item): JsonResponse
    {
        abort_unless($item->watchlist->user_id === $request->user()->id, 404);
        Gate::authorize('update', $item->watchlist);
        $item->delete();

        return ApiResponse::success($request, null);
    }

    private function owned(Request $request, Watchlist $watchlist): void
    {
        abort_unless($watchlist->user_id === $request->user()->id, 404);
        Gate::authorize('view', $watchlist);
    }
}

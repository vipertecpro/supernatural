<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ViewingOrderResource;
use App\Models\Universe;
use App\Models\ViewingOrder;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ViewingOrderController extends Controller
{
    public function index(Request $request, Universe $universe): JsonResponse
    {
        $paginator = ViewingOrder::query()->visibleToPublic()->where('universe_id', $universe->id)->orderByDesc('is_default')->orderBy('name')->orderBy('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, ViewingOrderResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function show(Request $request, ViewingOrder $viewingOrder): JsonResponse
    {
        abort_unless(ViewingOrder::query()->visibleToPublic()->whereKey($viewingOrder)->exists(), 404);
        Gate::authorize('view', $viewingOrder);

        return ApiResponse::success($request, (new ViewingOrderResource($viewingOrder->load('items')))->resolve($request));
    }

    public function items(Request $request, ViewingOrder $viewingOrder): JsonResponse
    {
        abort_unless(ViewingOrder::query()->visibleToPublic()->whereKey($viewingOrder)->exists(), 404);
        Gate::authorize('view', $viewingOrder);

        return ApiResponse::success($request, (new ViewingOrderResource($viewingOrder->load('items')))->resolve($request)['items']);
    }
}

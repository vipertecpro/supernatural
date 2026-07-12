<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalTargetRequest;
use App\Http\Resources\Api\V1\FavouriteResource;
use App\Models\Favourite;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FavouriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Favourite::class);
        $paginator = Favourite::query()->where('user_id', $request->user()->id)->latest()->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, FavouriteResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StorePersonalTargetRequest $request, ManagePersonalLibrary $action): JsonResponse
    {
        Gate::authorize('create', Favourite::class);
        $favourite = $action->addFavourite($request->user(), $request->string('target_type')->toString(), $request->integer('target_id'));

        return ApiResponse::success($request, (new FavouriteResource($favourite))->resolve($request), status: 201);
    }

    public function destroy(Request $request, Favourite $favourite): JsonResponse
    {
        abort_unless($favourite->user_id === $request->user()->id, 404);
        Gate::authorize('delete', $favourite);
        $favourite->delete();

        return ApiResponse::success($request, null);
    }
}

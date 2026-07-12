<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalTargetRequest;
use App\Http\Resources\Api\V1\RatingResource;
use App\Models\Rating;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RatingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Rating::class);
        $paginator = Rating::query()->where('user_id', $request->user()->id)->latest('updated_at')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, RatingResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function upsert(StorePersonalTargetRequest $request, string $type, int $id, ManagePersonalLibrary $action): JsonResponse
    {
        Gate::authorize('create', Rating::class);
        $rating = $action->rate($request->user(), $type, $id, $request->integer('rating'));

        return ApiResponse::success($request, (new RatingResource($rating))->resolve($request));
    }

    public function destroy(Request $request, Rating $rating): JsonResponse
    {
        abort_unless($rating->user_id === $request->user()->id, 404);
        Gate::authorize('delete', $rating);
        $rating->delete();

        return ApiResponse::success($request, null);
    }
}

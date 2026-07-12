<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Queries\ContinueWatching;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ViewingProgressResource;
use App\Models\ViewingProgress;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContinueWatchingController extends Controller
{
    public function __invoke(Request $request, ContinueWatching $query): JsonResponse
    {
        Gate::authorize('viewAny', ViewingProgress::class);

        return ApiResponse::success($request, ViewingProgressResource::collection($query->forUser($request->user(), $request->integer('limit', 20)))->resolve($request));
    }
}

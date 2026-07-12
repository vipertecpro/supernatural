<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Media\Actions\CreateExternalEmbed;
use App\Domain\Media\Actions\TransitionMedia;
use App\Domain\Media\Actions\UpdateMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MediaTransitionRequest;
use App\Http\Requests\Api\V1\StoreExternalEmbedRequest;
use App\Http\Requests\Api\V1\UpdateMediaRequest;
use App\Http\Resources\Api\V1\ExternalEmbedResource;
use App\Models\ExternalEmbed;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExternalEmbedController extends Controller
{
    public function show(Request $request, ExternalEmbed $embed): JsonResponse
    {
        Gate::authorize('view', $embed);

        return ApiResponse::success($request, (new ExternalEmbedResource($embed))->resolve($request));
    }

    public function store(StoreExternalEmbedRequest $request, CreateExternalEmbed $action): JsonResponse
    {
        $embed = $action->handle($request->validated(), $request->user());

        return ApiResponse::success($request, (new ExternalEmbedResource($embed))->resolve($request), status: 201);
    }

    public function update(UpdateMediaRequest $request, ExternalEmbed $embed, UpdateMedia $action): JsonResponse
    {
        Gate::authorize('update', $embed);
        $embed = $action->handle($embed, $request->validated(), $request->user());

        return ApiResponse::success($request, (new ExternalEmbedResource($embed))->resolve($request));
    }

    public function publish(MediaTransitionRequest $request, ExternalEmbed $embed, TransitionMedia $action): JsonResponse
    {
        Gate::authorize('publish', $embed);
        $embed = $action->publish($embed, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new ExternalEmbedResource($embed))->resolve($request));
    }

    public function archive(MediaTransitionRequest $request, ExternalEmbed $embed, TransitionMedia $action): JsonResponse
    {
        Gate::authorize('archive', $embed);
        $embed = $action->archive($embed, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new ExternalEmbedResource($embed))->resolve($request));
    }
}

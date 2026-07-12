<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Media\Actions\CreateMediaAsset;
use App\Domain\Media\Actions\TransitionMedia;
use App\Domain\Media\Actions\UpdateMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MediaTransitionRequest;
use App\Http\Requests\Api\V1\StoreMediaAssetRequest;
use App\Http\Requests\Api\V1\UpdateMediaRequest;
use App\Http\Resources\Api\V1\MediaAssetResource;
use App\Models\MediaAsset;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MediaAssetController extends Controller
{
    public function show(Request $request, MediaAsset $asset): JsonResponse
    {
        Gate::authorize('view', $asset);

        return ApiResponse::success($request, (new MediaAssetResource($asset->load('variants')))->resolve($request));
    }

    public function store(StoreMediaAssetRequest $request, CreateMediaAsset $action): JsonResponse
    {
        $attributes = $request->safe()->except('file');
        $asset = $action->handle($request->file('file'), $attributes, $request->user());

        return ApiResponse::success($request, (new MediaAssetResource($asset->load('variants')))->resolve($request), status: 201);
    }

    public function update(UpdateMediaRequest $request, MediaAsset $asset, UpdateMedia $action): JsonResponse
    {
        Gate::authorize('update', $asset);
        $asset = $action->handle($asset, $request->validated(), $request->user());

        return ApiResponse::success($request, (new MediaAssetResource($asset->load('variants')))->resolve($request));
    }

    public function publish(MediaTransitionRequest $request, MediaAsset $asset, TransitionMedia $action): JsonResponse
    {
        Gate::authorize('publish', $asset);
        $asset = $action->publish($asset, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new MediaAssetResource($asset->load('variants')))->resolve($request));
    }

    public function archive(MediaTransitionRequest $request, MediaAsset $asset, TransitionMedia $action): JsonResponse
    {
        Gate::authorize('archive', $asset);
        $asset = $action->archive($asset, $request->user(), $request->expectedVersion());

        return ApiResponse::success($request, (new MediaAssetResource($asset->load('variants')))->resolve($request));
    }
}

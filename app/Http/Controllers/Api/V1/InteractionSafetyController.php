<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Actions\ManageInteractionSafety;
use App\Enums\UserMuteScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserBlockRequest;
use App\Http\Requests\Api\V1\StoreUserMuteRequest;
use App\Http\Resources\Api\V1\UserBlockResource;
use App\Http\Resources\Api\V1\UserMuteResource;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InteractionSafetyController extends Controller
{
    public function blocks(Request $request): JsonResponse
    {
        $paginator = UserBlock::query()->with('blocked')->where('blocker_user_id', $request->user()->id)->latest('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, UserBlockResource::collection($paginator)->resolve(), $paginator);
    }

    public function block(StoreUserBlockRequest $request, ManageInteractionSafety $action): JsonResponse
    {
        $block = $action->block($request->user(), User::query()->findOrFail($request->integer('target_user_id')), $request->string('reason_code')->toString() ?: null);

        return ApiResponse::success($request, (new UserBlockResource($block->load('blocked')))->resolve(), status: $block->wasRecentlyCreated ? 201 : 200);
    }

    public function unblock(Request $request, UserBlock $block, ManageInteractionSafety $action): JsonResponse
    {
        $action->unblock($block, $request->user());

        return ApiResponse::success($request, null);
    }

    public function mutes(Request $request): JsonResponse
    {
        $paginator = UserMute::query()->with('mutedUser')->active()->where('muting_user_id', $request->user()->id)->latest('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, UserMuteResource::collection($paginator)->resolve(), $paginator);
    }

    public function mute(StoreUserMuteRequest $request, ManageInteractionSafety $action): JsonResponse
    {
        $mute = $action->mute($request->user(), User::query()->findOrFail($request->integer('target_user_id')), UserMuteScope::from($request->string('scope')->toString()), $request->string('expires_at')->toString() ?: null);

        return ApiResponse::success($request, (new UserMuteResource($mute->load('mutedUser')))->resolve(), status: $mute->wasRecentlyCreated ? 201 : 200);
    }

    public function unmute(Request $request, UserMute $mute, ManageInteractionSafety $action): JsonResponse
    {
        $action->unmute($mute, $request->user());

        return ApiResponse::success($request, null);
    }
}

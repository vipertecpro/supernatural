<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Moderation\Actions\ManageAppeals;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DecideAppealRequest;
use App\Http\Requests\Api\V1\StoreAppealRequest;
use App\Http\Resources\Api\V1\AppealResource;
use App\Models\Appeal;
use App\Models\ModerationAction;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Appeal::class);
        $paginator = Appeal::query()->with('decision')->where('appellant_user_id', $request->user()->id)->latest('submitted_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, AppealResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreAppealRequest $request, ManageAppeals $action): JsonResponse
    {
        Gate::authorize('create', Appeal::class);
        $appeal = $action->submit($request->user(), ModerationAction::query()->findOrFail($request->integer('moderation_action_id')), $request->validated('explanation'));

        return ApiResponse::success($request, (new AppealResource($appeal))->resolve($request), status: 201);
    }

    public function show(Request $request, Appeal $appeal): JsonResponse
    {
        abort_unless($appeal->appellant_user_id === $request->user()->id, 404);
        Gate::authorize('view', $appeal);

        return ApiResponse::success($request, (new AppealResource($appeal->load('decision')))->resolve($request));
    }

    public function withdraw(Request $request, Appeal $appeal, ManageAppeals $action): JsonResponse
    {
        abort_unless($appeal->appellant_user_id === $request->user()->id, 404);
        Gate::authorize('update', $appeal);
        $updated = $action->withdraw($appeal, $request->user(), $request->integer('expected_version'));

        return ApiResponse::success($request, (new AppealResource($updated))->resolve($request));
    }

    public function moderationIndex(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('moderation.appeals.review'), 403);
        $paginator = Appeal::query()->with('decision')->latest('submitted_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, AppealResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function moderationShow(Request $request, Appeal $appeal): JsonResponse
    {
        Gate::authorize('view', $appeal);

        return ApiResponse::success($request, (new AppealResource($appeal->load('decision')))->resolve($request));
    }

    public function decide(DecideAppealRequest $request, Appeal $appeal, ManageAppeals $action): JsonResponse
    {
        Gate::authorize('decide', $appeal);
        $decision = $action->decide($appeal, $request->user(), $request->validated());

        return ApiResponse::success($request, ['appeal_id' => $appeal->id, 'decision' => $decision->type->value, 'user_visible_explanation' => $decision->user_visible_explanation], status: 201);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Moderation\Actions\ManageModerationCases;
use App\Enums\ModerationCaseStatus;
use App\Enums\PermissionName;
use App\Enums\RestrictionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplyModerationActionRequest;
use App\Http\Requests\Api\V1\AssignModerationCaseRequest;
use App\Http\Requests\Api\V1\StoreModerationCaseRequest;
use App\Http\Requests\Api\V1\UpdateModerationCaseRequest;
use App\Http\Resources\Api\V1\ModerationCaseResource;
use App\Models\ContentRestriction;
use App\Models\ModerationCase;
use App\Models\User;
use App\Models\UserRestriction;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModerationCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ModerationCase::class);
        $query = ModerationCase::query()->with(['reports.category', 'reports.evidence', 'assignments', 'actions']);
        if (! $request->user()->hasPermission(PermissionName::ModerationReportsTriage)) {
            $query->whereHas('assignments', fn ($builder) => $builder->where('moderator_user_id', $request->user()->id)->whereNull('cancelled_at'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority')->toString());
        }
        $paginator = $query->latest('opened_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, ModerationCaseResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreModerationCaseRequest $request, ManageModerationCases $action): JsonResponse
    {
        Gate::authorize('create', ModerationCase::class);
        $case = $action->open($request->user(), $request->validated());

        return ApiResponse::success($request, (new ModerationCaseResource($case))->resolve($request), status: 201);
    }

    public function show(Request $request, ModerationCase $case): JsonResponse
    {
        Gate::authorize('view', $case);

        return ApiResponse::success($request, (new ModerationCaseResource($case->load(['reports.category', 'reports.evidence', 'assignments', 'actions'])))->resolve($request));
    }

    public function update(UpdateModerationCaseRequest $request, ModerationCase $case, ManageModerationCases $action): JsonResponse
    {
        Gate::authorize('update', $case);
        $updated = $action->transition($case, ModerationCaseStatus::from($request->validated('status')), $request->integer('expected_version'), $request->user(), $request->validated());

        return ApiResponse::success($request, (new ModerationCaseResource($updated))->resolve($request));
    }

    public function assign(AssignModerationCaseRequest $request, ModerationCase $case, ManageModerationCases $action): JsonResponse
    {
        Gate::authorize('assign', $case);
        $assignment = $action->assign($case, User::query()->findOrFail($request->integer('moderator_user_id')), $request->user(), $request->validated('private_note'));

        return ApiResponse::success($request, ['id' => $assignment->id, 'moderator_user_id' => $assignment->moderator_user_id, 'status' => $assignment->status->value], status: 201);
    }

    public function action(ApplyModerationActionRequest $request, ModerationCase $case, ManageModerationCases $action): JsonResponse
    {
        Gate::authorize('applyAction', $case);
        $record = $action->applyAction($case, $request->user(), $request->validated());

        return ApiResponse::success($request, ['id' => $record->id, 'type' => $record->type->value, 'reason_code' => $record->reason_code], status: 201);
    }

    public function liftUserRestriction(Request $request, UserRestriction $restriction, ManageModerationCases $action): JsonResponse
    {
        abort_unless($request->user()->hasPermission(PermissionName::ModerationUserRestrictionsApply), 403);
        abort_unless($restriction->status === RestrictionStatus::Active, 409);
        $record = $action->liftUserRestriction($restriction, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }

    public function liftContentRestriction(Request $request, ContentRestriction $restriction, ManageModerationCases $action): JsonResponse
    {
        abort_unless($request->user()->hasPermission(PermissionName::ModerationContentRestrictionsApply), 403);
        abort_unless($restriction->status === RestrictionStatus::Active, 409);
        $record = $action->liftContentRestriction($restriction, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }
}

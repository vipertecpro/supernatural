<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Editorial\Actions\ApplyEditorialRevision;
use App\Domain\Editorial\Actions\AssignEditorialReview;
use App\Domain\Editorial\Actions\DecideEditorialRevision;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignEditorialReviewRequest;
use App\Http\Requests\Api\V1\DecideEditorialRevisionRequest;
use App\Http\Resources\Api\V1\EditorialRevisionResource;
use App\Models\EditorialRevision;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditorialReviewController extends Controller
{
    public function assign(AssignEditorialReviewRequest $request, EditorialRevision $revision, AssignEditorialReview $action): JsonResponse
    {
        $reviewer = User::query()->findOrFail($request->integer('reviewer_user_id'));
        $assignment = $action->handle($revision, $reviewer, $request->user(), $request->string('internal_note')->toString() ?: null, $request->string('due_at')->toString() ?: null);

        return ApiResponse::success($request, ['id' => $assignment->id, 'reviewer_user_id' => $assignment->reviewer_user_id, 'status' => $assignment->status->value], status: 201);
    }

    public function begin(Request $request, EditorialRevision $revision, DecideEditorialRevision $action): JsonResponse
    {
        Gate::authorize('review', $revision);

        return $this->response($request, $action->beginReview($revision, $request->user()));
    }

    public function cancelAssignment(Request $request, EditorialRevision $revision, ReviewAssignment $assignment, AssignEditorialReview $action): JsonResponse
    {
        Gate::authorize('assign', $revision);
        abort_unless($assignment->editorial_revision_id === $revision->id, 404);
        $cancelled = $action->cancel($assignment, $request->user());

        return ApiResponse::success($request, ['id' => $cancelled->id, 'status' => $cancelled->status->value]);
    }

    public function requestChanges(DecideEditorialRevisionRequest $request, EditorialRevision $revision, DecideEditorialRevision $action): JsonResponse
    {
        Gate::authorize('review', $revision);

        return $this->response($request, $action->requestChanges($revision, $request->user(), $request->string('explanation')->toString(), $request->string('private_note')->toString() ?: null, $request->array('findings')));
    }

    public function approve(DecideEditorialRevisionRequest $request, EditorialRevision $revision, DecideEditorialRevision $action): JsonResponse
    {
        Gate::authorize('approve', $revision);

        return $this->response($request, $action->approve($revision, $request->user(), $request->string('explanation')->toString(), $request->string('private_note')->toString() ?: null, $request->array('findings')));
    }

    public function reject(DecideEditorialRevisionRequest $request, EditorialRevision $revision, DecideEditorialRevision $action): JsonResponse
    {
        Gate::authorize('review', $revision);

        return $this->response($request, $action->reject($revision, $request->user(), $request->string('explanation')->toString(), $request->string('private_note')->toString() ?: null, $request->array('findings')));
    }

    public function apply(Request $request, EditorialRevision $revision, ApplyEditorialRevision $action): JsonResponse
    {
        Gate::authorize('apply', $revision);

        return $this->response($request, $action->handle($revision, $request->user()));
    }

    private function response(Request $request, EditorialRevision $revision): JsonResponse
    {
        return ApiResponse::success($request, (new EditorialRevisionResource($revision->load(['revisable', 'items', 'blocks', 'assignments', 'actions'])))->resolve($request));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Editorial\Actions\CreateEditorialRevision;
use App\Domain\Editorial\Actions\TransitionEditorialRevision;
use App\Domain\Editorial\Actions\UpsertRevisionBlock;
use App\Domain\Editorial\Actions\UpsertRevisionItem;
use App\Enums\PermissionName;
use App\Enums\RevisionOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EditorialIndexRequest;
use App\Http\Requests\Api\V1\StoreEditorialRevisionRequest;
use App\Http\Requests\Api\V1\StoreRevisionBlockRequest;
use App\Http\Requests\Api\V1\StoreRevisionItemRequest;
use App\Http\Requests\Api\V1\UpdateEditorialRevisionRequest;
use App\Http\Resources\Api\V1\EditorialRevisionResource;
use App\Models\EditorialRevision;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditorialRevisionController extends Controller
{
    public function index(EditorialIndexRequest $request): JsonResponse
    {
        $query = EditorialRevision::query()->with(['revisable', 'items', 'blocks', 'assignments', 'actions']);
        if (! $request->user()->hasPermission(PermissionName::EditorialRevisionsViewAll)) {
            $query->where('author_user_id', $request->user()->id);
        }
        $query->when($request->string('status')->isNotEmpty(), fn ($items) => $items->where('status', $request->string('status')->toString()))
            ->when($request->string('target_type')->isNotEmpty(), fn ($items) => $items->where('revisable_type', $request->string('target_type')->toString()));
        $paginator = $query->orderByDesc('id')->cursorPaginate($request->integer('page.size', 20));

        return ApiResponse::cursor($request, EditorialRevisionResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreEditorialRevisionRequest $request, CreateEditorialRevision $action): JsonResponse
    {
        $target = $this->resolveTarget($request->string('target_type')->toString(), $request->integer('target_id'));
        Gate::authorize('update', $target);
        $revision = $action->handle($target, [
            'summary' => $request->string('summary')->toString(),
            'parent_revision_id' => $request->filled('parent_revision_id') ? $request->integer('parent_revision_id') : null,
            'metadata' => $request->input('metadata'),
        ], $request->user())
            ->load(['revisable', 'items', 'blocks', 'assignments', 'actions']);

        return ApiResponse::success($request, (new EditorialRevisionResource($revision))->resolve($request), status: 201);
    }

    public function show(Request $request, EditorialRevision $revision): JsonResponse
    {
        Gate::authorize('view', $revision);

        return ApiResponse::success($request, (new EditorialRevisionResource($revision->load(['revisable', 'items', 'blocks', 'assignments', 'actions'])))->resolve($request));
    }

    public function update(UpdateEditorialRevisionRequest $request, EditorialRevision $revision): JsonResponse
    {
        $revision->update($request->validated());

        return ApiResponse::success($request, (new EditorialRevisionResource($revision->fresh()->load(['revisable', 'items', 'blocks', 'assignments', 'actions'])))->resolve($request));
    }

    public function storeItem(StoreRevisionItemRequest $request, EditorialRevision $revision, UpsertRevisionItem $action): JsonResponse
    {
        $item = $action->handle($revision, $request->string('field')->toString(), $request->input('value'), RevisionOperation::from($request->string('operation', 'replace')->toString()), $request->integer('position'));

        return ApiResponse::success($request, ['id' => $item->id, 'field' => $item->field, 'operation' => $item->operation->value, 'proposed_value' => $item->proposed_value['value'] ?? null], status: 201);
    }

    public function updateItem(StoreRevisionItemRequest $request, EditorialRevision $revision, RevisionItem $item, UpsertRevisionItem $action): JsonResponse
    {
        abort_unless($item->editorial_revision_id === $revision->id, 404);
        $updated = $action->handle($revision, $item->field, $request->input('value'), RevisionOperation::from($request->string('operation', 'replace')->toString()), $request->integer('position', $item->position));

        return ApiResponse::success($request, ['id' => $updated->id, 'field' => $updated->field, 'operation' => $updated->operation->value, 'proposed_value' => $updated->proposed_value['value'] ?? null]);
    }

    public function destroyItem(Request $request, EditorialRevision $revision, RevisionItem $item): JsonResponse
    {
        Gate::authorize('update', $revision);
        abort_unless($item->editorial_revision_id === $revision->id, 404);
        $item->delete();

        return response()->json(null, 204);
    }

    public function storeBlock(StoreRevisionBlockRequest $request, EditorialRevision $revision, UpsertRevisionBlock $action): JsonResponse
    {
        $block = $action->handle($revision, $request->string('field')->toString(), $request->string('locale')->toString() ?: null, $request->string('text')->toString(), $request->integer('position'));

        return ApiResponse::success($request, ['id' => $block->id, 'field' => $block->field, 'locale' => $block->locale, 'proposed_text' => $block->proposed_text], status: 201);
    }

    public function destroyBlock(Request $request, EditorialRevision $revision, RevisionBlock $block): JsonResponse
    {
        Gate::authorize('update', $revision);
        abort_unless($block->editorial_revision_id === $revision->id, 404);
        $block->delete();

        return response()->json(null, 204);
    }

    public function submit(Request $request, EditorialRevision $revision, TransitionEditorialRevision $action): JsonResponse
    {
        Gate::authorize('submit', $revision);

        return $this->transitionResponse($request, $action->submit($revision, $request->user()));
    }

    public function resubmit(Request $request, EditorialRevision $revision, TransitionEditorialRevision $action): JsonResponse
    {
        Gate::authorize('submit', $revision);

        return $this->transitionResponse($request, $action->resubmit($revision, $request->user()));
    }

    public function withdraw(Request $request, EditorialRevision $revision, TransitionEditorialRevision $action): JsonResponse
    {
        Gate::authorize('submit', $revision);

        return $this->transitionResponse($request, $action->withdraw($revision, $request->user()));
    }

    private function transitionResponse(Request $request, EditorialRevision $revision): JsonResponse
    {
        return ApiResponse::success($request, (new EditorialRevisionResource($revision->load(['revisable', 'items', 'blocks', 'assignments', 'actions'])))->resolve($request));
    }

    private function resolveTarget(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);
        abort_if($class === null, 404);

        return $class::query()->findOrFail($id);
    }
}

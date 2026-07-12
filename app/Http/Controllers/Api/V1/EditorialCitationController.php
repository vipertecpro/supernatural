<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Editorial\Actions\CreateCitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCitationRequest;
use App\Http\Resources\Api\V1\CitationResource;
use App\Models\Citation;
use App\Models\EditorialRevision;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditorialCitationController extends Controller
{
    public function index(Request $request, EditorialRevision $revision): JsonResponse
    {
        Gate::authorize('view', $revision);
        $citations = Citation::query()->with('citationSources.source')->where(function ($query) use ($revision): void {
            $query->where(fn ($own) => $own->where('citable_type', 'editorial_revision')->where('citable_id', $revision->id))
                ->orWhere(fn ($items) => $items->where('citable_type', 'revision_item')->whereIn('citable_id', $revision->items()->select('id')))
                ->orWhere(fn ($blocks) => $blocks->where('citable_type', 'revision_block')->whereIn('citable_id', $revision->blocks()->select('id')));
        })->orderBy('id')->get();

        return ApiResponse::success($request, CitationResource::collection($citations)->resolve($request));
    }

    public function store(StoreCitationRequest $request, EditorialRevision $revision, CreateCitation $action): JsonResponse
    {
        $target = $this->resolveTarget($request->string('target_type')->toString(), $request->integer('target_id'));
        $attributes = $request->safe()->except(['target_type', 'target_id', 'source_ids']);
        $sourceIds = array_values(array_map(fn (mixed $id): int => (int) $id, $request->array('source_ids')));
        $citation = $action->handle($target, $attributes, $sourceIds, $request->user(), $revision);

        return ApiResponse::success($request, (new CitationResource($citation))->resolve($request), status: 201);
    }

    public function destroy(Request $request, Citation $citation): JsonResponse
    {
        $revision = $this->revisionFor($citation->citable);
        Gate::authorize('manageCitations', $revision);
        abort_unless($revision->status->isEditable(), 409);
        $citation->citationSources()->delete();
        $citation->delete();

        return response()->json(null, 204);
    }

    private function resolveTarget(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);
        abort_if($class === null, 404);

        return $class::query()->findOrFail($id);
    }

    private function revisionFor(Model $target): EditorialRevision
    {
        if ($target instanceof EditorialRevision) {
            return $target;
        }

        if ($target instanceof RevisionItem || $target instanceof RevisionBlock) {
            return $target->revision()->firstOrFail();
        }

        abort(404);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Spoilers\Actions\UpsertSpoilerBoundary;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSpoilerBoundaryRequest;
use App\Http\Resources\Api\V1\SpoilerBoundaryResource;
use App\Models\Episode;
use App\Models\Season;
use App\Models\SpoilerBoundary;
use App\Models\SpoilerConstraint;
use App\Models\Work;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SpoilerBoundaryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', SpoilerBoundary::class);
        $paginator = SpoilerBoundary::query()->with('constraint')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, SpoilerBoundaryResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreSpoilerBoundaryRequest $request, UpsertSpoilerBoundary $action): JsonResponse
    {
        $boundary = $this->persist($request, $action);

        return ApiResponse::success($request, (new SpoilerBoundaryResource($boundary))->resolve($request), status: 201);
    }

    public function update(StoreSpoilerBoundaryRequest $request, SpoilerBoundary $boundary, UpsertSpoilerBoundary $action): JsonResponse
    {
        Gate::authorize('update', $boundary);
        $request->merge(['constraint_id' => $boundary->spoiler_constraint_id]);

        return ApiResponse::success($request, (new SpoilerBoundaryResource($this->persist($request, $action, $boundary->constraint)))->resolve($request));
    }

    private function persist(StoreSpoilerBoundaryRequest $request, UpsertSpoilerBoundary $action, ?SpoilerConstraint $constraint = null): SpoilerBoundary
    {
        $target = $this->resolveTarget($request->string('target_type')->toString(), $request->integer('target_id'));
        $constraint ??= $request->filled('constraint_id') ? SpoilerConstraint::query()->findOrFail($request->integer('constraint_id')) : null;

        return $action->handle(
            $target,
            Work::query()->findOrFail($request->integer('work_id')),
            $request->filled('season_id') ? Season::query()->findOrFail($request->integer('season_id')) : null,
            $request->filled('episode_id') ? Episode::query()->findOrFail($request->integer('episode_id')) : null,
            SpoilerSeverity::from($request->string('severity')->toString()),
            SpoilerClassificationStatus::from($request->string('classification_status')->toString()),
            $request->user(),
            $request->string('warning')->toString() ?: null,
            $constraint,
        );
    }

    private function resolveTarget(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);
        abort_if($class === null, 404);

        return $class::query()->findOrFail($id);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\RecordViewingProgress;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateViewingProgressRequest;
use App\Http\Resources\Api\V1\ViewingProgressResource;
use App\Models\ViewingProgress;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ViewingProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ViewingProgress::class);
        $query = ViewingProgress::query()->where('user_id', $request->user()->id);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        } $paginator = $query->latest('last_watched_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, ViewingProgressResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function show(Request $request, string $type, int $id): JsonResponse
    {
        $progress = ViewingProgress::query()->where('user_id', $request->user()->id)->where('cycle_key', 0)->where('scope_key', $type.':'.$id)->firstOrFail();
        Gate::authorize('view', $progress);

        return ApiResponse::success($request, (new ViewingProgressResource($progress))->resolve($request));
    }

    public function update(UpdateViewingProgressRequest $request, string $type, int $id, RecordViewingProgress $action): JsonResponse
    {
        Gate::authorize('create', ViewingProgress::class);
        $progress = $action->handle($request->user(), $type, $id, $request->validated());

        return ApiResponse::success($request, (new ViewingProgressResource($progress))->resolve($request));
    }

    public function complete(UpdateViewingProgressRequest $request, string $type, int $id, RecordViewingProgress $action): JsonResponse
    {
        Gate::authorize('create', ViewingProgress::class);
        $progress = $action->handle($request->user(), $type, $id, [...$request->validated(), 'status' => ProgressStatus::Completed->value]);

        return ApiResponse::success($request, (new ViewingProgressResource($progress))->resolve($request));
    }

    public function correct(UpdateViewingProgressRequest $request, string $type, int $id, RecordViewingProgress $action): JsonResponse
    {
        Gate::authorize('create', ViewingProgress::class);
        $progress = $action->handle($request->user(), $type, $id, [...$request->validated(), 'allow_backward' => true, 'source' => ProgressSource::Manual->value]);

        return ApiResponse::success($request, (new ViewingProgressResource($progress))->resolve($request));
    }

    public function reset(UpdateViewingProgressRequest $request, string $type, int $id, RecordViewingProgress $action): JsonResponse
    {
        $progress = ViewingProgress::query()->where('user_id', $request->user()->id)->where('cycle_key', 0)->where('scope_key', $type.':'.$id)->firstOrFail();
        Gate::authorize('update', $progress);
        $progress = $action->reset($request->user(), $progress, $request->integer('expected_version'), $request->boolean('reset_spoiler_knowledge'));

        return ApiResponse::success($request, (new ViewingProgressResource($progress))->resolve($request));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Editorial\Actions\RecordSourceRightsReview;
use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSourceRightsReviewRequest;
use App\Http\Resources\Api\V1\SourceRightsReviewResource;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SourceRightsReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', SourceRightsReview::class);
        $paginator = SourceRightsReview::query()->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, SourceRightsReviewResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreSourceRightsReviewRequest $request, RecordSourceRightsReview $action): JsonResponse
    {
        $source = Source::query()->findOrFail($request->integer('source_id'));
        $review = $action->handle(
            $source,
            RightsUseType::from($request->string('use_type')->toString()),
            RightsDecision::from($request->string('decision')->toString()),
            $request->safe()->except(['source_id', 'use_type', 'decision']),
            $request->user(),
        );

        return ApiResponse::success($request, (new SourceRightsReviewResource($review))->resolve($request), status: 201);
    }

    public function show(Request $request, SourceRightsReview $assessment): JsonResponse
    {
        Gate::authorize('view', $assessment);

        return ApiResponse::success($request, (new SourceRightsReviewResource($assessment))->resolve($request));
    }
}

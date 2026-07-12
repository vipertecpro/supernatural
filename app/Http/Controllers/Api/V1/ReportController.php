<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Moderation\Actions\ManageReports;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportEvidenceRequest;
use App\Http\Requests\Api\V1\StoreReportRequest;
use App\Http\Resources\Api\V1\ReportCategoryResource;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        return ApiResponse::success($request, ReportCategoryResource::collection(ReportCategory::query()->where('is_active', true)->orderBy('name')->get())->resolve($request));
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);
        $paginator = Report::query()->with(['category', 'evidence'])->where('reporter_user_id', $request->user()->id)->latest('submitted_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, ReportResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StoreReportRequest $request, ManageReports $action): JsonResponse
    {
        Gate::authorize('create', Report::class);
        $report = $action->submit($request->user(), $request->validated(), $request->attributes->get('request_id'));

        return ApiResponse::success($request, (new ReportResource($report))->resolve($request), status: 201);
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        abort_unless($report->reporter_user_id === $request->user()->id, 404);
        Gate::authorize('view', $report);

        return ApiResponse::success($request, (new ReportResource($report->load(['category', 'evidence'])))->resolve($request));
    }

    public function withdraw(Request $request, Report $report, ManageReports $action): JsonResponse
    {
        abort_unless($report->reporter_user_id === $request->user()->id, 404);
        Gate::authorize('update', $report);

        return ApiResponse::success($request, (new ReportResource($action->withdraw($report, $request->user())))->resolve($request));
    }

    public function evidence(StoreReportEvidenceRequest $request, Report $report, ManageReports $action): JsonResponse
    {
        abort_unless($report->reporter_user_id === $request->user()->id, 404);
        Gate::authorize('update', $report);
        $evidence = $action->addEvidence($report, $request->user(), $request->validated());

        return ApiResponse::success($request, ['id' => $evidence->id, 'type' => $evidence->type->value], status: 201);
    }
}

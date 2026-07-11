<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Actions\UpsertWorkTranslation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PublishCatalogRequest;
use App\Http\Requests\Api\V1\StoreWorkTranslationRequest;
use App\Http\Requests\Api\V1\UpdateWorkTranslationRequest;
use App\Http\Resources\Api\V1\WorkTranslationResource;
use App\Models\Work;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class WorkTranslationController extends Controller
{
    public function store(StoreWorkTranslationRequest $request, Work $work, UpsertWorkTranslation $action): JsonResponse
    {
        $attributes = $request->safe()->except('locale');
        $translation = $action->handle($work, $request->string('locale')->toString(), $attributes, $request->user());

        return ApiResponse::success($request, (new WorkTranslationResource($translation))->resolve($request), status: 201);
    }

    public function update(UpdateWorkTranslationRequest $request, Work $work, string $locale, UpsertWorkTranslation $action): JsonResponse
    {
        $normalizedLocale = str($locale)->replace('_', '-')->lower()->toString();
        abort_unless($work->translations()->where('locale', $normalizedLocale)->exists(), 404);
        $translation = $action->handle($work, $normalizedLocale, $request->validated(), $request->user());

        return ApiResponse::success($request, (new WorkTranslationResource($translation))->resolve($request));
    }

    public function publish(PublishCatalogRequest $request, Work $work, string $locale, TransitionCatalogRecord $action): JsonResponse
    {
        $translation = $work->translations()->where('locale', str($locale)->replace('_', '-')->lower())->firstOrFail();
        Gate::authorize('publish', $translation);

        return ApiResponse::success($request, (new WorkTranslationResource($action->publish($translation, $request->user())))->resolve($request));
    }
}

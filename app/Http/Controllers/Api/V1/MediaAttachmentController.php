<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Media\Actions\AttachMedia;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MediaTransitionRequest;
use App\Http\Requests\Api\V1\StoreMediaAttachmentRequest;
use App\Http\Resources\Api\V1\MediaAttachmentResource;
use App\Models\ExternalEmbed;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MediaAttachmentController extends Controller
{
    public function index(Request $request, string $targetType, int $targetId): JsonResponse
    {
        abort_unless(in_array($targetType, ['universe', 'franchise', 'work', 'work_translation', 'season', 'episode', 'lore_entity', 'lore_entity_translation', 'lore_alias', 'entity_appearance', 'lore_relationship', 'timeline', 'timeline_entry'], true), 404);
        $class = Relation::getMorphedModel($targetType);
        abort_if($class === null || ! $class::query()->whereKey($targetId)->exists(), 404);
        $assetIds = MediaAsset::query()->visibleToPublic()->select('id');
        $embedIds = ExternalEmbed::query()->visibleToPublic()->select('id');
        $items = MediaAttachment::query()->with(['mediaAsset.variants', 'externalEmbed', 'spoilerConstraints.boundaries'])->where('attachable_type', $targetType)->where('attachable_id', $targetId)->where('status', PublicationStatus::Published)
            ->where(fn ($query) => $query->whereIn('media_asset_id', $assetIds)->orWhereIn('external_embed_id', $embedIds))
            ->orderBy('role')->orderBy('position')->orderBy('id')->limit(50)->get()
            ->reject(fn (MediaAttachment $attachment): bool => in_array(app(SpoilerVisibilityService::class)->decide($attachment, $request->user()), [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true))->values();

        return ApiResponse::success($request, MediaAttachmentResource::collection($items)->resolve($request));
    }

    public function store(StoreMediaAttachmentRequest $request, AttachMedia $action): JsonResponse
    {
        $attachment = $action->create($request->validated(), $request->user())->load(['mediaAsset.variants', 'externalEmbed']);

        return ApiResponse::success($request, (new MediaAttachmentResource($attachment))->resolve($request), status: 201);
    }

    public function publish(MediaTransitionRequest $request, MediaAttachment $attachment, AttachMedia $action): JsonResponse
    {
        Gate::authorize('publish', $attachment);
        $attachment = $action->publish($attachment, $request->user(), $request->expectedVersion())->load(['mediaAsset.variants', 'externalEmbed']);

        return ApiResponse::success($request, (new MediaAttachmentResource($attachment))->resolve($request));
    }

    public function destroy(Request $request, MediaAttachment $attachment, AttachMedia $action): JsonResponse
    {
        Gate::authorize('delete', $attachment);
        $action->delete($attachment, $request->user());

        return response()->json(null, 204);
    }
}

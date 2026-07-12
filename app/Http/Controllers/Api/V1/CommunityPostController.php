<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Community\Actions\ManageCommunityContent;
use App\Domain\Community\Queries\CommunityFeed;
use App\Enums\CommunityCommentStatus;
use App\Enums\CommunityPostStatus;
use App\Enums\SpoilerVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCommunityCommentRequest;
use App\Http\Requests\Api\V1\StoreCommunityPostRequest;
use App\Http\Requests\Api\V1\UpdateCommunityCommentRequest;
use App\Http\Requests\Api\V1\UpdateCommunityPostRequest;
use App\Http\Resources\Api\V1\CommunityCommentResource;
use App\Http\Resources\Api\V1\CommunityPostResource;
use App\Models\Bunker;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Universe;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Gate;

class CommunityPostController extends Controller
{
    public function feed(Request $request, CommunityFeed $query): JsonResponse
    {
        return $this->feedResponse($request, $query->handle($request->user(), size: $request->integer('page.size', 20)));
    }

    public function universeFeed(Request $request, Universe $universe, CommunityFeed $query): JsonResponse
    {
        return $this->feedResponse($request, $query->handle($request->user(), $universe, size: $request->integer('page.size', 20)));
    }

    public function bunkerFeed(Request $request, Bunker $bunker, CommunityFeed $query): JsonResponse
    {
        if ($bunker->visibility->value !== 'public') {
            abort_unless($request->user() !== null && Gate::forUser($request->user())->allows('view', $bunker), 404);
        }

        return $this->feedResponse($request, $query->handle($request->user(), bunker: $bunker, size: $request->integer('page.size', 20)));
    }

    public function show(Request $request, CommunityPost $post, SpoilerVisibilityService $spoilers): JsonResponse
    {
        if ($post->bunker !== null && $post->bunker->visibility->value !== 'public') {
            abort_unless($request->user() !== null && Gate::forUser($request->user())->allows('view', $post), 404);
        } abort_unless($post->status === CommunityPostStatus::Published, 404);
        $decision = $spoilers->decide($post, $request->user());
        abort_if($decision === SpoilerVisibility::Hidden, 404);
        $post->setAttribute('viewer_spoiler_visibility', $decision->value);

        return ApiResponse::success($request, (new CommunityPostResource($post->load(['author', 'polls.options'])))->resolve($request));
    }

    public function store(StoreCommunityPostRequest $request, ManageCommunityContent $action): JsonResponse
    {
        Gate::authorize('create', CommunityPost::class);

        return ApiResponse::success($request, (new CommunityPostResource($action->createPost($request->user(), $request->validated())))->resolve($request), status: 201);
    }

    public function update(UpdateCommunityPostRequest $request, CommunityPost $post, ManageCommunityContent $action): JsonResponse
    {
        Gate::authorize('update', $post);

        return ApiResponse::success($request, (new CommunityPostResource($action->updatePost($post, $request->user(), $request->validated())))->resolve($request));
    }

    public function destroy(Request $request, CommunityPost $post): JsonResponse
    {
        Gate::authorize('delete', $post);
        $post->update(['status' => $post->author_user_id === $request->user()->id ? CommunityPostStatus::Deleted : CommunityPostStatus::Removed, 'removed_at' => now(), 'lock_version' => $post->lock_version + 1]);
        $post->delete();

        return ApiResponse::success($request, ['id' => $post->id, 'deleted' => true]);
    }

    public function lock(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->setLock($request, $post, true);
    }

    public function unlock(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->setLock($request, $post, false);
    }

    public function comments(Request $request, CommunityPost $post, SpoilerVisibilityService $spoilers): JsonResponse
    {
        $this->show($request, $post, $spoilers);
        $paginator = $post->comments()->with('author')->where('status', CommunityCommentStatus::Published)->withoutActivePublicRestriction()->orderBy('created_at')->orderBy('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));
        $paginator->setCollection($paginator->getCollection()->map(function (CommunityComment $comment) use ($request, $spoilers): CommunityComment {
            $comment->setAttribute('viewer_spoiler_visibility', $spoilers->decide($comment, $request->user())->value);

            return $comment;
        })->reject(fn (CommunityComment $comment): bool => $comment->getAttribute('viewer_spoiler_visibility') === SpoilerVisibility::Hidden->value)->values());

        return ApiResponse::cursor($request, CommunityCommentResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function storeComment(StoreCommunityCommentRequest $request, CommunityPost $post, ManageCommunityContent $action): JsonResponse
    {
        Gate::authorize('create', CommunityComment::class);

        return ApiResponse::success($request, (new CommunityCommentResource($action->createComment($post, $request->user(), $request->validated())))->resolve($request), status: 201);
    }

    public function updateComment(UpdateCommunityCommentRequest $request, CommunityComment $comment, ManageCommunityContent $action): JsonResponse
    {
        Gate::authorize('update', $comment);

        return ApiResponse::success($request, (new CommunityCommentResource($action->updateComment($comment, $request->user(), $request->validated())))->resolve($request));
    }

    public function destroyComment(Request $request, CommunityComment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);
        $comment->update(['status' => $comment->author_user_id === $request->user()->id ? CommunityCommentStatus::Deleted : CommunityCommentStatus::Removed, 'body' => '', 'body_checksum' => hash('sha256', ''), 'removed_at' => now(), 'lock_version' => $comment->lock_version + 1]);

        return ApiResponse::success($request, ['id' => $comment->id, 'deleted' => true]);
    }

    private function setLock(Request $request, CommunityPost $post, bool $locked): JsonResponse
    {
        Gate::authorize('moderate', $post);
        $post->update(['locked_at' => $locked ? now() : null, 'lock_version' => $post->lock_version + 1]);

        return ApiResponse::success($request, ['id' => $post->id, 'locked' => $locked, 'lock_version' => $post->lock_version]);
    }

    /** @param CursorPaginator<int, CommunityPost> $paginator */
    private function feedResponse(Request $request, CursorPaginator $paginator): JsonResponse
    {
        return ApiResponse::cursor($request, CommunityPostResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }
}

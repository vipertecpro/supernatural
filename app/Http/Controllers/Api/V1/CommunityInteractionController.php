<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Community\Actions\ManageCommunityContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CommunityInteractionRequest;
use App\Http\Requests\Api\V1\StoreCommunityPollRequest;
use App\Http\Requests\Api\V1\VoteCommunityPollRequest;
use App\Http\Resources\Api\V1\CommunityPollResource;
use App\Models\CommunityBookmark;
use App\Models\CommunityComment;
use App\Models\CommunityPoll;
use App\Models\CommunityPost;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityInteractionController extends Controller
{
    public function react(CommunityInteractionRequest $request, string $type, int $id, string $reaction, ManageCommunityContent $action): JsonResponse
    {
        $record = $action->react($this->target($type, $id), $request->user(), $reaction);

        return ApiResponse::success($request, ['id' => $record->id, 'reaction' => $record->type->value], status: 201);
    }

    public function unreact(CommunityInteractionRequest $request, string $type, int $id, string $reaction, ManageCommunityContent $action): JsonResponse
    {
        $action->unreact($this->target($type, $id), $request->user(), $reaction);

        return ApiResponse::success($request, ['deleted' => true]);
    }

    public function bookmarks(Request $request): JsonResponse
    {
        $paginator = CommunityBookmark::query()->where('user_id', $request->user()->id)->latest('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, $paginator->getCollection()->map(fn (CommunityBookmark $bookmark): array => ['id' => $bookmark->id, 'target' => ['type' => $bookmark->bookmarkable_type, 'id' => $bookmark->bookmarkable_id]])->all(), $paginator);
    }

    public function bookmark(Request $request, CommunityPost $post, ManageCommunityContent $action): JsonResponse
    {
        $bookmark = $action->bookmark($post, $request->user());

        return ApiResponse::success($request, ['id' => $bookmark->id, 'post_id' => $post->id], status: 201);
    }

    public function removeBookmark(Request $request, CommunityBookmark $bookmark): JsonResponse
    {
        abort_unless($bookmark->user_id === $request->user()->id, 404);
        $bookmark->delete();

        return ApiResponse::success($request, ['deleted' => true]);
    }

    public function createPoll(StoreCommunityPollRequest $request, CommunityPost $post, ManageCommunityContent $action): JsonResponse
    {
        return ApiResponse::success($request, (new CommunityPollResource($action->createPoll($post, $request->user(), $request->validated())))->resolve($request), status: 201);
    }

    public function vote(VoteCommunityPollRequest $request, CommunityPoll $poll, ManageCommunityContent $action): JsonResponse
    {
        return ApiResponse::success($request, (new CommunityPollResource($action->vote($poll, $request->user(), $request->validated('option_ids'))))->resolve($request));
    }

    public function removeVote(Request $request, CommunityPoll $poll): JsonResponse
    {
        $poll->votes()->where('user_id', $request->user()->id)->delete();

        return ApiResponse::success($request, ['deleted' => true]);
    }

    public function close(VoteCommunityPollRequest $request, CommunityPoll $poll, ManageCommunityContent $action): JsonResponse
    {
        abort_unless($poll->post->author_user_id === $request->user()->id, 403);

        return ApiResponse::success($request, (new CommunityPollResource($action->closePoll($poll, $request->user(), $request->integer('lock_version'))))->resolve($request));
    }

    private function target(string $type, int $id): Model
    {
        return match ($type) {
            'post' => CommunityPost::query()->findOrFail($id), 'comment' => CommunityComment::query()->findOrFail($id), default => abort(404)
        };
    }
}

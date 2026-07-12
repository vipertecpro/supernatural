<?php

namespace App\Domain\Community\Queries;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Enums\CommunityPostStatus;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerVisibility;
use App\Models\Bunker;
use App\Models\CommunityPost;
use App\Models\ContentRestriction;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;

class CommunityFeed
{
    public function __construct(private readonly SpoilerVisibilityService $spoilers) {}

    /** @return CursorPaginator<int, CommunityPost> */
    public function handle(?User $viewer, ?Universe $universe = null, ?Bunker $bunker = null, int $size = 20): CursorPaginator
    {
        $paginator = CommunityPost::query()->with(['author', 'bunker', 'spoilerConstraints.boundaries', 'polls.options'])
            ->withoutActivePublicRestriction()->where('status', CommunityPostStatus::Published)->whereNotNull('published_at')
            ->whereHas('universe', fn ($query) => $query->where(['status' => PublicationStatus::Published, 'is_public' => true]))
            ->when($universe, fn ($query) => $query->where('universe_id', $universe->id))
            ->when($bunker, fn ($query) => $query->where('bunker_id', $bunker->id))
            ->when($viewer !== null, function ($query) use ($viewer): void {
                $query->whereNotIn('author_user_id', function ($authors) use ($viewer): void {
                    $authors->select('blocked_user_id')->from('user_blocks')->where('blocker_user_id', $viewer->id);
                })->whereNotIn('author_user_id', function ($authors) use ($viewer): void {
                    $authors->select('blocker_user_id')->from('user_blocks')->where('blocked_user_id', $viewer->id);
                })->whereNotIn('author_user_id', function ($authors) use ($viewer): void {
                    $authors->select('muted_user_id')->from('user_mutes')->where('muting_user_id', $viewer->id)->whereIn('scope', ['all', 'community_content'])->where(fn ($active) => $active->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                });
            })
            ->where(function ($query) use ($viewer): void {
                $query->whereNull('bunker_id')->orWhereHas('bunker', function ($bunkers) use ($viewer): void {
                    $bunkers->where('status', BunkerStatus::Published)->whereNotIn('id', ContentRestriction::query()->select('target_id')->where('target_type', 'bunker')->where('status', 'active')->whereIn('type', ['hidden_from_public', 'takedown_restricted']))->where(function ($visibility) use ($viewer): void {
                        $visibility->where('visibility', BunkerVisibility::Public);
                        if ($viewer !== null) {
                            $visibility->orWhereHas('memberships', fn ($memberships) => $memberships->where('user_id', $viewer->id)->where('status', 'active')->whereNotNull('active_key'));
                        }
                    });
                });
            })
            ->orderByDesc('published_at')->orderByDesc('id')->cursorPaginate(min(max($size, 1), 50));

        $paginator->setCollection($paginator->getCollection()->map(function (CommunityPost $post) use ($viewer): CommunityPost {
            $post->setAttribute('viewer_spoiler_visibility', $this->spoilers->decide($post, $viewer)->value);

            return $post;
        })->reject(fn (CommunityPost $post): bool => $post->getAttribute('viewer_spoiler_visibility') === SpoilerVisibility::Hidden->value)->values());

        return $paginator;
    }
}

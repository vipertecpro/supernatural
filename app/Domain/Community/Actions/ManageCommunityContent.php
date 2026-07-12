<?php

namespace App\Domain\Community\Actions;

use App\Domain\Community\Exceptions\InvalidCommunityOperation;
use App\Domain\Identity\Services\InteractionSafetyEvaluator;
use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Domain\Spoilers\Actions\UpsertSpoilerBoundary;
use App\Enums\CommunityCommentStatus;
use App\Enums\CommunityMentionType;
use App\Enums\CommunityPollStatus;
use App\Enums\CommunityPollType;
use App\Enums\CommunityPostStatus;
use App\Enums\CommunityPostVisibility;
use App\Enums\PublicationStatus;
use App\Enums\RestrictionScope;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Events\CommunityCommentCreated;
use App\Events\CommunityMentionCreated;
use App\Events\CommunityPollClosed;
use App\Events\CommunityPostPublished;
use App\Events\CommunityPostUpdated;
use App\Models\Bunker;
use App\Models\CommunityBookmark;
use App\Models\CommunityComment;
use App\Models\CommunityMention;
use App\Models\CommunityPoll;
use App\Models\CommunityPollOption;
use App\Models\CommunityPollVote;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Models\CommunityTag;
use App\Models\CommunityTaggable;
use App\Models\Episode;
use App\Models\LoreEntity;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageCommunityContent
{
    public function __construct(private readonly RestrictionEvaluator $restrictions, private readonly ManageBunkers $bunkers, private readonly UpsertSpoilerBoundary $spoilers, private readonly InteractionSafetyEvaluator $interactionSafety) {}

    /** @param array<string, mixed> $data */
    public function createPost(User $actor, array $data): CommunityPost
    {
        $this->denyScope($actor, RestrictionScope::CommunityContentCreation);
        if (! Universe::query()->where(['status' => PublicationStatus::Published, 'is_public' => true])->whereKey((int) $data['universe_id'])->exists()) {
            throw new InvalidCommunityOperation('The selected universe is unavailable.', 'community_universe_unavailable');
        }

        return DB::transaction(function () use ($actor, $data): CommunityPost {
            $bunker = isset($data['bunker_id']) ? Bunker::query()->findOrFail((int) $data['bunker_id']) : null;
            if ($bunker !== null && (! $this->bunkers->isMember($bunker, $actor) || $this->bunkers->isBanned($bunker, $actor))) {
                throw new InvalidCommunityOperation('Active Bunker membership is required.', 'bunker_membership_required');
            }
            if ($bunker !== null && $bunker->universe_id !== (int) $data['universe_id']) {
                throw new InvalidCommunityOperation('The post and Bunker must share a universe.', 'cross_universe_reference');
            }
            $reference = $this->reference($data['reference_type'] ?? null, $data['reference_id'] ?? null, (int) $data['universe_id']);
            $body = $this->plain($data['body']);
            $post = CommunityPost::query()->create(['author_user_id' => $actor->id, 'bunker_id' => $bunker?->id, 'universe_id' => $data['universe_id'], 'reference_type' => $reference?->getMorphClass(), 'reference_id' => $reference?->getKey(), 'title' => $this->plain($data['title'] ?? null), 'body' => $body, 'body_checksum' => hash('sha256', $body), 'status' => CommunityPostStatus::Published, 'visibility' => $bunker === null ? CommunityPostVisibility::Public : CommunityPostVisibility::Members, 'comments_enabled' => $data['comments_enabled'] ?? true, 'published_at' => now()]);
            $this->syncTags($post, $actor, $data['tags'] ?? []);
            $this->syncMentions($post, $actor, $body, CommunityMentionType::Post);
            $this->applySpoiler($post, $actor, $data);
            CommunityPostPublished::dispatch($post->id, $actor->id);

            return $post->fresh(['bunker', 'author', 'spoilerConstraints.boundaries']);
        });
    }

    /** @param array<string, mixed> $data */
    public function updatePost(CommunityPost $post, User $actor, array $data): CommunityPost
    {
        return DB::transaction(function () use ($post, $actor, $data): CommunityPost {
            $locked = CommunityPost::query()->lockForUpdate()->findOrFail($post->id);
            $this->assertVersion($locked->lock_version, (int) $data['lock_version']);
            $body = array_key_exists('body', $data) ? $this->plain($data['body']) : $locked->body;
            $locked->fill(['title' => array_key_exists('title', $data) ? $this->plain($data['title']) : $locked->title, 'body' => $body, 'body_checksum' => hash('sha256', $body), 'comments_enabled' => $data['comments_enabled'] ?? $locked->comments_enabled, 'edited_at' => now(), 'lock_version' => $locked->lock_version + 1]);
            $locked->save();
            $this->syncMentions($locked, $actor, $body, CommunityMentionType::Post);
            $this->applySpoiler($locked, $actor, $data);
            CommunityPostUpdated::dispatch($locked->id, $actor->id);

            return $locked->fresh(['spoilerConstraints.boundaries']);
        });
    }

    /** @param array<string, mixed> $data */
    public function createComment(CommunityPost $post, User $actor, array $data): CommunityComment
    {
        $this->denyScope($actor, RestrictionScope::CommunityCommenting);
        if ($post->locked_at !== null || ! $post->comments_enabled || $post->status !== CommunityPostStatus::Published) {
            throw new InvalidCommunityOperation('This post is not accepting comments.', 'comments_closed');
        }
        if ($post->bunker !== null && (! $this->bunkers->isMember($post->bunker, $actor) || $this->bunkers->isBanned($post->bunker, $actor))) {
            throw new InvalidCommunityOperation('Active Bunker membership is required.');
        }

        return DB::transaction(function () use ($post, $actor, $data): CommunityComment {
            $parent = isset($data['parent_id']) ? CommunityComment::query()->findOrFail((int) $data['parent_id']) : null;
            if ($parent !== null && $parent->post_id !== $post->id) {
                throw new InvalidCommunityOperation('The parent comment belongs to another post.');
            }
            $targetAuthorId = $parent instanceof CommunityComment ? $parent->author_user_id : $post->author_user_id;
            if ($targetAuthorId !== null && ! $this->interactionSafety->mayInitiateDirectInteraction($actor, $targetAuthorId)) {
                throw new InvalidCommunityOperation('This interaction is unavailable.', 'interaction_unavailable');
            }
            $depth = $parent === null ? 0 : $parent->depth + 1;
            if ($depth > (int) config('community.comment_max_depth', 5)) {
                throw new InvalidCommunityOperation('Maximum comment depth exceeded.', 'comment_depth_exceeded');
            }
            $body = $this->plain($data['body']);
            $rootId = $parent === null ? null : ($parent->root_id ?? $parent->id);
            $comment = CommunityComment::query()->create(['post_id' => $post->id, 'author_user_id' => $actor->id, 'parent_id' => $parent?->id, 'root_id' => $rootId, 'depth' => $depth, 'body' => $body, 'body_checksum' => hash('sha256', $body), 'status' => CommunityCommentStatus::Published]);
            $this->syncMentions($comment, $actor, $body, CommunityMentionType::Comment);
            $this->applySpoiler($comment, $actor, $data, $post->universe_id);
            CommunityCommentCreated::dispatch($comment->id, $post->id, $actor->id);

            return $comment->fresh(['author', 'spoilerConstraints.boundaries']);
        });
    }

    /** @param array<string, mixed> $data */
    public function updateComment(CommunityComment $comment, User $actor, array $data): CommunityComment
    {
        return DB::transaction(function () use ($comment, $actor, $data): CommunityComment {
            $locked = CommunityComment::query()->lockForUpdate()->findOrFail($comment->id);
            $this->assertVersion($locked->lock_version, (int) $data['lock_version']);
            $body = $this->plain($data['body']);
            $locked->update(['body' => $body, 'body_checksum' => hash('sha256', $body), 'edited_at' => now(), 'lock_version' => $locked->lock_version + 1]);
            $this->syncMentions($locked, $actor, $body, CommunityMentionType::Comment);

            return $locked;
        });
    }

    public function react(Model $target, User $actor, string $type): CommunityReaction
    {
        $this->denyScope($actor, RestrictionScope::CommunityReacting);
        $this->assertVisible($target, $actor);
        $authorId = $target->getAttribute('author_user_id');
        if (is_int($authorId) && ! $this->interactionSafety->mayInitiateDirectInteraction($actor, $authorId)) {
            throw new InvalidCommunityOperation('This interaction is unavailable.', 'interaction_unavailable');
        }

        return CommunityReaction::query()->firstOrCreate(['user_id' => $actor->id, 'reactable_type' => $target->getMorphClass(), 'reactable_id' => $target->getKey(), 'type' => $type]);
    }

    public function unreact(Model $target, User $actor, string $type): void
    {
        CommunityReaction::query()->where(['user_id' => $actor->id, 'reactable_type' => $target->getMorphClass(), 'reactable_id' => $target->getKey(), 'type' => $type])->delete();
    }

    public function bookmark(CommunityPost $post, User $actor): CommunityBookmark
    {
        $this->assertVisible($post, $actor);

        return CommunityBookmark::query()->firstOrCreate(['user_id' => $actor->id, 'bookmarkable_type' => $post->getMorphClass(), 'bookmarkable_id' => $post->id]);
    }

    /** @param array<string, mixed> $data */
    public function createPoll(CommunityPost $post, User $actor, array $data): CommunityPoll
    {
        return DB::transaction(function () use ($post, $actor, $data): CommunityPoll {
            if ($post->author_user_id !== $actor->id || CommunityPoll::query()->where('post_id', $post->id)->exists()) {
                throw new InvalidCommunityOperation('A poll cannot be added to this post.');
            }
            $type = CommunityPollType::from((string) $data['type']);
            $poll = CommunityPoll::query()->create(['post_id' => $post->id, 'question' => $this->plain($data['question']), 'type' => $type, 'maximum_choices' => $type === CommunityPollType::Single ? 1 : $data['maximum_choices'], 'status' => CommunityPollStatus::Open, 'results_visibility' => $data['results_visibility'], 'opens_at' => now(), 'closes_at' => $data['closes_at'] ?? null]);
            foreach (array_values($data['options']) as $position => $text) {
                CommunityPollOption::query()->create(['poll_id' => $poll->id, 'text' => $this->plain($text), 'position' => $position]);
            }

            return $poll->fresh('options');
        });
    }

    /** @param list<int> $optionIds */
    public function vote(CommunityPoll $poll, User $actor, array $optionIds): CommunityPoll
    {
        $this->denyScope($actor, RestrictionScope::CommunityPollVoting);
        $this->assertVisible($poll->post, $actor);

        return DB::transaction(function () use ($poll, $actor, $optionIds): CommunityPoll {
            $locked = CommunityPoll::query()->lockForUpdate()->findOrFail($poll->id);
            if ($locked->status !== CommunityPollStatus::Open || ($locked->closes_at !== null && $locked->closes_at->isPast())) {
                throw new InvalidCommunityOperation('This poll is closed.', 'poll_closed');
            }
            $optionIds = array_values(array_unique($optionIds));
            if (count($optionIds) < 1 || count($optionIds) > $locked->maximum_choices || CommunityPollOption::query()->where('poll_id', $locked->id)->whereIn('id', $optionIds)->count() !== count($optionIds)) {
                throw new InvalidCommunityOperation('The selected poll options are invalid.', 'invalid_poll_vote');
            }
            CommunityPollVote::query()->where(['poll_id' => $locked->id, 'user_id' => $actor->id])->delete();
            foreach ($optionIds as $optionId) {
                CommunityPollVote::query()->create(['poll_id' => $locked->id, 'poll_option_id' => $optionId, 'user_id' => $actor->id]);
            }

            return $locked->fresh('options');
        });
    }

    public function closePoll(CommunityPoll $poll, User $actor, int $version): CommunityPoll
    {
        $locked = DB::transaction(function () use ($poll, $version): CommunityPoll {
            $locked = CommunityPoll::query()->lockForUpdate()->findOrFail($poll->id);
            $this->assertVersion($locked->lock_version, $version);
            $locked->update(['status' => CommunityPollStatus::Closed, 'closed_at' => now(), 'lock_version' => $locked->lock_version + 1]);

            return $locked;
        });
        CommunityPollClosed::dispatch($locked->id, $locked->post_id, $actor->id);

        return $locked;
    }

    /** @param array<string, mixed> $data */
    private function applySpoiler(Model $target, User $actor, array $data, ?int $universeId = null): void
    {
        if (! isset($data['spoiler_work_id'])) {
            return;
        }
        $work = Work::query()->findOrFail((int) $data['spoiler_work_id']);
        $season = isset($data['spoiler_season_id']) ? Season::query()->findOrFail((int) $data['spoiler_season_id']) : null;
        $episode = isset($data['spoiler_episode_id']) ? Episode::query()->findOrFail((int) $data['spoiler_episode_id']) : null;
        if ($universeId !== null && $work->universe_id !== $universeId) {
            throw new InvalidCommunityOperation('The spoiler boundary belongs to another universe.', 'cross_universe_spoiler');
        }
        $this->spoilers->handle($target, $work, $season, $episode, SpoilerSeverity::from((string) ($data['spoiler_severity'] ?? SpoilerSeverity::Major->value)), SpoilerClassificationStatus::Draft, $actor, $this->plain($data['spoiler_warning'] ?? null));
    }

    /** @param list<string> $names */
    private function syncTags(CommunityPost $post, User $actor, array $names): void
    {
        if (count($names) > (int) config('community.tag_max_count', 8)) {
            throw new InvalidCommunityOperation('Too many tags.');
        }
        CommunityTaggable::query()->where(['taggable_type' => $post->getMorphClass(), 'taggable_id' => $post->id])->delete();
        foreach ($names as $name) {
            $normalized = Str::of($name)->lower()->squish()->toString();
            if ($normalized === '') {
                continue;
            } $tag = CommunityTag::query()->firstOrCreate(['universe_id' => $post->universe_id, 'normalized_name' => $normalized], ['display_name' => trim(strip_tags($name)), 'slug' => Str::slug($normalized), 'status' => 'active', 'created_by' => $actor->id]);
            CommunityTaggable::query()->firstOrCreate(['tag_id' => $tag->id, 'taggable_type' => $post->getMorphClass(), 'taggable_id' => $post->id]);
        }
    }

    private function syncMentions(Model $source, User $actor, string $body, CommunityMentionType $type): void
    {
        $this->denyScope($actor, RestrictionScope::CommunityMentioning);
        preg_match_all('/@([0-9]+)/', $body, $matches);
        $ids = array_values(array_unique(array_map('intval', $matches[1])));
        if (count($ids) > (int) config('community.mention_max_count', 10)) {
            throw new InvalidCommunityOperation('Too many mentions.');
        }
        CommunityMention::query()->where(['mentionable_type' => $source->getMorphClass(), 'mentionable_id' => $source->getKey()])->whereNotIn('mentioned_user_id', $ids ?: [0])->update(['inactive_at' => now(), 'notification_key' => null]);
        foreach ($ids as $id) {
            $target = User::query()->find($id);
            if ($target === null || ! $this->mentionAccessible($source, $target) || ! $this->interactionSafety->mayMention($actor, $target)) {
                throw new InvalidCommunityOperation('A mentioned user cannot access this content.', 'inaccessible_mention');
            } $mention = CommunityMention::query()->updateOrCreate(['mentionable_type' => $source->getMorphClass(), 'mentionable_id' => $source->getKey(), 'mentioned_user_id' => $id], ['mentioning_user_id' => $actor->id, 'type' => $type, 'notification_key' => $source->getMorphClass().':'.$source->getKey().':'.$id, 'inactive_at' => null]);
            CommunityMentionCreated::dispatch($mention->id, $id, $actor->id);
        }
    }

    private function mentionAccessible(Model $source, User $user): bool
    {
        $post = $source instanceof CommunityComment ? $source->post : $source;

        return $post instanceof CommunityPost && ($post->bunker === null || $this->bunkers->isMember($post->bunker, $user));
    }

    private function assertVisible(Model $target, User $actor): void
    {
        $post = $target instanceof CommunityComment ? $target->post : ($target instanceof CommunityPoll ? $target->post : $target);
        if (! $post instanceof CommunityPost || $post->status !== CommunityPostStatus::Published || ($post->bunker !== null && ! $this->bunkers->isMember($post->bunker, $actor))) {
            throw new InvalidCommunityOperation('The target is unavailable.', 'community_target_unavailable');
        }
    }

    private function reference(mixed $type, mixed $id, int $universeId): ?Model
    {
        if ($type === null || $id === null) {
            return null;
        } $model = match ($type) {
            'work' => Work::query()->visibleToPublic()->findOrFail((int) $id), 'lore_entity' => LoreEntity::query()->visibleToPublic()->findOrFail((int) $id), default => throw new InvalidCommunityOperation('Unsupported Community reference.')
        };
        if ((int) $model->universe_id !== $universeId) {
            throw new InvalidCommunityOperation('The reference belongs to another universe.', 'cross_universe_reference');
        }

        return $model;
    }

    private function denyScope(User $user, RestrictionScope $scope): void
    {
        if ($this->restrictions->hasUserScope($user, $scope)) {
            throw new InvalidCommunityOperation('This Community capability is restricted.', 'community_capability_restricted');
        }
    }

    private function assertVersion(int $actual, int $expected): void
    {
        if ($actual !== $expected) {
            throw new InvalidCommunityOperation('The record changed since it was loaded.', 'optimistic_lock_conflict');
        }
    }

    private function plain(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }
}

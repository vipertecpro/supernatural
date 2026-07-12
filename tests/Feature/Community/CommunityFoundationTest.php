<?php

use App\Domain\Community\Actions\ManageBunkers;
use App\Domain\Community\Actions\ManageCommunityContent;
use App\Domain\Community\Exceptions\InvalidCommunityOperation;
use App\Domain\Community\Queries\CommunityFeed;
use App\Domain\Moderation\Services\ReportTargetRegistry;
use App\Domain\Notifications\Services\NotificationTypeRegistry;
use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Enums\CommunityPollResultsVisibility;
use App\Enums\CommunityPollType;
use App\Enums\ContentRestrictionType;
use App\Enums\RestrictionScope;
use App\Enums\RestrictionStatus;
use App\Events\BunkerInvitationCreated;
use App\Events\CommunityPostPublished;
use App\Listeners\CreateDomainNotification;
use App\Models\Bunker;
use App\Models\BunkerBan;
use App\Models\BunkerMembership;
use App\Models\CommunityBookmark;
use App\Models\CommunityPollVote;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Models\ContentRestriction;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserRestriction;
use Database\Seeders\BunkerCategorySeeder;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Relations\Relation;

beforeEach(function () {
    $this->seed(BunkerCategorySeeder::class);
});

function createCommunityBunker(User $owner, array $overrides = []): Bunker
{
    $universe = Universe::factory()->published()->create();

    return app(ManageBunkers::class)->create($owner, $universe->id, $overrides + ['name' => 'Northstar Readers', 'visibility' => BunkerVisibility::Public->value, 'requires_approval' => false]);
}

test('community morph aliases are stable and events do not broadcast', function () {
    expect(Relation::getMorphedModel('bunker'))->toBe(Bunker::class)
        ->and(Relation::getMorphedModel('community_post'))->toBe(CommunityPost::class)
        ->and(class_implements(CommunityPostPublished::class))->toHaveKey(ShouldDispatchAfterCommit::class)
        ->not->toHaveKey(ShouldBroadcast::class);
});

test('creating a bunker transactionally creates exactly one local owner', function () {
    $owner = User::factory()->create();
    $bunker = createCommunityBunker($owner);

    expect($bunker->status)->toBe(BunkerStatus::Draft)
        ->and($bunker->owner_user_id)->toBe($owner->id)
        ->and($bunker->memberships()->count())->toBe(1)
        ->and($bunker->memberships()->first()->role)->toBe(BunkerMembershipRole::Owner)
        ->and($bunker->owner_membership_key)->not->toBeNull();
});

test('join approval creates membership and duplicate requests are blocked by integrity keys', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $bunker = createCommunityBunker($owner);
    $action = app(ManageBunkers::class);
    $request = $action->requestJoin($bunker, $member, 'I enjoy thoughtful discussion.');
    $action->decideJoin($request, $owner, true, 'Welcome.');

    expect(BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $member->id])->firstOrFail()->role)->toBe(BunkerMembershipRole::Member);
    $action->requestJoin($bunker, $member, null);
})->throws(InvalidCommunityOperation::class, 'already a member');

test('invitation tokens are hashed single use and never persisted raw', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $bunker = createCommunityBunker($owner, ['visibility' => BunkerVisibility::InviteOnly->value]);
    $result = app(ManageBunkers::class)->invite($bunker, $owner, $member, BunkerMembershipRole::Member);

    expect($result['invitation']->token_hash)->not->toBe($result['token'])
        ->and(strlen($result['invitation']->token_hash))->toBe(64);
    app(ManageBunkers::class)->acceptInvitation($result['invitation'], $member, $result['token']);
    app(ManageBunkers::class)->acceptInvitation($result['invitation'], $member, $result['token']);
})->throws(InvalidCommunityOperation::class, 'invalid or expired');

test('local bans remove membership without creating a platform restriction', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $bunker = createCommunityBunker($owner);
    BunkerMembership::factory()->create(['bunker_id' => $bunker->id, 'user_id' => $member->id, 'active_key' => $bunker->id.':'.$member->id]);
    app(ManageBunkers::class)->ban($bunker, $owner, $member, 'conduct', 'Access is temporarily restricted.', null, now()->addDay()->toISOString());

    expect(BunkerBan::query()->count())->toBe(1)
        ->and(BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $member->id])->first()->active_key)->toBeNull()
        ->and(UserRestriction::query()->count())->toBe(0);
});

test('posts strip html and enforce membership while comments preserve bounded threads', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $bunker = createCommunityBunker($owner);
    $content = app(ManageCommunityContent::class);
    $post = $content->createPost($owner, ['universe_id' => $bunker->universe_id, 'bunker_id' => $bunker->id, 'title' => '<b>Topic</b>', 'body' => '<script>alert(1)</script>Safe text']);

    expect($post->title)->toBe('Topic')->and($post->body)->not->toContain('<script>');
    $content->createPost($outsider, ['universe_id' => $bunker->universe_id, 'bunker_id' => $bunker->id, 'body' => 'Not allowed']);
})->throws(InvalidCommunityOperation::class, 'membership');

test('reactions are constrained bookmarks remain private and polls enforce choice limits', function () {
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $content = app(ManageCommunityContent::class);
    $post = $content->createPost($author, ['universe_id' => $universe->id, 'body' => 'A safe public discussion.']);
    $content->react($post, $voter, 'like');
    $content->react($post, $voter, 'like');
    $content->bookmark($post, $voter);
    $poll = $content->createPoll($post, $author, ['question' => 'Choose one?', 'type' => CommunityPollType::Single->value, 'maximum_choices' => 1, 'results_visibility' => CommunityPollResultsVisibility::AfterVote->value, 'options' => ['First', 'Second']]);
    $content->vote($poll, $voter, [$poll->options->first()->id]);

    expect(CommunityReaction::query()->count())->toBe(1)
        ->and(CommunityBookmark::query()->where('user_id', $voter->id)->count())->toBe(1)
        ->and(CommunityPollVote::query()->where('user_id', $voter->id)->count())->toBe(1);
});

test('comment parents must belong to the same post', function () {
    $user = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $content = app(ManageCommunityContent::class);
    $first = $content->createPost($user, ['universe_id' => $universe->id, 'body' => 'First post']);
    $second = $content->createPost($user, ['universe_id' => $universe->id, 'body' => 'Second post']);
    $comment = $content->createComment($first, $user, ['body' => 'Root reply']);
    $content->createComment($second, $user, ['body' => 'Invalid reply', 'parent_id' => $comment->id]);
})->throws(InvalidCommunityOperation::class, 'another post');

test('optimistic locking rejects stale bunker updates', function () {
    $owner = User::factory()->create();
    $bunker = createCommunityBunker($owner);
    app(ManageBunkers::class)->update($bunker, $owner, ['lock_version' => 0, 'name' => 'Updated Group']);
    app(ManageBunkers::class)->update($bunker, $owner, ['lock_version' => 0, 'name' => 'Stale Group']);
})->throws(InvalidCommunityOperation::class, 'changed since');

test('community capability restrictions block content creation', function () {
    $user = User::factory()->create();
    $restriction = UserRestriction::factory()->create(['user_id' => $user->id, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => now()->addDay()]);
    $restriction->scopes()->create(['scope' => RestrictionScope::CommunityContentCreation]);
    $universe = Universe::factory()->published()->create();
    app(ManageCommunityContent::class)->createPost($user, ['universe_id' => $universe->id, 'body' => 'Blocked content']);
})->throws(InvalidCommunityOperation::class, 'restricted');

test('content restrictions remove posts before feed pagination', function () {
    $user = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $post = app(ManageCommunityContent::class)->createPost($user, ['universe_id' => $universe->id, 'body' => 'Restricted content']);
    ContentRestriction::factory()->create(['target_type' => 'community_post', 'target_id' => $post->id, 'type' => ContentRestrictionType::HiddenFromPublic, 'status' => RestrictionStatus::Active, 'effective_at' => now(), 'expires_at' => now()->addDay()]);
    expect(app(CommunityFeed::class)->handle(null)->count())->toBe(0);
});

test('report targets enforce private bunker membership', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $bunker = createCommunityBunker($owner, ['visibility' => BunkerVisibility::Private->value]);
    $registry = app(ReportTargetRegistry::class);
    expect($registry->isAccessibleToReporter($bunker, $owner))->toBeTrue()->and($registry->isAccessibleToReporter($bunker, $outsider))->toBeFalse();
});

test('community notification definitions are scalar and idempotent', function () {
    $owner = User::factory()->create();
    $invited = User::factory()->create();
    $bunker = createCommunityBunker($owner);
    $result = app(ManageBunkers::class)->invite($bunker, $owner, $invited, BunkerMembershipRole::Member);
    app(NotificationTypeRegistry::class)->validatePayload('community.bunker.invited', ['invitation_id' => $result['invitation']->id, 'bunker_id' => $bunker->id]);
    $event = new BunkerInvitationCreated($result['invitation']->id, $bunker->id, $invited->id);
    app(CreateDomainNotification::class)->handle($event);
    app(CreateDomainNotification::class)->handle($event);
    expect(UserNotification::query()->where('user_id', $invited->id)->count())->toBe(1)->and(UserNotification::query()->first()->payload)->not->toHaveKey('token');
});

test('account deletion archives owned bunkers and deletes private interactions', function () {
    $user = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $bunker = createCommunityBunker($user);
    $post = app(ManageCommunityContent::class)->createPost($user, ['universe_id' => $universe->id, 'body' => 'Retained public content']);
    app(ManageCommunityContent::class)->bookmark($post, $user);
    app(ManageCommunityContent::class)->react($post, $user, 'like');
    $user->delete();
    expect($bunker->fresh()->status)->toBe(BunkerStatus::Archived)->and($post->fresh()->author_user_id)->toBeNull()->and(CommunityBookmark::query()->count())->toBe(0)->and(CommunityReaction::query()->count())->toBe(0);
});

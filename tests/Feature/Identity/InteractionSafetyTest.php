<?php

use App\Domain\Community\Actions\ManageBunkers;
use App\Domain\Community\Actions\ManageCommunityContent;
use App\Domain\Community\Exceptions\InvalidCommunityOperation;
use App\Domain\Community\Queries\CommunityFeed;
use App\Domain\Identity\Actions\ManageInteractionSafety;
use App\Domain\Identity\Services\InteractionSafetyEvaluator;
use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerVisibility;
use App\Enums\UserMuteScope;
use App\Events\BunkerInvitationCreated;
use App\Events\UserBlocked;
use App\Listeners\CreateDomainNotification;
use App\Listeners\DeactivateBlockedCommunityMentions;
use App\Models\CommunityMention;
use App\Models\CommunityPost;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;
use App\Models\UserNotification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Facades\Event;

test('blocks are idempotent reciprocal private and dispatched after commit', function () {
    Event::fake([UserBlocked::class]);
    $first = User::factory()->create();
    $second = User::factory()->create();
    $action = app(ManageInteractionSafety::class);

    $action->block($first, $second, 'personal_safety');
    $action->block($first, $second, 'ignored_duplicate');
    $action->block($second, $first);

    expect(UserBlock::query()->count())->toBe(2)
        ->and(app(InteractionSafetyEvaluator::class)->hasEitherBlocked($first, $second))->toBeTrue()
        ->and(class_implements(UserBlocked::class))->toHaveKey(ShouldDispatchAfterCommit::class)->not->toHaveKey(ShouldBroadcast::class);
    Event::assertDispatchedTimes(UserBlocked::class, 2);
});

test('self block and self mute are rejected', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson('/api/v1/me/blocks', ['target_user_id' => $user->id])->assertUnprocessable();
    $this->actingAs($user)->postJson('/api/v1/me/mutes', ['target_user_id' => $user->id, 'scope' => 'all'])->assertUnprocessable();
});

test('interaction safety writes require verified authentication and are rate limited', function () {
    $target = User::factory()->create();
    $this->postJson('/api/v1/me/blocks', ['target_user_id' => $target->id])->assertUnauthorized();
    $this->actingAs(User::factory()->unverified()->create())->postJson('/api/v1/me/blocks', ['target_user_id' => $target->id])->assertForbidden()->assertJsonPath('error.code', 'email_unverified');

    $actor = User::factory()->create();
    $this->actingAs($actor);
    foreach (range(1, 20) as $attempt) {
        $this->postJson('/api/v1/me/mutes', ['target_user_id' => User::factory()->create()->id, 'scope' => 'mentions'])->assertSuccessful();
    }
    $this->postJson('/api/v1/me/mutes', ['target_user_id' => User::factory()->create()->id, 'scope' => 'mentions'])->assertTooManyRequests()->assertJsonPath('error.code', 'rate_limited');
});

test('mute scopes expire and all scope dominates narrower decisions', function () {
    $viewer = User::factory()->create();
    $author = User::factory()->create();
    UserMute::factory()->expired()->create(['muting_user_id' => $viewer->id, 'muted_user_id' => $author->id, 'scope' => UserMuteScope::CommunityContent]);
    $evaluator = app(InteractionSafetyEvaluator::class);
    expect($evaluator->hasMuted($viewer, $author, UserMuteScope::CommunityContent))->toBeFalse();

    app(ManageInteractionSafety::class)->mute($viewer, $author, UserMuteScope::All, null);
    expect($evaluator->hasMuted($viewer, $author, UserMuteScope::CommunityContent))->toBeTrue()
        ->and($evaluator->mayInitiateDirectInteraction($viewer, $author))->toBeTrue();

    app(ManageInteractionSafety::class)->mute($viewer, $author, UserMuteScope::CommunityContent, null);
    expect($evaluator->hasMuted($viewer, $author, UserMuteScope::CommunityContent))->toBeTrue();
});

test('block and mute APIs are owner scoped and hide target facing state', function () {
    $owner = User::factory()->create();
    $target = User::factory()->create();
    $other = User::factory()->create();
    $block = app(ManageInteractionSafety::class)->block($owner, $target, 'privacy');
    $mute = app(ManageInteractionSafety::class)->mute($owner, $target, UserMuteScope::Mentions, null);

    $this->actingAs($owner)->getJson('/api/v1/me/blocks')->assertSuccessful()->assertJsonPath('data.0.reason_code', 'privacy')->assertHeader('X-Request-ID');
    $this->actingAs($target)->getJson('/api/v1/me/blocks')->assertSuccessful()->assertJsonCount(0, 'data');
    $this->actingAs($other)->deleteJson('/api/v1/me/blocks/'.$block->id)->assertNotFound();
    $this->actingAs($other)->deleteJson('/api/v1/me/mutes/'.$mute->id)->assertNotFound();
});

test('blocks prevent mentions replies reactions and bunker invitations generically', function () {
    $author = User::factory()->create();
    $actor = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $post = app(ManageCommunityContent::class)->createPost($author, ['universe_id' => $universe->id, 'body' => 'Public discussion']);
    app(ManageInteractionSafety::class)->block($author, $actor);

    expect(fn () => app(ManageCommunityContent::class)->createPost($actor, ['universe_id' => $universe->id, 'body' => 'Hello @'.$author->id]))->toThrow(InvalidCommunityOperation::class, 'cannot access');

    expect(fn () => app(ManageCommunityContent::class)->createComment($post, $actor, ['body' => 'Reply']))->toThrow(InvalidCommunityOperation::class, 'interaction is unavailable')
        ->and(fn () => app(ManageCommunityContent::class)->react($post, $actor, 'like'))->toThrow(InvalidCommunityOperation::class, 'interaction is unavailable');

    $bunker = app(ManageBunkers::class)->create($author, $universe->id, ['name' => 'Safety Circle', 'visibility' => BunkerVisibility::Public->value]);
    expect(fn () => app(ManageBunkers::class)->invite($bunker, $author, $actor, BunkerMembershipRole::Member))->toThrow(InvalidCommunityOperation::class, 'cannot be invited');
});

test('a new block deactivates existing mentions in either direction', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $mention = CommunityMention::factory()->create(['mentioning_user_id' => $first->id, 'mentioned_user_id' => $second->id]);
    $block = app(ManageInteractionSafety::class)->block($second, $first);
    app(DeactivateBlockedCommunityMentions::class)->handle(new UserBlocked($block->id, $second->id, $first->id));

    expect($mention->fresh()->inactive_at)->not->toBeNull()->and($mention->fresh()->notification_key)->toBeNull();
});

test('optional invitation notifications are suppressed while mandatory notifications remain outside suppression', function () {
    $owner = User::factory()->create();
    $invited = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $bunker = app(ManageBunkers::class)->create($owner, $universe->id, ['name' => 'Quiet Circle', 'visibility' => BunkerVisibility::Public->value]);
    app(ManageInteractionSafety::class)->mute($invited, $owner, UserMuteScope::BunkerInvitations, null);
    $result = app(ManageBunkers::class)->invite($bunker, $owner, $invited, BunkerMembershipRole::Member);
    app(CreateDomainNotification::class)->handle(new BunkerInvitationCreated($result['invitation']->id, $bunker->id, $invited->id));

    expect(UserNotification::query()->where('user_id', $invited->id)->count())->toBe(0)
        ->and(app(InteractionSafetyEvaluator::class)->shouldSuppressOptionalNotification($invited, $owner, 'community.bunker.invited'))->toBeTrue();
});

test('authenticated feeds suppress blocked and muted authors while guest feed stays public', function () {
    $viewer = User::factory()->create();
    $blocked = User::factory()->create();
    $muted = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    CommunityPost::factory()->create(['universe_id' => $universe->id, 'author_user_id' => $blocked->id]);
    CommunityPost::factory()->create(['universe_id' => $universe->id, 'author_user_id' => $muted->id]);
    app(ManageInteractionSafety::class)->block($blocked, $viewer);
    app(ManageInteractionSafety::class)->mute($viewer, $muted, UserMuteScope::CommunityContent, null);

    expect(app(CommunityFeed::class)->handle($viewer)->count())->toBe(0)
        ->and(app(CommunityFeed::class)->handle(null)->count())->toBe(2);
});

test('account deletion removes incoming and outgoing preferences', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    UserBlock::factory()->create(['blocker_user_id' => $first->id, 'blocked_user_id' => $second->id]);
    UserMute::factory()->create(['muting_user_id' => $second->id, 'muted_user_id' => $first->id]);
    $first->delete();

    expect(UserBlock::query()->count())->toBe(0)->and(UserMute::query()->count())->toBe(0);
});

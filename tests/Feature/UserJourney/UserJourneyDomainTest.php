<?php

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\UserJourney\Actions\ManageViewingJourneys;
use App\Domain\UserJourney\Actions\ManageViewingOrders;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Enums\JourneyStatus;
use App\Enums\PersonalVisibility;
use App\Enums\PublicationStatus;
use App\Enums\RoleName;
use App\Events\ViewingJourneyCompleted;
use App\Events\ViewingJourneyStarted;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('viewing orders enforce universe targets deterministic positions and transactional reorder', function () {
    $actor = editorialUser(RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create();
    $otherWork = Work::factory()->published()->create();
    $order = ViewingOrder::factory()->for($universe)->create();
    $action = app(ManageViewingOrders::class);

    $first = $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'work', 'target_id' => $work->id, 'position' => 1], $actor);
    $secondWork = Work::factory()->for($universe)->published()->create();
    $second = $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'work', 'target_id' => $secondWork->id, 'position' => 2], $actor);
    $reordered = $action->reorder($order, [$second->id, $first->id], 0, $actor);

    expect($reordered->items->pluck('id')->all())->toBe([$second->id, $first->id]);
    expect(fn () => $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'work', 'target_id' => $otherWork->id, 'position' => 3], $actor))->toThrow(InvalidJourneyOperation::class);
});

test('viewing order defaults publication archival and optimistic locking are explicit', function () {
    $actor = editorialUser(RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create();
    $first = ViewingOrder::factory()->for($universe)->create();
    $second = ViewingOrder::factory()->for($universe)->create();
    $action = app(ManageViewingOrders::class);
    $action->addItem($first, ['viewing_order_id' => $first->id, 'target_type' => 'work', 'target_id' => $work->id, 'position' => 1], $actor);

    $first = $action->setDefault($first, 0, $actor);
    $second = $action->setDefault($second, 0, $actor);
    expect($first->fresh()->is_default)->toBeFalse()->and($second->is_default)->toBeTrue();

    $first = $action->publish($first, 1, $actor);
    expect($first->status)->toBe(PublicationStatus::Published)->and($first->visibility)->toBe(PersonalVisibility::Public);
    expect(fn () => $action->archive($first, 1, $actor))->toThrow(OptimisticLockConflict::class);
    $first = $action->archive($first, 2, $actor);
    expect($first->status)->toBe(PublicationStatus::Archived)->and($first->archived_at)->not->toBeNull();
});

test('journey lifecycle retains history is private and rejects a second active journey', function () {
    Event::fake([ViewingJourneyStarted::class, ViewingJourneyCompleted::class]);
    $user = User::factory()->create();
    $order = ViewingOrder::factory()->published()->create();
    $work = Work::factory()->for($order->universe)->published()->create();
    app(ManageViewingOrders::class)->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'work', 'target_id' => $work->id, 'position' => 1], editorialUser(RoleName::Administrator));
    $action = app(ManageViewingJourneys::class);

    $journey = $action->start($user, $order->fresh('items'));
    expect($journey->status)->toBe(JourneyStatus::Active)->and($journey->visibility)->toBe(PersonalVisibility::Private)->and($journey->current_item_id)->not->toBeNull();
    expect(fn () => $action->start($user, $order))->toThrow(InvalidJourneyOperation::class);

    $journey = $action->transition($journey, JourneyStatus::Paused, 0);
    $journey = $action->transition($journey, JourneyStatus::Active, 1);
    $journey = $action->transition($journey, JourneyStatus::Completed, 2);
    expect($journey->status)->toBe(JourneyStatus::Completed)->and(UserViewingJourney::query()->whereKey($journey)->exists())->toBeTrue();
    Event::assertDispatched(ViewingJourneyStarted::class);
    Event::assertDispatched(ViewingJourneyCompleted::class);
});

test('journey API is authenticated verified owner scoped and private by default', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $journey = UserViewingJourney::factory()->for($owner)->create();

    $this->getJson('/api/v1/me/journeys')->assertUnauthorized();
    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified)->getJson('/api/v1/me/journeys')->assertForbidden();
    $this->actingAs($other)->getJson("/api/v1/me/journeys/{$journey->id}")->assertNotFound();
    $this->actingAs($owner)->getJson("/api/v1/me/journeys/{$journey->id}")->assertSuccessful()->assertJsonPath('data.visibility', 'private');
});

test('published viewing orders are public while drafts and archived orders are excluded', function () {
    $public = ViewingOrder::factory()->published()->create();
    ViewingOrder::factory()->for($public->universe)->create();
    ViewingOrder::factory()->for($public->universe)->published()->create(['archived_at' => now()]);

    $this->getJson("/api/v1/universes/{$public->universe_id}/viewing-orders")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $public->id);
});

test('viewing order items support work season and episode targets from one universe', function () {
    $actor = editorialUser(RoleName::Administrator);
    $work = Work::factory()->series()->published()->create();
    $season = Season::factory()->for($work)->published()->create();
    $episode = Episode::factory()->forSeason($season)->published()->create();
    $order = ViewingOrder::factory()->create(['universe_id' => $work->universe_id]);
    $action = app(ManageViewingOrders::class);

    $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'work', 'target_id' => $work->id, 'position' => 1], $actor);
    $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'season', 'target_id' => $season->id, 'position' => 2], $actor);
    $action->addItem($order, ['viewing_order_id' => $order->id, 'target_type' => 'episode', 'target_id' => $episode->id, 'position' => 3], $actor);

    expect($order->items()->pluck('target_type')->all())->toBe(['work', 'season', 'episode']);
});

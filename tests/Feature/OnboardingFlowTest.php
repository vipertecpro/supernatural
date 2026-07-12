<?php

use App\Domain\Onboarding\OnboardingStateResolver;
use App\Enums\OnboardingStep;
use App\Enums\PersonalVisibility;
use App\Enums\RestrictionScope;
use App\Enums\SpoilerTolerance;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserOnboardingState;
use App\Models\UserRestriction;
use App\Models\ViewingOrder;
use App\Models\ViewingProgress;
use App\Models\Work;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

function onboardingUser(OnboardingStep $step = OnboardingStep::Introduction, bool $verified = true): User
{
    $user = User::factory()->when(! $verified, fn ($factory) => $factory->unverified())->create();
    UserOnboardingState::factory()->create([
        'user_id' => $user->id,
        'current_step' => $step,
        'started_at' => $step === OnboardingStep::Introduction ? null : now(),
        'last_activity_at' => $step === OnboardingStep::Introduction ? null : now(),
        'completed_at' => $step === OnboardingStep::Completed ? now() : null,
    ]);

    return $user;
}

test('registration creates one incomplete onboarding state transactionally', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'New Fan',
        'email' => 'new-fan@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'new-fan@example.com')->firstOrFail();

    $response->assertRedirect(route('dashboard', absolute: false));
    expect($user->onboardingState()->count())->toBe(1)
        ->and($user->onboardingState->current_step)->toBe(OnboardingStep::Introduction)
        ->and($user->onboardingState->completed_at)->toBeNull();
});

test('missing states are treated as completed so existing users are not blocked', function () {
    $user = User::factory()->create();
    $resolver = app(OnboardingStateResolver::class);

    $first = $resolver->forUser($user);
    $second = $resolver->forUser($user);

    expect($first->isCompleted())->toBeTrue()
        ->and($second->id)->toBe($first->id)
        ->and(UserOnboardingState::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('unverified users cannot enter onboarding and verification resumes incomplete setup', function () {
    $user = onboardingUser(verified: false);

    $this->actingAs($user)->get(route('onboarding.introduction'))
        ->assertRedirect(route('verification.notice'));

    Event::fake();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addHour(),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('onboarding.introduction'));

    Event::assertDispatched(Verified::class);
});

test('incomplete users resume the current step while profile and logout remain accessible', function () {
    $user = onboardingUser(OnboardingStep::SpoilerPreferences);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('onboarding.spoilers.edit'));
    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
});

test('empty catalog onboarding completes sequentially with conservative defaults', function () {
    $user = onboardingUser();
    $this->actingAs($user);

    $this->get(route('onboarding.introduction'))
        ->assertInertia(fn (Assert $page) => $page->component('onboarding/introduction')->where('onboarding.currentStep', 'introduction'));

    $state = $user->onboardingState;
    $this->patch(route('onboarding.introduction.update'), ['expected_version' => $state->lock_version])
        ->assertRedirect(route('onboarding.interests.edit'));

    $state->refresh();
    $this->get(route('onboarding.interests.edit'))
        ->assertInertia(fn (Assert $page) => $page->component('onboarding/universe-interests')->has('universes', 0));
    $this->patch(route('onboarding.interests.update'), [
        'universe_ids' => [],
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.progress.edit'));

    $state->refresh();
    $this->patch(route('onboarding.progress.update'), [
        'mode' => 'skip',
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.spoilers.edit'));

    $state->refresh();
    $this->patch(route('onboarding.spoilers.update'), [
        'tolerance' => 'strict',
        'show_warnings' => true,
        'rewatch_behavior' => 'historical',
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.viewing-order.edit'));

    $state->refresh();
    $this->patch(route('onboarding.viewing-order.update'), [
        'viewing_order_id' => null,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.privacy.edit'));

    $state->refresh();
    $this->patch(route('onboarding.privacy.update'), [
        'confirm_private_defaults' => true,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.review'));

    $state->refresh();
    $this->get(route('onboarding.review'))
        ->assertInertia(fn (Assert $page) => $page->component('onboarding/review')->has('summary'));
    $this->post(route('onboarding.complete'), ['expected_version' => $state->lock_version])
        ->assertRedirect(route('dashboard'));

    expect($state->refresh()->isCompleted())->toBeTrue();
    $this->get(route('onboarding.introduction'))->assertRedirect(route('dashboard'));
});

test('each available onboarding step renders its dedicated Inertia page', function (string $routeName, string $component) {
    $user = onboardingUser(OnboardingStep::Review);

    $this->actingAs($user)->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component)->has('onboarding.version'));
})->with([
    'introduction' => ['onboarding.introduction', 'onboarding/introduction'],
    'interests' => ['onboarding.interests.edit', 'onboarding/universe-interests'],
    'progress' => ['onboarding.progress.edit', 'onboarding/viewing-progress'],
    'spoilers' => ['onboarding.spoilers.edit', 'onboarding/spoiler-preferences'],
    'viewing order' => ['onboarding.viewing-order.edit', 'onboarding/viewing-order'],
    'privacy' => ['onboarding.privacy.edit', 'onboarding/privacy-defaults'],
    'review' => ['onboarding.review', 'onboarding/review'],
]);

test('published universe interests persist and archived selections are rejected without advancing', function () {
    $user = onboardingUser(OnboardingStep::UniverseInterests);
    $available = Universe::factory()->published()->create();
    $draft = Universe::factory()->create();
    $state = $user->onboardingState;

    $this->actingAs($user)->patch(route('onboarding.interests.update'), [
        'universe_ids' => [$available->id, $draft->id],
        'expected_version' => $state->lock_version,
    ])->assertSessionHasErrors('universe_ids');

    expect($state->refresh()->current_step)->toBe(OnboardingStep::UniverseInterests)
        ->and(UserFandomPreference::query()->where('user_id', $user->id)->exists())->toBeFalse();

    $this->patch(route('onboarding.interests.update'), [
        'universe_ids' => [$available->id],
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.progress.edit'));

    $this->assertDatabaseHas('user_fandom_preferences', [
        'user_id' => $user->id,
        'universe_id' => $available->id,
        'journey_visibility' => PersonalVisibility::Private->value,
    ]);
});

test('initial progress uses the existing hierarchical action and is idempotent', function () {
    $user = onboardingUser(OnboardingStep::ViewingProgress);
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->series()->published()->create(['universe_id' => $universe->id]);
    $season = Season::factory()->published()->create(['work_id' => $work->id, 'number' => 1, 'position' => 1]);
    $first = Episode::factory()->forSeason($season, 1)->published()->create();
    $second = Episode::factory()->forSeason($season, 2)->published()->create();
    UserFandomPreference::factory()->create(['user_id' => $user->id, 'universe_id' => $universe->id]);
    $state = $user->onboardingState;

    $this->actingAs($user)->patch(route('onboarding.progress.update'), [
        'mode' => 'watched_through',
        'work_id' => $work->id,
        'episode_id' => $second->id,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.spoilers.edit'));

    expect(ViewingProgress::query()->where('user_id', $user->id)->whereIn('episode_id', [$first->id, $second->id])->where('status', 'completed')->count())->toBe(2);

    $state->refresh();
    $this->actingAs($user)->get(route('onboarding.progress.edit'))->assertOk();
    $this->patch(route('onboarding.progress.update'), [
        'mode' => 'watched_through',
        'work_id' => $work->id,
        'episode_id' => $second->id,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.spoilers.edit'));

    expect(ViewingProgress::query()->where('user_id', $user->id)->whereIn('episode_id', [$first->id, $second->id])->count())->toBe(2);
});

test('spoiler order and privacy steps persist only supported typed preferences', function () {
    $user = onboardingUser(OnboardingStep::SpoilerPreferences);
    $universe = Universe::factory()->published()->create();
    $preference = UserFandomPreference::factory()->create(['user_id' => $user->id, 'universe_id' => $universe->id]);
    $order = ViewingOrder::factory()->published()->create(['universe_id' => $universe->id]);
    $state = $user->onboardingState;

    $this->actingAs($user)->patch(route('onboarding.spoilers.update'), [
        'tolerance' => SpoilerTolerance::Warn->value,
        'show_warnings' => false,
        'rewatch_behavior' => 'current_cycle',
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.viewing-order.edit'));

    $this->assertDatabaseHas('user_spoiler_preferences', [
        'user_id' => $user->id,
        'universe_id' => $universe->id,
        'tolerance' => SpoilerTolerance::Warn->value,
        'show_warnings' => false,
    ]);

    $state->refresh();
    $this->patch(route('onboarding.viewing-order.update'), [
        'viewing_order_id' => $order->id,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.privacy.edit'));

    expect($preference->refresh()->preferred_viewing_order_id)->toBe($order->id);

    $state->refresh();
    $this->patch(route('onboarding.privacy.update'), [
        'confirm_private_defaults' => true,
        'expected_version' => $state->lock_version,
    ])->assertRedirect(route('onboarding.review'));

    expect($preference->refresh()->continue_watching_visibility)->toBe(PersonalVisibility::Private)
        ->and($preference->rating_visibility)->toBe(PersonalVisibility::Private)
        ->and($preference->favourite_visibility)->toBe(PersonalVisibility::Private)
        ->and($preference->journey_visibility)->toBe(PersonalVisibility::Private);
});

test('future and stale step submissions return a stable conflict page without advancing', function () {
    $user = onboardingUser();
    $state = $user->onboardingState;

    $this->actingAs($user)->patch(route('onboarding.progress.update'), [
        'mode' => 'skip',
        'expected_version' => $state->lock_version,
    ])->assertStatus(409)->assertInertia(fn (Assert $page) => $page->component('onboarding/conflict'));

    expect($state->refresh()->current_step)->toBe(OnboardingStep::Introduction);

    $this->patch(route('onboarding.introduction.update'), ['expected_version' => 99])
        ->assertStatus(409)
        ->assertInertia(fn (Assert $page) => $page->component('onboarding/conflict'));
});

test('account deletion removes onboarding state', function () {
    $user = onboardingUser(OnboardingStep::Review);
    $stateId = $user->onboardingState->id;

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    $this->assertDatabaseMissing('user_onboarding_states', ['id' => $stateId]);
});

test('platform-suspended users see the safe suspension page before onboarding', function () {
    $user = onboardingUser();
    $restriction = UserRestriction::factory()->create([
        'user_id' => $user->id,
        'user_visible_reason' => 'Platform access is suspended during review.',
    ]);
    $restriction->scopes()->create(['scope' => RestrictionScope::PlatformAccess]);

    $this->actingAs($user)->get(route('onboarding.introduction'))
        ->assertRedirect(route('account.suspended'));
    $this->get(route('account.suspended'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/suspended')
            ->where('reason', 'Platform access is suspended during review.'));
});

<?php

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Spoilers\Actions\UpsertSpoilerBoundary;
use App\Domain\UserJourney\Actions\ManageRewatchCycles;
use App\Domain\UserJourney\Actions\ManageViewingSessions;
use App\Domain\UserJourney\Actions\RecordViewingProgress;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Enums\ProgressEventType;
use App\Enums\ProgressStatus;
use App\Enums\RewatchStatus;
use App\Enums\RoleName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Enums\SpoilerVisibility;
use App\Enums\ViewingSessionStatus;
use App\Models\Episode;
use App\Models\Season;
use App\Models\User;
use App\Models\ViewingProgress;
use App\Models\ViewingProgressEvent;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function journeySeries(): array
{
    $work = Work::factory()->series()->published()->create();
    $season = Season::factory()->for($work)->published()->create();
    $first = Episode::factory()->forSeason($season)->published()->create(['position' => 1, 'runtime_minutes' => 40]);
    $second = Episode::factory()->forSeason($season)->published()->create(['episode_number' => 2, 'display_number' => '2', 'position' => 2, 'runtime_minutes' => 40]);

    return [$work, $season, $first, $second];
}

test('episode progress derives season and work progress without overwriting explicit progress', function () {
    [$work, $season, $first, $second] = journeySeries();
    $user = User::factory()->create();
    $action = app(RecordViewingProgress::class);

    $action->handle($user, 'episode', $first->id, ['status' => 'completed', 'expected_version' => 0, 'client_request_id' => 'episode-one']);
    $seasonProgress = ViewingProgress::query()->where('user_id', $user->id)->where('scope_key', 'season:'.$season->id)->firstOrFail();
    $workProgress = ViewingProgress::query()->where('user_id', $user->id)->where('scope_key', 'work:'.$work->id)->firstOrFail();
    expect($seasonProgress->progress_basis_points)->toBe(5000)->and($workProgress->progress_basis_points)->toBe(5000);

    $action->handle($user, 'episode', $second->id, ['status' => 'completed', 'expected_version' => 0, 'client_request_id' => 'episode-two']);
    expect($seasonProgress->fresh()->status)->toBe(ProgressStatus::Completed)->and($workProgress->fresh()->status)->toBe(ProgressStatus::Completed);
});

test('progress writes are idempotent stale safe bounded and backward protected', function () {
    [, , $episode] = journeySeries();
    $user = User::factory()->create();
    $action = app(RecordViewingProgress::class);

    $progress = $action->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 600, 'expected_version' => 0, 'client_request_id' => 'same-write']);
    $same = $action->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 900, 'expected_version' => 1, 'client_request_id' => 'same-write']);
    expect($same->id)->toBe($progress->id)->and($same->runtime_position_seconds)->toBe(600)->and(ViewingProgressEvent::query()->where('client_request_id', 'same-write')->count())->toBe(1);

    expect(fn () => $action->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 700, 'expected_version' => 0]))->toThrow(OptimisticLockConflict::class);
    expect(fn () => $action->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 500, 'expected_version' => 1]))->toThrow(InvalidJourneyOperation::class);
    expect(fn () => $action->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 999999, 'expected_version' => 1]))->toThrow(InvalidJourneyOperation::class);
});

test('manual correction reset and historical spoiler knowledge are explicit', function () {
    [, , $episode] = journeySeries();
    $user = User::factory()->create();
    $actor = editorialUser(RoleName::Administrator);
    app(UpsertSpoilerBoundary::class)->handle($episode, $episode->work, $episode->season, $episode, SpoilerSeverity::Finale, SpoilerClassificationStatus::Approved, $actor);
    $action = app(RecordViewingProgress::class);

    $progress = $action->handle($user, 'episode', $episode->id, ['status' => 'completed', 'expected_version' => 0]);
    expect(app(SpoilerVisibilityService::class)->decide($episode->fresh(), $user))->toBe(SpoilerVisibility::Visible);

    $progress = $action->reset($user, $progress, 1, false);
    expect($progress->status)->toBe(ProgressStatus::NotStarted)->and(app(SpoilerVisibilityService::class)->decide($episode->fresh(), $user))->toBe(SpoilerVisibility::Visible);

    $action->reset($user, $progress, 2, true);
    expect(app(SpoilerVisibilityService::class)->decide($episode->fresh(), $user))->toBe(SpoilerVisibility::Hidden)
        ->and(ViewingProgressEvent::query()->where('viewing_progress_id', $progress->id)->where('event_type', ProgressEventType::Reset)->count())->toBe(2);
});

test('partial progress does not unlock episode completion boundaries', function () {
    [, , $episode] = journeySeries();
    $user = User::factory()->create();
    app(UpsertSpoilerBoundary::class)->handle($episode, $episode->work, $episode->season, $episode, SpoilerSeverity::Finale, SpoilerClassificationStatus::Approved, editorialUser(RoleName::Administrator));
    app(RecordViewingProgress::class)->handle($user, 'episode', $episode->id, ['runtime_position_seconds' => 1200, 'expected_version' => 0]);

    expect(app(SpoilerVisibilityService::class)->decide($episode->fresh(), $user))->toBe(SpoilerVisibility::Hidden);
});

test('viewing sessions are idempotent bounded and update progress without device fingerprint data', function () {
    [, , $episode] = journeySeries();
    $user = User::factory()->create();
    $action = app(ManageViewingSessions::class);
    $attributes = ['target_type' => 'episode', 'target_id' => $episode->id, 'client_session_id' => 'client-session', 'position_seconds' => 10, 'safe_metadata' => ['client_platform' => 'web', 'app_version' => '1.0']];

    $session = $action->start($user, $attributes);
    expect($action->start($user, $attributes)->id)->toBe($session->id);
    $session = $action->update($user, $session, ['expected_version' => 0, 'position_seconds' => 120, 'client_request_id' => 'session-progress'], true);

    expect($session->status)->toBe(ViewingSessionStatus::Ended)->and($session->watched_seconds)->toBeLessThanOrEqual(900)->and($session->safe_metadata)->toBe(['client_platform' => 'web', 'app_version' => '1.0']);
    expect(array_keys($session->getAttributes()))->not->toContain('ip_address', 'user_agent', 'device_fingerprint');
    expect(ViewingProgress::query()->where('user_id', $user->id)->where('episode_id', $episode->id)->exists())->toBeTrue();
});

test('rewatch cycles increment and preserve original completion history', function () {
    [$work, , $episode] = journeySeries();
    $user = User::factory()->create();
    $progress = app(RecordViewingProgress::class);
    $progress->handle($user, 'episode', $episode->id, ['status' => 'completed', 'expected_version' => 0]);
    $rewatches = app(ManageRewatchCycles::class);
    $first = $rewatches->start($user, $work);
    $progress->handle($user, 'episode', $episode->id, ['status' => 'in_progress', 'progress_basis_points' => 1000, 'expected_version' => 0, 'rewatch_cycle_id' => $first->id]);

    expect(ViewingProgress::query()->where('user_id', $user->id)->where('episode_id', $episode->id)->count())->toBe(2);
    expect(fn () => $rewatches->start($user, $work))->toThrow(InvalidJourneyOperation::class);
    $rewatches->transition($user, $first, RewatchStatus::Completed);
    $second = $rewatches->start($user, $work);
    expect($second->cycle_number)->toBe(2)->and(ViewingProgress::query()->where('user_id', $user->id)->where('cycle_key', 0)->where('status', ProgressStatus::Completed)->exists())->toBeTrue();
});

test('continue watching is private deterministic bounded and excludes completed content', function () {
    [, , $first, $second] = journeySeries();
    $user = User::factory()->create();
    $action = app(RecordViewingProgress::class);
    $action->handle($user, 'episode', $first->id, ['runtime_position_seconds' => 100, 'expected_version' => 0]);
    $action->handle($user, 'episode', $second->id, ['status' => 'completed', 'expected_version' => 0]);

    $this->getJson('/api/v1/me/continue-watching')->assertUnauthorized();
    $this->actingAs($user)->getJson('/api/v1/me/continue-watching?limit=1')->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.episode_id', $first->id);
});

test('progress API returns stable conflict ownership and request metadata', function () {
    [, , $episode] = journeySeries();
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $progress = app(RecordViewingProgress::class)->handle($owner, 'episode', $episode->id, ['runtime_position_seconds' => 100, 'expected_version' => 0]);

    $this->actingAs($other)->getJson("/api/v1/me/progress/episode/{$episode->id}")->assertNotFound();
    $this->actingAs($owner)->putJson("/api/v1/me/progress/episode/{$episode->id}", ['runtime_position_seconds' => 120, 'expected_version' => 0])
        ->assertConflict()
        ->assertJsonPath('error.code', 'optimistic_lock_conflict')
        ->assertJsonStructure(['meta' => ['request_id']]);
    expect($progress->fresh()->runtime_position_seconds)->toBe(100);
});

<?php

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Spoilers\Actions\UpsertSpoilerBoundary;
use App\Enums\RoleName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Enums\SpoilerTolerance;
use App\Enums\SpoilerVisibility;
use App\Models\AuditLog;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserSpoilerPreference;
use App\Models\ViewingProgress;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('normalized work season and episode paths enforce Catalog ownership', function () {
    $actor = editorialUser(RoleName::Administrator);
    $work = Work::factory()->series()->create();
    $season = Season::factory()->for($work)->create();
    $episode = Episode::factory()->forSeason($season)->create();
    $boundary = app(UpsertSpoilerBoundary::class)->handle(
        $episode,
        $work,
        $season,
        $episode,
        SpoilerSeverity::Major,
        SpoilerClassificationStatus::Approved,
        $actor,
        'Contains a major turning point.',
    );

    expect($boundary->work_id)->toBe($work->id)
        ->and($boundary->season_id)->toBe($season->id)
        ->and($boundary->episode_id)->toBe($episode->id)
        ->and(AuditLog::query()->where('event', 'spoilers.classification_created')->exists())->toBeTrue();

    $foreignSeason = Season::factory()->create();
    expect(fn () => app(UpsertSpoilerBoundary::class)->handle(
        $episode,
        $work,
        $foreignSeason,
        null,
        SpoilerSeverity::Minor,
        SpoilerClassificationStatus::Draft,
        $actor,
    ))->toThrow(InvalidEditorialOperation::class);
});

test('missing and draft classifications use conservative redaction', function () {
    $work = Work::factory()->create();
    $service = app(SpoilerVisibilityService::class);

    expect($service->decide($work))->toBe(SpoilerVisibility::Redacted);

    $work->spoilerConstraints()->create([
        'universe_id' => $work->universe_id,
        'severity' => SpoilerSeverity::None,
        'classification_status' => SpoilerClassificationStatus::Draft,
    ]);
    expect($service->decide($work->fresh()))->toBe(SpoilerVisibility::Redacted);
});

test('approved safe classification is visible', function () {
    $work = Work::factory()->create();
    $work->spoilerConstraints()->create([
        'universe_id' => $work->universe_id,
        'severity' => SpoilerSeverity::None,
        'classification_status' => SpoilerClassificationStatus::Approved,
    ]);

    expect(app(SpoilerVisibilityService::class)->decide($work->fresh()))->toBe(SpoilerVisibility::Visible);
});

test('viewer tolerance and progress produce warning redacted hidden and visible decisions', function () {
    $actor = editorialUser(RoleName::Administrator);
    $viewer = User::factory()->create();
    $work = Work::factory()->series()->create();
    $season = Season::factory()->for($work)->create(['position' => 1]);
    $episode = Episode::factory()->forSeason($season)->create(['position' => 2]);
    app(UpsertSpoilerBoundary::class)->handle($episode, $work, $season, $episode, SpoilerSeverity::Finale, SpoilerClassificationStatus::Approved, $actor);
    $service = app(SpoilerVisibilityService::class);

    expect($service->decide($episode->fresh(), $viewer))->toBe(SpoilerVisibility::Hidden);

    UserSpoilerPreference::factory()->create(['user_id' => $viewer->id, 'universe_id' => $work->universe_id, 'tolerance' => SpoilerTolerance::Permissive]);
    expect($service->decide($episode->fresh(), $viewer))->toBe(SpoilerVisibility::Warning);

    ViewingProgress::factory()->create([
        'user_id' => $viewer->id,
        'universe_id' => $work->universe_id,
        'work_id' => $work->id,
        'season_id' => $season->id,
        'episode_id' => $episode->id,
    ]);
    expect($service->decide($episode->fresh(), $viewer))->toBe(SpoilerVisibility::Visible);
});

test('explicit administrator bypass is permission based', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $work = Work::factory()->create();

    expect(app(SpoilerVisibilityService::class)->decide($work, $administrator))->toBe(SpoilerVisibility::Visible);
});

test('public serialization redacts unsafe fields and hidden records return not found', function () {
    $actor = editorialUser(RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->series()->published()->create();
    $season = Season::factory()->for($work)->published()->create();
    $episode = Episode::factory()->forSeason($season)->published()->create([
        'summary' => 'A protected reveal that must never leak.',
        'synopsis' => 'More protected detail.',
    ]);
    app(UpsertSpoilerBoundary::class)->handle($episode, $work, $season, $episode, SpoilerSeverity::Major, SpoilerClassificationStatus::Approved, $actor);

    $this->getJson("/api/v1/episodes/{$episode->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.summary', null)
        ->assertJsonPath('data.synopsis', null)
        ->assertJsonPath('data.spoiler_visibility', 'redacted')
        ->assertJsonMissing(['summary' => 'A protected reveal that must never leak.']);

    $episode->spoilerConstraints()->update(['severity' => SpoilerSeverity::Finale]);
    $this->getJson("/api/v1/episodes/{$episode->id}")->assertNotFound();
});

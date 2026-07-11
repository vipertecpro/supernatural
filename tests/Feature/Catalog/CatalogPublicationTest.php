<?php

use App\Actions\Authorization\AssignRole;
use App\Domain\Catalog\Actions\TransitionCatalogRecord;
use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\PublicationStatus;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('publication follows parent order and records minimal audit metadata', function () {
    $actor = User::factory()->create();
    app(AssignRole::class)->handle($actor, RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->series()->create(['summary' => 'Sensitive body text.']);
    $season = Season::factory()->for($work)->create();
    $episode = Episode::factory()->forSeason($season)->create(['synopsis' => 'Sensitive synopsis.']);
    $action = app(TransitionCatalogRecord::class);

    expect(fn () => $action->publish($season, $actor))->toThrow(InvalidCatalogOperation::class);
    $action->publish($work, $actor);
    $action->publish($season, $actor);
    $action->publish($episode, $actor);

    expect($episode->fresh()->status)->toBe(PublicationStatus::Published)
        ->and(AuditLog::query()->where('event', 'catalog.work_published')->exists())->toBeTrue()
        ->and(json_encode(AuditLog::query()->pluck('metadata')->all()))->not->toContain('Sensitive body text')
        ->and(json_encode(AuditLog::query()->pluck('metadata')->all()))->not->toContain('Sensitive synopsis');
});

test('duplicate publication is a stable invalid transition', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create();

    expect(fn () => app(TransitionCatalogRecord::class)->publish($work, $actor))->toThrow(InvalidCatalogOperation::class);
});

test('archival is distinct from deletion and hides catalog records', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create();

    app(TransitionCatalogRecord::class)->archive($work, $actor);

    expect($work->fresh()->status)->toBe(PublicationStatus::Archived)
        ->and($work->fresh()->archived_at)->not->toBeNull()
        ->and(Work::query()->whereKey($work)->exists())->toBeTrue()
        ->and(Work::query()->visibleToPublic()->whereKey($work)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('event', 'catalog.work_archived')->exists())->toBeTrue();
});

test('translations cannot publish before their work', function () {
    $actor = User::factory()->create();
    $translation = WorkTranslation::factory()->create();

    expect(fn () => app(TransitionCatalogRecord::class)->publish($translation, $actor))->toThrow(InvalidCatalogOperation::class);
});

test('published parent state does not automatically publish children', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->series()->create();
    $season = Season::factory()->for($work)->create();

    app(TransitionCatalogRecord::class)->publish($work, $actor);

    expect($season->fresh()->status)->toBe(PublicationStatus::Draft)
        ->and($season->fresh()->published_at)->toBeNull();
});

test('publication may retain non-public visibility', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->create();

    app(TransitionCatalogRecord::class)->publish($work, $actor, false);

    expect($work->fresh()->status)->toBe(PublicationStatus::Published)
        ->and($work->fresh()->is_public)->toBeFalse()
        ->and(Work::query()->visibleToPublic()->whereKey($work)->exists())->toBeFalse();
});

<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\PublicationStatus;
use App\Enums\RoleName;
use App\Enums\SourceType;
use App\Enums\SpoilerSeverity;
use App\Models\ContentLicense;
use App\Models\Source;
use App\Models\SpoilerConstraint;
use App\Models\Universe;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('universe factories create cast and tracking ready records', function () {
    $user = User::factory()->create();
    $universe = Universe::factory()->trackedBy($user)->published()->create([
        'metadata' => ['locale' => 'en'],
    ]);

    expect($universe->status)->toBe(PublicationStatus::Published)
        ->and($universe->is_public)->toBeTrue()
        ->and($universe->metadata)->toBe(['locale' => 'en'])
        ->and($universe->creator->is($user))->toBeTrue()
        ->and($universe->updater->is($user))->toBeTrue();
});

test('universe slugs are unique', function () {
    Universe::factory()->create(['slug' => 'shared-universe']);

    expect(fn () => Universe::factory()->create(['slug' => 'shared-universe']))
        ->toThrow(QueryException::class);
});

test('content license rights preserve an unknown state', function () {
    $license = ContentLicense::factory()->create([
        'attribution_required' => null,
        'commercial_use_allowed' => null,
        'derivative_use_allowed' => null,
    ]);

    expect($license->attribution_required)->toBeNull()
        ->and($license->commercial_use_allowed)->toBeNull()
        ->and($license->derivative_use_allowed)->toBeNull();
});

test('sources relate to universes and content licenses with constrained types', function () {
    $universe = Universe::factory()->create();
    $license = ContentLicense::factory()->create();
    $source = Source::factory()->create([
        'universe_id' => $universe->id,
        'content_license_id' => $license->id,
        'source_type' => SourceType::Interview,
    ]);

    expect($source->source_type)->toBe(SourceType::Interview)
        ->and($source->universe->is($universe))->toBeTrue()
        ->and($source->contentLicense->is($license))->toBeTrue();

    expect(fn () => Source::factory()->create(['source_type' => 'invalid']))
        ->toThrow(ValueError::class);
});

test('spoiler constraints attach polymorphically without episode assumptions', function () {
    $universe = Universe::factory()->create();
    $source = Source::factory()->create(['universe_id' => $universe->id]);
    $constraint = $source->spoilerConstraints()->create([
        'universe_id' => $universe->id,
        'severity' => SpoilerSeverity::Major,
        'earliest_progress' => ['type' => 'ordinal', 'value' => 12],
        'warning' => 'Contains a major reveal.',
        'metadata' => [],
    ]);

    expect($constraint->severity)->toBe(SpoilerSeverity::Major)
        ->and($constraint->spoilerable->is($source))->toBeTrue()
        ->and($constraint->earliest_progress)->toBe(['type' => 'ordinal', 'value' => 12]);
});

test('foreign key behavior preserves sources and removes dependent spoiler constraints', function () {
    $universe = Universe::factory()->create();
    $license = ContentLicense::factory()->create();
    $source = Source::factory()->create([
        'universe_id' => $universe->id,
        'content_license_id' => $license->id,
    ]);
    SpoilerConstraint::factory()->create([
        'universe_id' => $universe->id,
        'spoilerable_type' => Source::class,
        'spoilerable_id' => $source->id,
    ]);

    $license->delete();
    expect($source->fresh()->content_license_id)->toBeNull();

    $universe->delete();
    expect($source->fresh()->universe_id)->toBeNull()
        ->and(SpoilerConstraint::query()->count())->toBe(0);
});

test('domain policies use backend contribution and review permissions', function () {
    $fan = User::factory()->create();
    $contributor = User::factory()->create();
    app(AssignRole::class)->handle($fan, RoleName::Fan);
    app(AssignRole::class)->handle($contributor, RoleName::Contributor);

    expect(Gate::forUser($fan)->allows('create', Source::class))->toBeFalse()
        ->and(Gate::forUser($contributor)->allows('create', Source::class))->toBeTrue();
});

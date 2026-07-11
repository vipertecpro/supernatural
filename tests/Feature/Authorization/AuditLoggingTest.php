<?php

use App\Actions\Authorization\AssignRole;
use App\Actions\Authorization\RemoveRole;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('role assignments write an actor-aware audit entry', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create();

    app(AssignRole::class)->handle($subject, RoleName::Contributor, $actor, ['reason' => 'reviewed']);

    $audit = AuditLog::query()->sole();

    expect($audit->event)->toBe('authorization.role_assigned')
        ->and($audit->actor->is($actor))->toBeTrue()
        ->and($audit->auditable->is($subject))->toBeTrue()
        ->and($audit->metadata)->toMatchArray(['role' => 'contributor', 'reason' => 'reviewed']);
});

test('role removals write an audit entry', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create();
    app(AssignRole::class)->handle($subject, RoleName::Fan, $actor);

    app(RemoveRole::class)->handle($subject, RoleName::Fan, $actor);

    expect(AuditLog::query()->where('event', 'authorization.role_removed')->count())->toBe(1)
        ->and($subject->roles()->count())->toBe(0);
});

test('sensitive values are removed from audit metadata recursively', function () {
    $audit = app(AuditLogger::class)->record(
        event: 'security.tested',
        metadata: [
            'reason' => 'safe',
            'password' => 'never-store-this',
            'nested' => [
                'access_token' => 'also-never-store-this',
                'note' => 'retained',
            ],
        ],
    );

    $encoded = json_encode($audit->metadata, JSON_THROW_ON_ERROR);

    expect($audit->metadata)->toMatchArray(['reason' => 'safe', 'nested' => ['note' => 'retained']])
        ->and($encoded)->not->toContain('never-store-this')
        ->and($encoded)->not->toContain('also-never-store-this');
});

test('system generated actions may omit actor and auditable entity', function () {
    $audit = app(AuditLogger::class)->record('system.foundation_checked');

    expect($audit->actor_user_id)->toBeNull()
        ->and($audit->auditable_type)->toBeNull()
        ->and($audit->auditable_id)->toBeNull()
        ->and($audit->request_id)->not->toBeEmpty();
});

test('audit log access is permission restricted', function () {
    $fan = User::factory()->create();
    $moderator = User::factory()->create();
    app(AssignRole::class)->handle($fan, RoleName::Fan);
    app(AssignRole::class)->handle($moderator, RoleName::Moderator);

    expect(Gate::forUser($fan)->allows('viewAny', AuditLog::class))->toBeFalse()
        ->and(Gate::forUser($moderator)->allows('viewAny', AuditLog::class))->toBeTrue();
});

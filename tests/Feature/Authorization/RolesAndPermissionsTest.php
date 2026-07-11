<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('a registered user receives only the fan role', function () {
    $this->post(route('register.store'), [
        'name' => 'New Fan',
        'email' => 'fan@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'fan@example.com')->firstOrFail();

    expect($user->roles()->pluck('name')->all())->toBe([RoleName::Fan]);
});

test('a fan cannot access administrator functionality', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Fan);

    $this->actingAs($user)->get(route('administration.index'))->assertForbidden();
});

test('a contributor receives contribution permissions only', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Contributor);

    expect(Gate::forUser($user)->allows(PermissionName::ContentContribute->value))->toBeTrue()
        ->and(Gate::forUser($user)->allows(PermissionName::ContentReview->value))->toBeFalse()
        ->and(Gate::forUser($user)->allows(PermissionName::AdministrationAccess->value))->toBeFalse();
});

test('a moderator can access moderation but not administrator functionality', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Moderator);

    $this->actingAs($user)->get(route('moderation.index'))->assertNoContent();
    $this->actingAs($user)->get(route('administration.index'))->assertForbidden();
});

test('an administrator receives every defined permission', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Administrator);

    foreach (PermissionName::cases() as $permission) {
        expect(Gate::forUser($user)->allows($permission->value))->toBeTrue();
    }

    $this->actingAs($user)->get(route('administration.index'))->assertNoContent();
});

test('profile updates cannot escalate roles', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Fan);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Updated Fan',
        'email' => $user->email,
        'roles' => [RoleName::Administrator->value],
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->hasRole(RoleName::Fan))->toBeTrue()
        ->and($user->fresh()->hasRole(RoleName::Administrator))->toBeFalse();
});

test('duplicate role assignments are prevented', function () {
    $user = User::factory()->create();

    expect(app(AssignRole::class)->handle($user, RoleName::Fan))->toBeTrue()
        ->and(app(AssignRole::class)->handle($user, RoleName::Fan))->toBeFalse()
        ->and($user->roles()->count())->toBe(1);
});

test('unverified privileged users remain blocked', function () {
    $user = User::factory()->unverified()->create();
    app(AssignRole::class)->handle($user, RoleName::Administrator);

    $this->actingAs($user)
        ->get(route('administration.index'))
        ->assertRedirect(route('verification.notice'));
});

test('role and permission definitions seed idempotently', function () {
    $roleCount = Role::query()->count();
    $permissionCount = Permission::query()->count();
    $assignmentCount = DB::table('permission_role')->count();

    $this->seed(RolePermissionSeeder::class);

    expect(Role::query()->count())->toBe($roleCount)
        ->and(Permission::query()->count())->toBe($permissionCount)
        ->and(DB::table('permission_role')->count())->toBe($assignmentCount);
});

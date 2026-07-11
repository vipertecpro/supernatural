<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Franchise;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function catalogUser(RoleName $role, bool $verified = true): User
{
    $user = $verified ? User::factory()->create() : User::factory()->unverified()->create();
    app(AssignRole::class)->handle($user, $role);

    return $user;
}

test('fans and moderators receive no catalog write authority', function () {
    $fan = catalogUser(RoleName::Fan);
    $moderator = catalogUser(RoleName::Moderator);

    foreach ([$fan, $moderator] as $user) {
        expect(Gate::forUser($user)->allows('create', Work::class))->toBeFalse()
            ->and(Gate::forUser($user)->allows(PermissionName::CatalogPublish->value))->toBeFalse();
    }
});

test('contributors can create and update only their own drafts', function () {
    $contributor = catalogUser(RoleName::Contributor);
    $own = Work::factory()->trackedBy($contributor)->create();
    $other = Work::factory()->create();

    expect(Gate::forUser($contributor)->allows('create', Work::class))->toBeTrue()
        ->and(Gate::forUser($contributor)->allows('update', $own))->toBeTrue()
        ->and(Gate::forUser($contributor)->allows('update', $other))->toBeFalse()
        ->and(Gate::forUser($contributor)->allows('publish', $own))->toBeFalse();
});

test('administrators receive complete catalog authority', function () {
    $administrator = catalogUser(RoleName::Administrator);
    $franchise = Franchise::factory()->create();

    expect(Gate::forUser($administrator)->allows('update', $franchise))->toBeTrue()
        ->and(Gate::forUser($administrator)->allows('publish', $franchise))->toBeTrue()
        ->and(Gate::forUser($administrator)->allows('archive', $franchise))->toBeTrue()
        ->and(Gate::forUser($administrator)->allows('delete', $franchise))->toBeTrue();
});

test('guest fan moderator and unverified writes are rejected by the API boundary', function () {
    $universe = Universe::factory()->create();
    $payload = ['name' => 'Midnight Archive', 'slug' => 'midnight-archive'];

    $this->postJson(route('api.v1.universes.franchises.store', $universe), $payload)->assertUnauthorized();
    $this->actingAs(catalogUser(RoleName::Fan))->postJson(route('api.v1.universes.franchises.store', $universe), $payload)->assertForbidden();
    $this->actingAs(catalogUser(RoleName::Moderator))->postJson(route('api.v1.universes.franchises.store', $universe), $payload)->assertForbidden();
    $this->actingAs(catalogUser(RoleName::Contributor, false))->postJson(route('api.v1.universes.franchises.store', $universe), $payload)
        ->assertForbidden()->assertJsonPath('error.code', 'email_unverified');
});

test('profile input cannot escalate catalog permissions', function () {
    $fan = catalogUser(RoleName::Fan);

    $this->actingAs($fan)->patch(route('profile.update'), [
        'name' => $fan->name,
        'email' => $fan->email,
        'permissions' => [PermissionName::CatalogPublish->value],
    ])->assertRedirect();

    expect($fan->hasPermission(PermissionName::CatalogPublish))->toBeFalse();
});

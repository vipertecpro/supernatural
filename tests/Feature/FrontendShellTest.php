<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('public welcome renders the branded foundation page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('navigation.workspaces', []));
});

test('verified users receive the fan shell without unfinished workspace links', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('navigation.workspaces', []));
});

test('privileged no-content stubs are not exposed as frontend workspace destinations', function (string $role, string $routeName) {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::from($role));

    $this->actingAs($user)->get(route($routeName))->assertNoContent();
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('navigation.workspaces', []));
})->with([
    'moderator' => ['moderator', 'moderation.index'],
    'administrator' => ['administrator', 'administration.index'],
]);

test('frontend shell contracts retain semantic states and route-safe navigation', function () {
    $navigation = file_get_contents(resource_path('js/lib/shell/navigation.ts'));
    $tokens = file_get_contents(resource_path('css/app.css'));
    $spoilers = file_get_contents(resource_path('js/components/spoiler/spoiler-states.tsx'));

    expect($navigation)
        ->toContain("from '@/routes'")
        ->not->toContain("'/app/")
        ->and($tokens)
        ->toContain('--surface-restricted', '--spoiler-finale', 'prefers-reduced-motion', 'forced-colors')
        ->and($spoilers)
        ->toContain("'visible'", "'warning'", "'redacted'", "'hidden'");
});

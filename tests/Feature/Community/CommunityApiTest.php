<?php

use App\Actions\Authorization\AssignRole;
use App\Domain\Community\Actions\ManageBunkers;
use App\Domain\Community\Actions\ManageCommunityContent;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Enums\RoleName;
use App\Models\Bunker;
use App\Models\CommunityBookmark;
use App\Models\CommunityPost;
use App\Models\Universe;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('public bunker discovery excludes private and draft records', function () {
    $owner = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    $public = app(ManageBunkers::class)->create($owner, $universe->id, ['name' => 'Public Circle', 'visibility' => 'public']);
    app(ManageBunkers::class)->transition($public, $owner, BunkerStatus::Published, 0);
    app(ManageBunkers::class)->create($owner, $universe->id, ['name' => 'Private Circle', 'visibility' => 'private']);

    $this->getJson('/api/v1/universes/'.$universe->id.'/bunkers')->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Public Circle');
});

test('private bunkers return not found to non members', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $bunker = createCommunityBunker($owner, ['visibility' => BunkerVisibility::Private->value]);
    $bunker = app(ManageBunkers::class)->transition($bunker, $owner, BunkerStatus::Published, 0);

    $this->actingAs($outsider)->getJson('/api/v1/bunkers/'.$bunker->id)->assertNotFound();
});

test('community writes require authentication and verified email', function () {
    $universe = Universe::factory()->published()->create();
    $this->postJson('/api/v1/community/posts', ['universe_id' => $universe->id, 'body' => 'Hello'])->assertUnauthorized();
    $this->actingAs(User::factory()->unverified()->create())->postJson('/api/v1/community/posts', ['universe_id' => $universe->id, 'body' => 'Hello'])->assertForbidden()->assertJsonPath('error.code', 'email_unverified');
});

test('fan can create a bunker while platform and local roles remain separate', function () {
    $fan = User::factory()->create();
    app(AssignRole::class)->handle($fan, RoleName::Fan);
    $universe = Universe::factory()->published()->create();

    $response = $this->actingAs($fan)->postJson('/api/v1/universes/'.$universe->id.'/bunkers', ['name' => 'API Readers', 'visibility' => 'public']);
    $response->assertCreated()->assertJsonPath('data.name', 'API Readers')->assertHeader('X-Request-ID');
    expect($fan->hasRole(RoleName::Moderator))->toBeFalse()->and(Bunker::query()->first()->memberships()->first()->role->value)->toBe('owner');
});

test('bookmarks are owner only and expose no public counts', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post = CommunityPost::factory()->create();
    $bookmark = CommunityBookmark::factory()->create(['user_id' => $owner->id, 'bookmarkable_id' => $post->id]);

    $this->actingAs($other)->deleteJson('/api/v1/me/community-bookmarks/'.$bookmark->id)->assertNotFound();
    $this->actingAs($owner)->getJson('/api/v1/me/community-bookmarks')->assertSuccessful()->assertJsonMissingPath('meta.total');
});

test('unclassified community posts are conservatively redacted in feeds', function () {
    $author = User::factory()->create();
    $universe = Universe::factory()->published()->create();
    app(ManageCommunityContent::class)->createPost($author, ['universe_id' => $universe->id, 'title' => 'Protected topic', 'body' => 'Potential spoiler text']);

    $this->getJson('/api/v1/community/feed')->assertSuccessful()
        ->assertJsonPath('data.0.spoiler_visibility', 'redacted')
        ->assertJsonPath('data.0.title', null)
        ->assertJsonPath('data.0.body', null);
});

<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\RoleName;
use App\Enums\SpoilerSeverity;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\Season;
use App\Models\SpoilerConstraint;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function publishedCatalogUniverse(): Universe
{
    return Universe::factory()->published()->create();
}

function catalogAdministrator(): User
{
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Administrator);

    return $user;
}

test('public work collections exclude drafts archived records and unpublished ancestors', function () {
    $universe = publishedCatalogUniverse();
    $visible = Work::factory()->for($universe)->published()->create(['original_title' => 'The First Signal']);
    Work::factory()->for($universe)->create(['original_title' => 'Private Draft']);
    Work::factory()->for($universe)->published()->create(['original_title' => 'Archived Work', 'status' => 'archived', 'is_public' => false, 'archived_at' => now()]);
    Work::factory()->published()->create(['original_title' => 'Hidden Parent']);

    $this->getJson(route('api.v1.universes.works.index', $universe))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonPath('meta.api_version', 'v1')
        ->assertJsonStructure(['meta' => ['pagination' => ['per_page', 'next_cursor', 'has_more']]]);
});

test('public details return not found for draft work while authorized users may read it', function () {
    $draft = Work::factory()->create();
    $administrator = catalogAdministrator();

    $this->getJson(route('api.v1.works.show', $draft))->assertNotFound();
    $this->actingAs($administrator)->getJson(route('api.v1.works.show', $draft))
        ->assertSuccessful()->assertJsonPath('data.id', $draft->id);
});

test('work resources select a published requested locale and preserve canonical title', function () {
    $universe = publishedCatalogUniverse();
    $work = Work::factory()->for($universe)->published()->create(['original_title' => 'The Ember Files', 'summary' => 'Canonical summary.']);
    WorkTranslation::factory()->for($work)->published()->create(['locale' => 'fr-ca', 'title' => 'Les dossiers de braise', 'summary' => 'Résumé localisé.']);

    $this->getJson(route('api.v1.works.show', ['work' => $work, 'locale' => 'fr-CA']))
        ->assertSuccessful()
        ->assertJsonPath('data.canonical_title', 'The Ember Files')
        ->assertJsonPath('data.title', 'Les dossiers de braise')
        ->assertJsonPath('data.locale', 'fr-ca');

    $this->getJson(route('api.v1.works.show', ['work' => $work, 'locale' => 'de']))
        ->assertJsonPath('data.title', 'The Ember Files')
        ->assertJsonPath('data.locale', 'en');
});

test('spoiler constrained episode text is removed before serialization', function () {
    $universe = publishedCatalogUniverse();
    $work = Work::factory()->for($universe)->series()->published()->create();
    $season = Season::factory()->for($work)->published()->create();
    $episode = Episode::factory()->forSeason($season)->published()->create(['summary' => 'Protected reveal.', 'synopsis' => 'Protected details.']);
    SpoilerConstraint::factory()->create([
        'universe_id' => $universe->id,
        'spoilerable_type' => Episode::class,
        'spoilerable_id' => $episode->id,
        'severity' => SpoilerSeverity::Major,
    ]);

    $this->getJson(route('api.v1.episodes.show', $episode))
        ->assertSuccessful()
        ->assertJsonPath('data.spoiler_redacted', true)
        ->assertJsonPath('data.summary', null)
        ->assertJsonPath('data.synopsis', null)
        ->assertJsonMissing(['Protected reveal.', 'Protected details.']);
});

test('season and episode collections are ordered paginated and parent filtered', function () {
    $universe = publishedCatalogUniverse();
    $work = Work::factory()->for($universe)->series()->published()->create();
    $season = Season::factory()->for($work)->published()->create(['position' => 2]);
    Season::factory()->for($work)->create(['position' => 1]);
    $episode = Episode::factory()->forSeason($season, 1)->published()->create(['position' => 1]);
    Episode::factory()->forSeason($season, 2)->create(['position' => 2]);

    $this->getJson(route('api.v1.works.seasons.index', $work))->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $season->id);
    $this->getJson(route('api.v1.seasons.episodes.index', $season))->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $episode->id);
});

test('catalog index validation rejects unknown filters sorting and oversized pages', function () {
    $universe = publishedCatalogUniverse();

    $this->getJson(route('api.v1.universes.works.index', ['universe' => $universe, 'filter' => ['secret' => 'x']]))
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
    $this->getJson(route('api.v1.universes.works.index', ['universe' => $universe, 'sort' => 'secret']))->assertUnprocessable();
    $this->getJson(route('api.v1.universes.works.index', ['universe' => $universe, 'page' => ['size' => 51]]))->assertUnprocessable();
});

test('authorized writes force draft ownership and reject cross-universe nesting', function () {
    $administrator = catalogAdministrator();
    $universe = Universe::factory()->create();
    $foreignFranchise = Franchise::factory()->create();

    $this->actingAs($administrator)->postJson(route('api.v1.universes.works.store', $universe), [
        'franchise_id' => $foreignFranchise->id,
        'type' => 'film',
        'slug' => 'the-first-signal',
        'original_title' => 'The First Signal',
        'original_language' => 'en',
        'status' => 'published',
    ])->assertStatus(409)->assertJsonPath('error.code', 'invalid_catalog_transition');

    $response = $this->actingAs($administrator)->postJson(route('api.v1.universes.works.store', $universe), [
        'type' => 'film',
        'slug' => 'the-first-signal',
        'original_title' => 'The First Signal',
        'original_language' => 'en',
        'status' => 'published',
    ])->assertCreated()->assertJsonPath('data.status', 'draft');

    expect(Work::query()->findOrFail($response->json('data.id'))->created_by)->toBe($administrator->id);
});

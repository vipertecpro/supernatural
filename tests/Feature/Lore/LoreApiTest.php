<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\LoreRelationshipStatus;
use App\Enums\PublicationStatus;
use App\Enums\RoleName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\RelationshipType;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function loreUser(RoleName $role): User
{
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, $role);

    return $user;
}

test('public lore collections exclude drafts archived and restricted entities', function () {
    $universe = Universe::factory()->published()->create();
    $visible = LoreEntity::factory()->for($universe)->published()->create(['canonical_name' => 'Mara Vey']);
    LoreEntity::factory()->for($universe)->create(['canonical_name' => 'Private Draft']);
    LoreEntity::factory()->for($universe)->published()->archived()->create(['canonical_name' => 'Archived Entity']);
    LoreEntity::factory()->for($universe)->published()->create(['canonical_name' => 'Restricted Entity', 'visibility' => 'restricted']);

    $this->getJson(route('api.v1.universes.lore.index', $universe))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonStructure(['meta' => ['request_id', 'pagination' => ['per_page', 'next_cursor', 'has_more']]]);
});

test('draft detail route binding does not expose unauthorized lore', function () {
    $draft = LoreEntity::factory()->create();
    $administrator = loreUser(RoleName::Administrator);

    $this->getJson(route('api.v1.lore.show', $draft))->assertNotFound();
    $this->actingAs($administrator)->getJson(route('api.v1.lore.show', $draft))->assertSuccessful()->assertJsonPath('data.id', $draft->id);
});

test('write endpoints enforce authentication verification and role boundaries', function () {
    $universe = Universe::factory()->create();
    $payload = ['type' => 'character', 'slug' => 'mara-vey', 'canonical_name' => 'Mara Vey', 'original_language' => 'en'];

    $this->postJson(route('api.v1.universes.lore.store', $universe), $payload)->assertUnauthorized();
    $this->actingAs(loreUser(RoleName::Fan))->postJson(route('api.v1.universes.lore.store', $universe), $payload)->assertForbidden();
    $this->actingAs(User::factory()->unverified()->create())->postJson(route('api.v1.universes.lore.store', $universe), $payload)->assertForbidden()->assertJsonPath('error.code', 'email_unverified');

    $contributor = loreUser(RoleName::Contributor);
    $this->actingAs($contributor)->postJson(route('api.v1.universes.lore.store', $universe), $payload)
        ->assertCreated()->assertJsonPath('data.status', 'draft')->assertJsonPath('data.version', 0);
});

test('entity publication is explicit audited and optimistic locked', function () {
    $administrator = loreUser(RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->create(['created_by' => $administrator->id]);

    $this->actingAs($administrator)->postJson(route('api.v1.lore.publish', $entity), ['expected_version' => 0, 'is_public' => true])
        ->assertSuccessful()->assertJsonPath('data.status', 'published')->assertJsonPath('data.version', 1);
    $this->actingAs($administrator)->patchJson(route('api.v1.lore.update', $entity), ['expected_version' => 0, 'canonical_name' => 'Stale Name'])
        ->assertStatus(409)->assertJsonPath('error.code', 'optimistic_lock_conflict');
    $this->assertDatabaseHas('audit_logs', ['event' => 'lore.lore_entity_published', 'auditable_id' => $entity->id]);
});

test('localized lore selects only a published exact locale and falls back canonically', function () {
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->published()->create(['canonical_name' => 'Ember Archive', 'original_language' => 'en']);
    LoreEntityTranslation::factory()->for($entity)->create(['locale' => 'fr-ca', 'name' => 'Archives de braise', 'status' => PublicationStatus::Published, 'published_at' => now()]);
    LoreEntityTranslation::factory()->for($entity)->create(['locale' => 'de', 'name' => 'Draft Name']);

    $this->getJson(route('api.v1.lore.show', ['entity' => $entity, 'locale' => 'fr-CA']))->assertSuccessful()->assertJsonPath('data.name', 'Archives de braise')->assertJsonPath('data.locale', 'fr-ca');
    $this->getJson(route('api.v1.lore.show', ['entity' => $entity, 'locale' => 'de']))->assertJsonPath('data.name', 'Ember Archive')->assertJsonPath('data.locale', 'en');
});

test('public alias appearance and timeline collections exclude draft children', function () {
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->published()->create();
    $alias = LoreAlias::factory()->for($entity)->create(['status' => PublicationStatus::Published, 'published_at' => now()]);
    LoreAlias::factory()->for($entity)->create();
    $timeline = Timeline::factory()->for($universe)->published()->create();
    $entry = TimelineEntry::factory()->for($timeline)->create(['status' => PublicationStatus::Published, 'published_at' => now(), 'sort_key' => 1]);
    TimelineEntry::factory()->for($timeline)->create(['sort_key' => 2]);

    $this->getJson(route('api.v1.lore.aliases.index', $entity))->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $alias->id);
    $this->getJson(route('api.v1.timelines.entries.index', $timeline))->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $entry->id);
});

test('relationship serialization does not leak a spoiler-protected target or private editorial notes', function () {
    $universe = Universe::factory()->published()->create();
    $source = LoreEntity::factory()->for($universe)->published()->create();
    $target = LoreEntity::factory()->for($universe)->published()->create(['canonical_name' => 'Hidden Identity']);
    $type = RelationshipType::factory()->create();
    $relationship = LoreRelationship::factory()->create(['source_entity_id' => $source->id, 'target_entity_id' => $target->id, 'relationship_type_id' => $type->id, 'status' => LoreRelationshipStatus::Published, 'editorial_note' => 'Private reviewer-only text.', 'published_at' => now()]);
    $relationship->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::None, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);

    $this->getJson(route('api.v1.lore.relationships.index', $source))
        ->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.target', null)
        ->assertJsonMissing(['Hidden Identity', 'Private reviewer-only text.']);
});

test('lore query allowlists reject unknown filters sorts and oversized pages', function () {
    $universe = Universe::factory()->published()->create();

    $this->getJson(route('api.v1.universes.lore.index', ['universe' => $universe, 'filter' => ['secret' => 'x']]))->assertUnprocessable();
    $this->getJson(route('api.v1.universes.lore.index', ['universe' => $universe, 'sort' => 'secret']))->assertUnprocessable();
    $this->getJson(route('api.v1.universes.lore.index', ['universe' => $universe, 'page' => ['size' => 51]]))->assertUnprocessable();
});

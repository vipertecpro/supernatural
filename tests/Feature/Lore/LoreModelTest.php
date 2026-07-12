<?php

use App\Domain\Lore\Actions\MutateEntityAppearance;
use App\Domain\Lore\Actions\MutateLoreAlias;
use App\Domain\Lore\Actions\MutateLoreEntity;
use App\Domain\Lore\Actions\MutateLoreRelationship;
use App\Domain\Lore\Actions\MutateLoreTranslation;
use App\Domain\Lore\Actions\MutateTimeline;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\AppearanceKind;
use App\Enums\LoreAliasType;
use App\Enums\LoreEntityType;
use App\Enums\RelationshipDirection;
use App\Enums\TimelineEntryType;
use App\Enums\TimelineType;
use App\Models\CharacterDetail;
use App\Models\EntityAppearance;
use App\Models\LoreEntity;
use App\Models\RelationshipType;
use App\Models\Season;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\RelationshipTypeSeeder;
use Illuminate\Database\QueryException;

test('lore entities use scoped slugs stable enum casts and archive-first visibility', function () {
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->published()->create(['slug' => 'first-signal', 'type' => LoreEntityType::Character]);
    LoreEntity::factory()->for($universe)->create(['slug' => 'first-signal', 'type' => LoreEntityType::Location]);
    LoreEntity::factory()->create(['slug' => 'first-signal', 'type' => LoreEntityType::Character]);

    expect($entity->type)->toBe(LoreEntityType::Character)
        ->and(LoreEntity::query()->visibleToPublic()->pluck('id'))->toContain($entity->id)
        ->and(fn () => LoreEntity::factory()->for($universe)->create(['slug' => 'first-signal', 'type' => LoreEntityType::Character]))->toThrow(QueryException::class);
});

test('approved typed extensions are one to one and incompatible details are rejected', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->create();
    $entity = app(MutateLoreEntity::class)->create($universe, [
        'type' => LoreEntityType::Character->value,
        'slug' => 'mara-vey',
        'canonical_name' => 'Mara Vey',
        'original_language' => 'en',
        'details' => ['category' => 'traveler'],
    ], $actor);

    expect($entity->characterDetail)->toBeInstanceOf(CharacterDetail::class)
        ->and($entity->characterDetail->category)->toBe('traveler')
        ->and(fn () => CharacterDetail::factory()->for($entity)->create())->toThrow(QueryException::class)
        ->and(fn () => app(MutateLoreEntity::class)->create($universe, [
            'type' => LoreEntityType::Creature->value,
            'slug' => 'ashbound',
            'canonical_name' => 'The Ashbound',
            'original_language' => 'en',
            'details' => ['category' => 'unsupported'],
        ], $actor))->toThrow(InvalidLoreOperation::class);
});

test('entity type changes are blocked while an extension exists', function () {
    $actor = User::factory()->create();
    $entity = LoreEntity::factory()->type(LoreEntityType::Character)->create(['created_by' => $actor->id]);
    CharacterDetail::factory()->for($entity)->create();

    expect(fn () => app(MutateLoreEntity::class)->update($entity, ['expected_version' => 0, 'type' => LoreEntityType::Location->value], $actor))
        ->toThrow(InvalidLoreOperation::class);
});

test('translations and aliases normalize locale and searchable values and reject duplicates', function () {
    $actor = User::factory()->create();
    $entity = LoreEntity::factory()->create();
    $translation = app(MutateLoreTranslation::class)->create($entity, ['locale' => 'FR_ca', 'name' => 'Signal de cendre'], $actor);
    $alias = app(MutateLoreAlias::class)->create($entity, ['name' => '  Glass   Walker ', 'type' => LoreAliasType::Codename->value, 'locale' => 'EN_us'], $actor);

    expect($translation->locale)->toBe('fr-ca')
        ->and($alias->locale)->toBe('en-us')
        ->and($alias->normalized_name)->toBe('glass walker')
        ->and(fn () => app(MutateLoreTranslation::class)->create($entity, ['locale' => 'fr-ca', 'name' => 'Duplicate'], $actor))->toThrow(InvalidLoreOperation::class)
        ->and(fn () => app(MutateLoreAlias::class)->create($entity, ['name' => 'glass walker', 'type' => LoreAliasType::Codename->value, 'locale' => 'en-us'], $actor))->toThrow(InvalidLoreOperation::class);
});

test('appearances enforce universe and catalog ownership and reject null-safe duplicates', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->create();
    $entity = LoreEntity::factory()->for($universe)->create();
    $work = Work::factory()->for($universe)->series()->create();
    $season = Season::factory()->for($work)->create();
    $appearance = app(MutateEntityAppearance::class)->create($entity, ['work_id' => $work->id, 'season_id' => $season->id, 'kind' => AppearanceKind::Mention->value], $actor);

    expect($appearance)->toBeInstanceOf(EntityAppearance::class)
        ->and(fn () => app(MutateEntityAppearance::class)->create($entity, ['work_id' => $work->id, 'season_id' => $season->id, 'kind' => AppearanceKind::Mention->value], $actor))->toThrow(InvalidLoreOperation::class)
        ->and(fn () => app(MutateEntityAppearance::class)->create($entity, ['work_id' => Work::factory()->create()->id, 'kind' => AppearanceKind::Appearance->value], $actor))->toThrow(InvalidLoreOperation::class);
});

test('symmetric relationships are canonicalized and mirrored duplicates are rejected', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->create();
    $first = LoreEntity::factory()->for($universe)->create();
    $second = LoreEntity::factory()->for($universe)->create();
    $type = RelationshipType::factory()->create(['direction' => RelationshipDirection::Undirected, 'is_symmetric' => true]);
    $type->rules()->create(['source_entity_type' => LoreEntityType::Character, 'target_entity_type' => LoreEntityType::Character]);
    $relationship = app(MutateLoreRelationship::class)->create(['source_entity_id' => $second->id, 'target_entity_id' => $first->id, 'relationship_type_id' => $type->id], $actor);

    expect($relationship->source_entity_id)->toBe(min($first->id, $second->id))
        ->and($relationship->target_entity_id)->toBe(max($first->id, $second->id))
        ->and(fn () => app(MutateLoreRelationship::class)->create(['source_entity_id' => $first->id, 'target_entity_id' => $second->id, 'relationship_type_id' => $type->id], $actor))->toThrow(InvalidLoreOperation::class);
});

test('relationship rules reject self disallowed endpoint and cross-universe edges', function () {
    $actor = User::factory()->create();
    $source = LoreEntity::factory()->type(LoreEntityType::Character)->create();
    $target = LoreEntity::factory()->type(LoreEntityType::Location)->create(['universe_id' => $source->universe_id]);
    $foreign = LoreEntity::factory()->create();
    $type = RelationshipType::factory()->create(['is_symmetric' => false, 'direction' => RelationshipDirection::Directed]);
    $type->rules()->create(['source_entity_type' => LoreEntityType::Character, 'target_entity_type' => LoreEntityType::Performer]);

    expect(fn () => app(MutateLoreRelationship::class)->create(['source_entity_id' => $source->id, 'target_entity_id' => $source->id, 'relationship_type_id' => $type->id], $actor))->toThrow(InvalidLoreOperation::class)
        ->and(fn () => app(MutateLoreRelationship::class)->create(['source_entity_id' => $source->id, 'target_entity_id' => $target->id, 'relationship_type_id' => $type->id], $actor))->toThrow(InvalidLoreOperation::class)
        ->and(fn () => app(MutateLoreRelationship::class)->create(['source_entity_id' => $source->id, 'target_entity_id' => $foreign->id, 'relationship_type_id' => $type->id], $actor))->toThrow(InvalidLoreOperation::class);
});

test('timelines enforce universe targets and deterministic unique ordering', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->create();
    $timeline = app(MutateTimeline::class)->createTimeline($universe, ['name' => 'Hollow Meridian', 'slug' => 'hollow-meridian', 'type' => TimelineType::Universe->value], $actor);
    $entity = LoreEntity::factory()->for($universe)->create();
    $first = app(MutateTimeline::class)->createEntry($timeline, ['type' => TimelineEntryType::Entity->value, 'title' => 'The First Signal', 'sort_key' => 10, 'entity_ids' => [$entity->id]], $actor);

    expect($first->entities)->toHaveCount(1)
        ->and(fn () => app(MutateTimeline::class)->createEntry($timeline, ['type' => TimelineEntryType::EditorialMarker->value, 'title' => 'Duplicate order', 'sort_key' => 10], $actor))->toThrow(QueryException::class)
        ->and(fn () => app(MutateTimeline::class)->createEntry($timeline, ['type' => TimelineEntryType::Entity->value, 'title' => 'Foreign', 'sort_key' => 20, 'entity_ids' => [LoreEntity::factory()->create()->id]], $actor))->toThrow(InvalidLoreOperation::class);
});

test('relationship type seeding is idempotent and uses inverse presentation metadata', function () {
    $this->seed(RelationshipTypeSeeder::class);
    $this->seed(RelationshipTypeSeeder::class);

    expect(RelationshipType::query()->where('key', 'portrayed_by')->count())->toBe(1)
        ->and(RelationshipType::query()->where('key', 'portrayed_by')->value('inverse_label'))->toBe('portrays')
        ->and(RelationshipType::query()->where('key', 'related_to')->firstOrFail()->rules()->count())->toBe(count(LoreEntityType::cases()) ** 2);
});

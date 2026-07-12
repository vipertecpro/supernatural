<?php

use App\Domain\Editorial\Actions\ApplyEditorialRevision;
use App\Domain\Editorial\Actions\AssignEditorialReview;
use App\Domain\Editorial\Actions\CreateCitation;
use App\Domain\Editorial\Actions\CreateEditorialRevision;
use App\Domain\Editorial\Actions\DecideEditorialRevision;
use App\Domain\Editorial\Actions\TransitionEditorialRevision;
use App\Domain\Editorial\Actions\UpsertRevisionItem;
use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Actions\MutateLoreRelationship;
use App\Domain\Lore\Actions\TransitionLoreRecord;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Enums\CitationReviewStatus;
use App\Enums\LoreEntityType;
use App\Enums\RoleName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Events\LoreEntityPublished;
use App\Models\Citation;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\RelationshipType;
use App\Models\Source;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('lore targets participate in attributable transactional editorial revisions', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $timeline = Timeline::factory()->create(['created_by' => $author->id]);
    $entry = TimelineEntry::factory()->for($timeline)->create(['sort_key' => 1, 'created_by' => $author->id]);
    $revision = app(CreateEditorialRevision::class)->handle($entry, ['summary' => 'Correct relative chronology.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'sort_key', 2);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);
    app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'Ordering evidence reviewed.');
    app(ApplyEditorialRevision::class)->handle($revision->fresh(), $reviewer);

    expect($entry->fresh()->sort_key)->toBe('2.000000')
        ->and($entry->fresh()->lock_version)->toBe(1)
        ->and($revision->fresh()->status->value)->toBe('applied');
});

test('lore editorial registry rejects protected fields and stale application', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $entry = TimelineEntry::factory()->create(['sort_key' => 1, 'created_by' => $author->id]);
    $revision = app(CreateEditorialRevision::class)->handle($entry, ['summary' => 'Correct chronology.'], $author);

    expect(fn () => app(UpsertRevisionItem::class)->handle($revision, 'created_by', $reviewer->id))->toThrow(InvalidEditorialOperation::class);

    app(UpsertRevisionItem::class)->handle($revision, 'sort_key', 2);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);
    app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'Approved.');
    $entry->update(['lock_version' => 1]);

    expect(fn () => app(ApplyEditorialRevision::class)->handle($revision->fresh(), $reviewer))->toThrow(OptimisticLockConflict::class);
});

test('relationship publication requires verified citation and approved spoiler classification', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $universe = Universe::factory()->published()->create();
    $sourceEntity = LoreEntity::factory()->for($universe)->published()->create();
    $targetEntity = LoreEntity::factory()->for($universe)->published()->create();
    $type = RelationshipType::factory()->create(['is_symmetric' => false]);
    $type->rules()->create(['source_entity_type' => LoreEntityType::Character, 'target_entity_type' => LoreEntityType::Character]);
    $relationship = app(MutateLoreRelationship::class)->create(['source_entity_id' => $sourceEntity->id, 'target_entity_id' => $targetEntity->id, 'relationship_type_id' => $type->id], $administrator);

    expect(fn () => app(TransitionLoreRecord::class)->publish($relationship, $administrator, 0))->toThrow(InvalidLoreOperation::class);

    $source = Source::factory()->for($universe)->create();
    app(CreateCitation::class)->handle($relationship, ['review_status' => CitationReviewStatus::Verified], [$source->id], $administrator);
    $relationship->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::None, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);
    $published = app(TransitionLoreRecord::class)->publish($relationship->fresh(), $administrator, 0);

    expect($published->status->value)->toBe('published')
        ->and(Citation::query()->where('citable_type', 'lore_relationship')->where('citable_id', $relationship->id)->exists())->toBeTrue();
});

test('spoiler-sensitive aliases are redacted before serialization', function () {
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->published()->create();
    $alias = LoreAlias::factory()->for($entity)->create(['name' => 'The Hidden Heir', 'spoiler_sensitive' => true, 'status' => 'published', 'published_at' => now()]);
    $alias->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::Major, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);

    $this->getJson(route('api.v1.lore.aliases.index', $entity))
        ->assertSuccessful()->assertJsonPath('data.0.name', null)->assertJsonPath('data.0.spoiler_redacted', true)->assertJsonMissing(['The Hidden Heir']);
});

test('hidden timeline entries are omitted without leaking through collection size', function () {
    $universe = Universe::factory()->published()->create();
    $timeline = Timeline::factory()->for($universe)->published()->create();
    $visible = TimelineEntry::factory()->for($timeline)->create(['status' => 'published', 'published_at' => now(), 'sort_key' => 1]);
    $hidden = TimelineEntry::factory()->for($timeline)->create(['status' => 'published', 'published_at' => now(), 'sort_key' => 2, 'title' => 'Final identity reveal']);
    $visible->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::None, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);
    $hidden->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::Finale, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);

    $this->getJson(route('api.v1.timelines.entries.index', $timeline))
        ->assertSuccessful()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id)->assertJsonMissing(['Final identity reveal']);
});

test('publication emits a scalar after-commit lore domain event without broadcasting', function () {
    Event::fake([LoreEntityPublished::class]);
    $administrator = editorialUser(RoleName::Administrator);
    $entity = LoreEntity::factory()->for(Universe::factory()->published())->create();

    app(TransitionLoreRecord::class)->publish($entity, $administrator, 0);

    Event::assertDispatched(LoreEntityPublished::class, fn (LoreEntityPublished $event): bool => $event->loreEntityId === $entity->id && $event->actorUserId === $administrator->id);
});

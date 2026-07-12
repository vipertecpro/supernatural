<?php

use App\Domain\Search\Services\SearchProjector;
use App\Domain\Search\Services\SearchQueryService;
use App\Enums\PublicationStatus;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\SearchDocument;
use App\Models\SearchQuery;
use App\Models\SearchSuggestion;
use App\Models\Universe;
use App\Models\User;
use App\Models\ViewingProgress;
use App\Models\Work;
use App\Models\WorkTranslation;
use Laravel\Sanctum\Sanctum;

function safeSearchSource(Work|LoreEntity $source): void
{
    $source->spoilerConstraints()->create(['universe_id' => $source->universe_id, 'severity' => SpoilerSeverity::None, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);
}

test('published work creates deterministic multi locale projections and draft sources do not', function () {
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create(['original_title' => 'The First Signal', 'original_language' => 'en']);
    WorkTranslation::factory()->for($work)->create(['locale' => 'fr-ca', 'title' => 'Le Premier Signal', 'status' => PublicationStatus::Published, 'published_at' => now()]);
    safeSearchSource($work);

    $first = app(SearchProjector::class)->project($work);
    $second = app(SearchProjector::class)->project($work->fresh());
    $draft = Work::factory()->for($universe)->create();
    $draftResult = app(SearchProjector::class)->project($draft);

    expect($first['created'])->toBe(2)->and($second['created'])->toBe(0)->and($second['unchanged'])->toBe(2)
        ->and($draftResult['created'])->toBe(0)->and(SearchDocument::query()->where('source_id', $work->id)->pluck('locale')->all())->toEqualCanonicalizing(['en', 'fr-ca']);
});

test('projection refresh records source version and removal never mutates authoritative content', function () {
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create(['original_title' => 'Glass Horizon', 'lock_version' => 2]);
    safeSearchSource($work);
    app(SearchProjector::class)->project($work);
    $work->update(['original_title' => 'Glass Horizon Revised', 'lock_version' => 3]);
    app(SearchProjector::class)->project($work->fresh());

    expect(SearchDocument::query()->where('source_id', $work->id)->value('source_lock_version'))->toBe(3)
        ->and(SearchDocument::query()->where('source_id', $work->id)->value('canonical_title'))->toBe('Glass Horizon Revised');
    app(SearchProjector::class)->remove($work);
    expect($work->fresh())->not->toBeNull()->and(SearchDocument::query()->where('source_id', $work->id)->exists())->toBeFalse();
});

test('relational search ranks exact titles before summary matches and records only a query hash', function () {
    $universe = Universe::factory()->published()->create();
    $exact = Work::factory()->for($universe)->published()->create(['original_title' => 'Ember Archive', 'summary' => 'A quiet fictional index.']);
    $summary = Work::factory()->for($universe)->published()->create(['original_title' => 'Glass Meridian', 'summary' => 'The Ember Archive appears in this summary.']);
    safeSearchSource($exact);
    safeSearchSource($summary);
    app(SearchProjector::class)->project($exact);
    app(SearchProjector::class)->project($summary);

    $results = app(SearchQueryService::class)->search('Ember Archive', ['universe_id' => $universe->id, 'locale' => 'en', 'page_size' => 10], null);

    expect($results['items'])->toHaveCount(2)->and($results['items'][0]['id'])->toBe($exact->id)
        ->and(SearchQuery::query()->value('query_hash'))->toHaveLength(64);
    $this->assertDatabaseMissing('search_queries', ['query_hash' => 'ember archive']);
});

test('spoiler sensitive aliases are excluded from projections and suggestions', function () {
    $universe = Universe::factory()->published()->create();
    $entity = LoreEntity::factory()->for($universe)->published()->create(['canonical_name' => 'Mara Vey']);
    safeSearchSource($entity);
    LoreAlias::factory()->for($entity)->create(['name' => 'Safe Alias', 'normalized_name' => 'safe alias', 'status' => PublicationStatus::Published, 'spoiler_sensitive' => false, 'published_at' => now()]);
    LoreAlias::factory()->for($entity)->create(['name' => 'Hidden Identity', 'normalized_name' => 'hidden identity', 'status' => PublicationStatus::Published, 'spoiler_sensitive' => true, 'published_at' => now()]);

    app(SearchProjector::class)->project($entity);

    expect(SearchSuggestion::query()->pluck('value'))->toContain('Safe Alias')->not->toContain('Hidden Identity')
        ->and(SearchDocument::query()->value('normalized_text'))->not->toContain('hidden identity');
});

test('search rebuild supports dry run idempotency filters and explicit prune', function () {
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create();
    safeSearchSource($work);

    $this->artisan('search:rebuild', ['--type' => 'work', '--dry-run' => true])->assertSuccessful();
    expect(SearchDocument::query()->count())->toBe(0);
    $this->artisan('search:rebuild', ['--type' => 'work', '--universe' => $universe->id, '--chunk' => 1])->assertSuccessful();
    $this->artisan('search:rebuild', ['--type' => 'work', '--universe' => $universe->id])->assertSuccessful();
    expect(SearchDocument::query()->count())->toBe(1);

    SearchDocument::factory()->create(['source_type' => 'work', 'source_id' => 999999, 'universe_id' => $universe->id]);
    $this->artisan('search:rebuild', ['--type' => 'work', '--prune' => true])->assertSuccessful();
    expect(SearchDocument::query()->where('source_id', 999999)->exists())->toBeFalse();
});

test('search API validates bounds returns stable envelope and never exposes projection internals', function () {
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create(['original_title' => 'The Ember Files']);
    safeSearchSource($work);
    app(SearchProjector::class)->project($work);

    $this->getJson(route('api.v1.search.index', ['q' => 'Ember', 'filter' => ['universe_id' => $universe->id], 'page' => ['size' => 10]]))
        ->assertSuccessful()->assertJsonPath('data.0.id', $work->id)->assertJsonStructure(['meta' => ['api_version', 'request_id', 'pagination']])
        ->assertJsonMissingPath('data.0.normalized_text')->assertJsonMissingPath('data.0.ranking_weight');
    $this->getJson(route('api.v1.search.index', ['q' => 'x']))->assertUnprocessable();
    $this->getJson(route('api.v1.search.index', ['q' => 'Ember', 'page' => ['size' => 51]]))->assertUnprocessable();
    $this->getJson(route('api.v1.search.index', ['q' => 'Ember', 'sort' => 'raw_sql']))->assertUnprocessable();
});

test('public search remains guest accessible and resolves optional Sanctum spoiler context', function () {
    $universe = Universe::factory()->published()->create();
    $work = Work::factory()->for($universe)->published()->create(['original_title' => 'Hollow Meridian']);
    $constraint = $work->spoilerConstraints()->create(['universe_id' => $universe->id, 'severity' => SpoilerSeverity::Major, 'classification_status' => SpoilerClassificationStatus::Approved, 'earliest_progress' => [], 'metadata' => []]);
    $constraint->boundaries()->create(['work_id' => $work->id]);
    app(SearchProjector::class)->project($work);

    $this->getJson(route('api.v1.search.index', ['q' => 'Hollow']))
        ->assertSuccessful()->assertJsonPath('data.0.spoiler_visibility', 'redacted');

    $viewer = User::factory()->create();
    ViewingProgress::factory()->create(['user_id' => $viewer->id, 'universe_id' => $universe->id, 'work_id' => $work->id, 'season_id' => null, 'episode_id' => null]);
    Sanctum::actingAs($viewer);

    $this->getJson(route('api.v1.search.index', ['q' => 'Hollow']))
        ->assertSuccessful()->assertJsonPath('data.0.spoiler_visibility', 'visible');
});

<?php

use App\Domain\Catalog\Actions\CreateEpisode;
use App\Domain\Catalog\Actions\CreateSeason;
use App\Domain\Catalog\Actions\CreateWork;
use App\Domain\Catalog\Actions\UpdateWork;
use App\Domain\Catalog\Actions\UpsertWorkTranslation;
use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Enums\EpisodeType;
use App\Enums\SeasonType;
use App\Enums\WorkType;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\Season;
use App\Models\SeriesDetail;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Database\QueryException;

test('catalog factories create a coherent episodic hierarchy with enum casts', function () {
    $work = Work::factory()->series()->create();
    $details = SeriesDetail::factory()->for($work)->create();
    $season = Season::factory()->for($work)->create();
    $episode = Episode::factory()->forSeason($season)->create();

    expect($work->type)->toBe(WorkType::Series)
        ->and($details->work->is($work))->toBeTrue()
        ->and($season->type)->toBe(SeasonType::Season)
        ->and($episode->type)->toBe(EpisodeType::Standard)
        ->and($episode->season->is($season))->toBeTrue()
        ->and($episode->work->is($work))->toBeTrue();
});

test('franchise and work slugs are unique only within a universe', function () {
    $first = Universe::factory()->create();
    $second = Universe::factory()->create();
    Franchise::factory()->for($first)->create(['slug' => 'midnight-archive']);
    Franchise::factory()->for($second)->create(['slug' => 'midnight-archive']);
    Work::factory()->for($first)->create(['slug' => 'first-signal']);
    Work::factory()->for($second)->create(['slug' => 'first-signal']);

    expect(fn () => Franchise::factory()->for($first)->create(['slug' => 'midnight-archive']))->toThrow(QueryException::class)
        ->and(fn () => Work::factory()->for($first)->create(['slug' => 'first-signal']))->toThrow(QueryException::class);
});

test('work translations normalize locale and remain unique per work', function () {
    $actor = User::factory()->create();
    $work = Work::factory()->trackedBy($actor)->create();
    $translation = app(UpsertWorkTranslation::class)->handle($work, 'FR_ca', ['title' => 'Archives de minuit'], $actor);

    expect($translation->locale)->toBe('fr-ca')
        ->and($translation->work->is($work))->toBeTrue();

    expect(fn () => WorkTranslation::factory()->for($work)->create(['locale' => 'fr-ca']))->toThrow(QueryException::class);
});

test('series details and seasons reject non-series works', function () {
    $actor = User::factory()->create();
    $universe = Universe::factory()->create();
    $film = Work::factory()->for($universe)->create(['type' => WorkType::Film]);

    expect(fn () => app(CreateSeason::class)->handle($film, [
        'title' => 'Season One', 'slug' => 'season-one', 'type' => SeasonType::Season,
    ], $actor))->toThrow(InvalidCatalogOperation::class);

    expect(fn () => app(CreateWork::class)->handle($universe, [
        'type' => WorkType::Film->value,
        'slug' => 'not-a-series',
        'original_title' => 'Not a Series',
        'original_language' => 'en',
        'series_details' => ['format' => 'streaming'],
    ], $actor))->toThrow(InvalidCatalogOperation::class);
});

test('episode creation always derives its work from the season', function () {
    $actor = User::factory()->create();
    $season = Season::factory()->create();
    $episode = app(CreateEpisode::class)->handle($season, [
        'episode_number' => 1,
        'title' => 'The First Signal',
        'slug' => 'the-first-signal',
    ], $actor);

    expect($episode->season_id)->toBe($season->id)
        ->and($episode->work_id)->toBe($season->work_id);
});

test('database constraints enforce episode numbering and durable parent protection', function () {
    $season = Season::factory()->create();
    Episode::factory()->forSeason($season, 1)->create(['absolute_number' => 1]);

    expect(fn () => Episode::factory()->forSeason($season, 1)->create(['absolute_number' => 2]))->toThrow(QueryException::class)
        ->and(fn () => $season->work->delete())->toThrow(QueryException::class);
});

test('cross-universe franchises and unsafe work type changes are rejected', function () {
    $actor = User::factory()->create();
    $work = Work::factory()->series()->create();
    $foreignFranchise = Franchise::factory()->create();
    SeriesDetail::factory()->for($work)->create();

    expect(fn () => app(UpdateWork::class)->handle($work, ['franchise_id' => $foreignFranchise->id], $actor))->toThrow(InvalidCatalogOperation::class)
        ->and(fn () => app(UpdateWork::class)->handle($work, ['type' => WorkType::Film->value], $actor))->toThrow(InvalidCatalogOperation::class);
});

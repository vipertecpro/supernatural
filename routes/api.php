<?php

use App\Http\Controllers\Api\V1\EditorialCitationController;
use App\Http\Controllers\Api\V1\EditorialReviewController;
use App\Http\Controllers\Api\V1\EditorialRevisionController;
use App\Http\Controllers\Api\V1\EntityAppearanceController;
use App\Http\Controllers\Api\V1\EpisodeController;
use App\Http\Controllers\Api\V1\FranchiseController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\LoreAliasController;
use App\Http\Controllers\Api\V1\LoreEntityController;
use App\Http\Controllers\Api\V1\LoreRelationshipController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\SeasonController;
use App\Http\Controllers\Api\V1\SourceRightsReviewController;
use App\Http\Controllers\Api\V1\SpoilerBoundaryController;
use App\Http\Controllers\Api\V1\TimelineController;
use App\Http\Controllers\Api\V1\TimelineEntryController;
use App\Http\Controllers\Api\V1\WorkController;
use App\Http\Controllers\Api\V1\WorkTranslationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('health', HealthController::class)
        ->middleware('throttle:api-v1-public')
        ->name('health');

    Route::get('me', MeController::class)
        ->middleware(['auth:sanctum', 'verified', 'throttle:api-v1'])
        ->name('me');

    Route::middleware('throttle:api-v1-public')->scopeBindings()->group(function () {
        Route::get('universes/{universe}/franchises', [FranchiseController::class, 'index'])->name('universes.franchises.index');
        Route::get('franchises/{franchise}', [FranchiseController::class, 'show'])->name('franchises.show');
        Route::get('universes/{universe}/works', [WorkController::class, 'index'])->name('universes.works.index');
        Route::get('works/{work}', [WorkController::class, 'show'])->name('works.show');
        Route::get('works/{work}/seasons', [SeasonController::class, 'index'])->name('works.seasons.index');
        Route::get('seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
        Route::get('seasons/{season}/episodes', [EpisodeController::class, 'index'])->name('seasons.episodes.index');
        Route::get('episodes/{episode}', [EpisodeController::class, 'show'])->name('episodes.show');
        Route::get('universes/{universe}/lore', [LoreEntityController::class, 'index'])->name('universes.lore.index');
        Route::get('lore/{entity}', [LoreEntityController::class, 'show'])->name('lore.show');
        Route::get('lore/{entity}/aliases', [LoreAliasController::class, 'index'])->name('lore.aliases.index');
        Route::get('lore/{entity}/appearances', [EntityAppearanceController::class, 'index'])->name('lore.appearances.index');
        Route::get('lore/{entity}/relationships', [LoreRelationshipController::class, 'index'])->name('lore.relationships.index');
        Route::get('lore/{entity}/timeline-entries', [TimelineEntryController::class, 'forEntity'])->name('lore.timeline-entries.index');
        Route::get('universes/{universe}/timelines', [TimelineController::class, 'index'])->name('universes.timelines.index');
        Route::get('timelines/{timeline}', [TimelineController::class, 'show'])->name('timelines.show');
        Route::get('timelines/{timeline}/entries', [TimelineEntryController::class, 'index'])->name('timelines.entries.index');
    });

    Route::middleware(['auth:sanctum', 'verified', 'throttle:api-v1'])->scopeBindings()->group(function () {
        Route::post('universes/{universe}/franchises', [FranchiseController::class, 'store'])->name('universes.franchises.store');
        Route::patch('franchises/{franchise}', [FranchiseController::class, 'update'])->name('franchises.update');
        Route::post('franchises/{franchise}/publish', [FranchiseController::class, 'publish'])->name('franchises.publish');
        Route::post('franchises/{franchise}/archive', [FranchiseController::class, 'archive'])->name('franchises.archive');
        Route::delete('franchises/{franchise}', [FranchiseController::class, 'destroy'])->name('franchises.destroy');

        Route::post('universes/{universe}/works', [WorkController::class, 'store'])->name('universes.works.store');
        Route::patch('works/{work}', [WorkController::class, 'update'])->name('works.update');
        Route::post('works/{work}/publish', [WorkController::class, 'publish'])->name('works.publish');
        Route::post('works/{work}/archive', [WorkController::class, 'archive'])->name('works.archive');
        Route::delete('works/{work}', [WorkController::class, 'destroy'])->name('works.destroy');

        Route::post('works/{work}/translations', [WorkTranslationController::class, 'store'])->name('works.translations.store');
        Route::patch('works/{work}/translations/{locale}', [WorkTranslationController::class, 'update'])->name('works.translations.update');
        Route::post('works/{work}/translations/{locale}/publish', [WorkTranslationController::class, 'publish'])->name('works.translations.publish');

        Route::post('works/{work}/seasons', [SeasonController::class, 'store'])->name('works.seasons.store');
        Route::patch('seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
        Route::post('seasons/{season}/publish', [SeasonController::class, 'publish'])->name('seasons.publish');
        Route::post('seasons/{season}/archive', [SeasonController::class, 'archive'])->name('seasons.archive');
        Route::delete('seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');

        Route::post('seasons/{season}/episodes', [EpisodeController::class, 'store'])->name('seasons.episodes.store');
        Route::patch('episodes/{episode}', [EpisodeController::class, 'update'])->name('episodes.update');
        Route::post('episodes/{episode}/publish', [EpisodeController::class, 'publish'])->name('episodes.publish');
        Route::post('episodes/{episode}/archive', [EpisodeController::class, 'archive'])->name('episodes.archive');
        Route::delete('episodes/{episode}', [EpisodeController::class, 'destroy'])->name('episodes.destroy');

        Route::post('universes/{universe}/lore', [LoreEntityController::class, 'store'])->name('universes.lore.store');
        Route::patch('lore/{entity}', [LoreEntityController::class, 'update'])->name('lore.update');
        Route::post('lore/{entity}/publish', [LoreEntityController::class, 'publish'])->name('lore.publish');
        Route::post('lore/{entity}/archive', [LoreEntityController::class, 'archive'])->name('lore.archive');
        Route::delete('lore/{entity}', [LoreEntityController::class, 'destroy'])->name('lore.destroy');
        Route::post('lore/{entity}/translations', [LoreEntityController::class, 'storeTranslation'])->name('lore.translations.store');
        Route::patch('lore-translations/{translation}', [LoreEntityController::class, 'updateTranslation'])->name('lore-translations.update');
        Route::post('lore-translations/{translation}/publish', [LoreEntityController::class, 'publishTranslation'])->name('lore-translations.publish');

        Route::post('lore/{entity}/aliases', [LoreAliasController::class, 'store'])->name('lore.aliases.store');
        Route::patch('lore-aliases/{alias}', [LoreAliasController::class, 'update'])->name('lore-aliases.update');
        Route::post('lore-aliases/{alias}/publish', [LoreAliasController::class, 'publish'])->name('lore-aliases.publish');
        Route::post('lore-aliases/{alias}/archive', [LoreAliasController::class, 'archive'])->name('lore-aliases.archive');

        Route::post('lore/{entity}/appearances', [EntityAppearanceController::class, 'store'])->name('lore.appearances.store');
        Route::patch('lore-appearances/{appearance}', [EntityAppearanceController::class, 'update'])->name('lore-appearances.update');
        Route::post('lore-appearances/{appearance}/publish', [EntityAppearanceController::class, 'publish'])->name('lore-appearances.publish');
        Route::post('lore-appearances/{appearance}/archive', [EntityAppearanceController::class, 'archive'])->name('lore-appearances.archive');

        Route::post('lore-relationships', [LoreRelationshipController::class, 'store'])->name('lore-relationships.store');
        Route::patch('lore-relationships/{relationship}', [LoreRelationshipController::class, 'update'])->name('lore-relationships.update');
        Route::post('lore-relationships/{relationship}/publish', [LoreRelationshipController::class, 'publish'])->name('lore-relationships.publish');
        Route::post('lore-relationships/{relationship}/archive', [LoreRelationshipController::class, 'archive'])->name('lore-relationships.archive');

        Route::post('universes/{universe}/timelines', [TimelineController::class, 'store'])->name('universes.timelines.store');
        Route::patch('timelines/{timeline}', [TimelineController::class, 'update'])->name('timelines.update');
        Route::post('timelines/{timeline}/publish', [TimelineController::class, 'publish'])->name('timelines.publish');
        Route::post('timelines/{timeline}/archive', [TimelineController::class, 'archive'])->name('timelines.archive');
        Route::post('timelines/{timeline}/entries', [TimelineEntryController::class, 'store'])->name('timelines.entries.store');
        Route::patch('timeline-entries/{entry}', [TimelineEntryController::class, 'update'])->name('timeline-entries.update');
        Route::post('timeline-entries/{entry}/publish', [TimelineEntryController::class, 'publish'])->name('timeline-entries.publish');
        Route::post('timeline-entries/{entry}/archive', [TimelineEntryController::class, 'archive'])->name('timeline-entries.archive');

        Route::prefix('editorial')->name('editorial.')->group(function () {
            Route::get('revisions', [EditorialRevisionController::class, 'index'])->name('revisions.index');
            Route::post('revisions', [EditorialRevisionController::class, 'store'])->name('revisions.store');
            Route::get('revisions/{revision}', [EditorialRevisionController::class, 'show'])->name('revisions.show');
            Route::patch('revisions/{revision}', [EditorialRevisionController::class, 'update'])->name('revisions.update');
            Route::post('revisions/{revision}/items', [EditorialRevisionController::class, 'storeItem'])->name('revisions.items.store');
            Route::patch('revisions/{revision}/items/{item}', [EditorialRevisionController::class, 'updateItem'])->name('revisions.items.update');
            Route::delete('revisions/{revision}/items/{item}', [EditorialRevisionController::class, 'destroyItem'])->name('revisions.items.destroy');
            Route::post('revisions/{revision}/blocks', [EditorialRevisionController::class, 'storeBlock'])->name('revisions.blocks.store');
            Route::delete('revisions/{revision}/blocks/{block}', [EditorialRevisionController::class, 'destroyBlock'])->name('revisions.blocks.destroy');
            Route::post('revisions/{revision}/submit', [EditorialRevisionController::class, 'submit'])->name('revisions.submit');
            Route::post('revisions/{revision}/withdraw', [EditorialRevisionController::class, 'withdraw'])->name('revisions.withdraw');
            Route::post('revisions/{revision}/resubmit', [EditorialRevisionController::class, 'resubmit'])->name('revisions.resubmit');

            Route::post('revisions/{revision}/assign', [EditorialReviewController::class, 'assign'])->name('revisions.assign');
            Route::post('revisions/{revision}/assignments/{assignment}/cancel', [EditorialReviewController::class, 'cancelAssignment'])->name('revisions.assignments.cancel');
            Route::post('revisions/{revision}/begin-review', [EditorialReviewController::class, 'begin'])->name('revisions.begin-review');
            Route::post('revisions/{revision}/request-changes', [EditorialReviewController::class, 'requestChanges'])->name('revisions.request-changes');
            Route::post('revisions/{revision}/approve', [EditorialReviewController::class, 'approve'])->name('revisions.approve');
            Route::post('revisions/{revision}/reject', [EditorialReviewController::class, 'reject'])->name('revisions.reject');
            Route::post('revisions/{revision}/apply', [EditorialReviewController::class, 'apply'])->name('revisions.apply');

            Route::get('revisions/{revision}/citations', [EditorialCitationController::class, 'index'])->name('revisions.citations.index');
            Route::post('revisions/{revision}/citations', [EditorialCitationController::class, 'store'])->name('revisions.citations.store');
            Route::delete('citations/{citation}', [EditorialCitationController::class, 'destroy'])->name('citations.destroy');

            Route::get('rights-assessments', [SourceRightsReviewController::class, 'index'])->name('rights-assessments.index');
            Route::post('rights-assessments', [SourceRightsReviewController::class, 'store'])->name('rights-assessments.store');
            Route::get('rights-assessments/{assessment}', [SourceRightsReviewController::class, 'show'])->name('rights-assessments.show');

            Route::get('spoiler-boundaries', [SpoilerBoundaryController::class, 'index'])->name('spoiler-boundaries.index');
            Route::post('spoiler-boundaries', [SpoilerBoundaryController::class, 'store'])->name('spoiler-boundaries.store');
            Route::patch('spoiler-boundaries/{boundary}', [SpoilerBoundaryController::class, 'update'])->name('spoiler-boundaries.update');
        });
    });
});

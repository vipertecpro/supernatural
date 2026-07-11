<?php

use App\Http\Controllers\Api\V1\EpisodeController;
use App\Http\Controllers\Api\V1\FranchiseController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\SeasonController;
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
    });
});

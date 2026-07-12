<?php

use App\Http\Controllers\Auth\SuspensionController;
use App\Http\Controllers\Onboarding\CompleteOnboardingController;
use App\Http\Controllers\Onboarding\IntroductionController;
use App\Http\Controllers\Onboarding\PrivacyDefaultsController;
use App\Http\Controllers\Onboarding\ReviewController;
use App\Http\Controllers\Onboarding\SpoilerPreferencesController;
use App\Http\Controllers\Onboarding\UniverseInterestsController;
use App\Http\Controllers\Onboarding\ViewingOrderController;
use App\Http\Controllers\Onboarding\ViewingProgressController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
    Route::get('open-source', 'openSource')->name('open-source');
    Route::get('accessibility', 'accessibility')->name('accessibility');
    Route::get('content-policy', 'contentPolicy')->name('content-policy');
    Route::get('copyright-and-takedown', 'copyrightAndTakedown')->name('copyright-and-takedown');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('account/suspended', SuspensionController::class)->name('account.suspended');

    Route::middleware(['platform.access', 'onboarding.incomplete'])->prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [IntroductionController::class, 'show'])->name('introduction');
        Route::patch('/', [IntroductionController::class, 'update'])->name('introduction.update');
        Route::get('interests', [UniverseInterestsController::class, 'edit'])->name('interests.edit');
        Route::patch('interests', [UniverseInterestsController::class, 'update'])->name('interests.update');
        Route::get('progress', [ViewingProgressController::class, 'edit'])->name('progress.edit');
        Route::patch('progress', [ViewingProgressController::class, 'update'])->name('progress.update');
        Route::get('spoilers', [SpoilerPreferencesController::class, 'edit'])->name('spoilers.edit');
        Route::patch('spoilers', [SpoilerPreferencesController::class, 'update'])->name('spoilers.update');
        Route::get('viewing-order', [ViewingOrderController::class, 'edit'])->name('viewing-order.edit');
        Route::patch('viewing-order', [ViewingOrderController::class, 'update'])->name('viewing-order.update');
        Route::get('privacy', [PrivacyDefaultsController::class, 'edit'])->name('privacy.edit');
        Route::patch('privacy', [PrivacyDefaultsController::class, 'update'])->name('privacy.update');
        Route::get('review', [ReviewController::class, 'show'])->name('review');
        Route::post('complete', CompleteOnboardingController::class)->name('complete');
    });

    Route::inertia('dashboard', 'dashboard')
        ->middleware(['platform.access', 'onboarding.completed'])
        ->name('dashboard');

    Route::get('moderation', fn () => response()->noContent())
        ->middleware('can:community.moderate')
        ->name('moderation.index');

    Route::get('administration', fn () => response()->noContent())
        ->middleware('can:administration.access')
        ->name('administration.index');
});

require __DIR__.'/settings.php';

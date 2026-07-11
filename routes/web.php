<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('moderation', fn () => response()->noContent())
        ->middleware('can:community.moderate')
        ->name('moderation.index');

    Route::get('administration', fn () => response()->noContent())
        ->middleware('can:administration.access')
        ->name('administration.index');
});

require __DIR__.'/settings.php';

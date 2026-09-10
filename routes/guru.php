<?php

use Illuminate\Support\Facades\Route;

Route::prefix('guru')
    ->name('guru.')
    ->middleware(['auth', 'role:guru'])
    ->group(function (): void {
        Route::view('/dashboard', 'guru.dashboard')->name('dashboard');
    });

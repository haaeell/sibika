<?php

use Illuminate\Support\Facades\Route;

Route::prefix('wali-kelas')
    ->name('wali-kelas.')
    ->middleware(['auth', 'role:wali_kelas'])
    ->group(function (): void {
        Route::view('/dashboard', 'wali-kelas.dashboard')->name('dashboard');
    });

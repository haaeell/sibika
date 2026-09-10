<?php

use Illuminate\Support\Facades\Route;

Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth', 'role:siswa'])
    ->group(function (): void {
        Route::view('/dashboard', 'siswa.dashboard')->name('dashboard');
    });

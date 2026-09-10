<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function (): void {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    });

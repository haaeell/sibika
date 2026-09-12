<?php

use App\Http\Controllers\WaliKelasDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('wali-kelas')
    ->name('wali-kelas.')
    ->middleware(['auth', 'role:wali_kelas'])
    ->group(function (): void {
        Route::get('/dashboard', WaliKelasDashboardController::class)->name('dashboard');
    });

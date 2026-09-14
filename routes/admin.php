<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\LoginSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/login-settings', [LoginSettingController::class, 'edit'])->name('login-settings.edit');
        Route::put('/login-settings', [LoginSettingController::class, 'update'])->name('login-settings.update');
    });

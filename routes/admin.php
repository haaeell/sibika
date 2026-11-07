<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\LoginSettingController;
use App\Http\Controllers\PengasuhanAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/account', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AdminAccountController::class, 'update'])->name('account.update');
        Route::get('/login-settings', [LoginSettingController::class, 'edit'])->name('login-settings.edit');
        Route::put('/login-settings', [LoginSettingController::class, 'update'])->name('login-settings.update');
    });

Route::prefix('admin/pengasuhan-accounts')
    ->name('admin.pengasuhan-accounts.')
    ->middleware(['auth', 'role:bk|super_admin'])
    ->group(function (): void {
        Route::get('/', [PengasuhanAccountController::class, 'index'])->name('index');
        Route::get('/create', [PengasuhanAccountController::class, 'create'])->name('create');
        Route::post('/', [PengasuhanAccountController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [PengasuhanAccountController::class, 'edit'])->name('edit');
        Route::put('/{user}', [PengasuhanAccountController::class, 'update'])->name('update');
        Route::delete('/{user}', [PengasuhanAccountController::class, 'destroy'])->name('destroy');
    });

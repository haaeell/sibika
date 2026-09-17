<?php

use App\Http\Controllers\WaliKelasDashboardController;
use App\Http\Controllers\WaliKelasMonitoringController;
use Illuminate\Support\Facades\Route;

Route::prefix('wali-kelas')
    ->name('wali-kelas.')
    ->middleware(['auth', 'role:wali_kelas'])
    ->group(function (): void {
        Route::get('/dashboard', WaliKelasDashboardController::class)->name('dashboard');
        Route::get('/biodata', [WaliKelasMonitoringController::class, 'biodata'])->name('biodata.index');
        Route::get('/biodata/data', [WaliKelasMonitoringController::class, 'biodataData'])->name('biodata.data');
        Route::get('/biodata/report', [WaliKelasMonitoringController::class, 'biodataReport'])->name('biodata.report');
        Route::get('/biodata/{student}', [WaliKelasMonitoringController::class, 'biodataShow'])->name('biodata.show');
        Route::get('/nilai', [WaliKelasMonitoringController::class, 'scores'])->name('scores.index');
        Route::get('/nilai/data', [WaliKelasMonitoringController::class, 'scoresData'])->name('scores.data');
        Route::get('/nilai/report', [WaliKelasMonitoringController::class, 'scoresReport'])->name('scores.report');
        Route::get('/nilai/{student}', [WaliKelasMonitoringController::class, 'scoresShow'])->name('scores.show');
    });

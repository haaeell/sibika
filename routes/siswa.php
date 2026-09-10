<?php

use App\Http\Controllers\StudentBiodataController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth', 'role:siswa'])
    ->group(function (): void {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/biodata', [StudentBiodataController::class, 'index'])->name('biodata.index');
        Route::put('/biodata', [StudentBiodataController::class, 'update'])->name('biodata.update');
        Route::post('/biodata/photo', [StudentBiodataController::class, 'uploadPhoto'])->name('biodata.photo.store');
        Route::get('/biodata/photo', [StudentBiodataController::class, 'photo'])->name('biodata.photo.show');
        Route::post('/biodata/documents', [StudentBiodataController::class, 'uploadDocument'])->name('biodata.documents.store');
        Route::get('/biodata/documents/{document}/download', [StudentBiodataController::class, 'downloadDocument'])->name('biodata.documents.download');
        Route::delete('/biodata/documents/{document}', [StudentBiodataController::class, 'destroyDocument'])->name('biodata.documents.destroy');
    });

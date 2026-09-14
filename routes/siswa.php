<?php

use App\Http\Controllers\StudentArticleController;
use App\Http\Controllers\StudentBiodataController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentPasswordController;
use App\Http\Controllers\StudentScoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth', 'role:siswa', 'password.changed'])
    ->group(function (): void {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/artikel', [StudentArticleController::class, 'index'])->name('articles.index');
        Route::get('/artikel/{article:slug}', [StudentArticleController::class, 'show'])->name('articles.show');
        Route::put('/password', [StudentPasswordController::class, 'update'])->name('password.update');
        Route::get('/biodata', [StudentBiodataController::class, 'index'])->name('biodata.index');
        Route::put('/biodata', [StudentBiodataController::class, 'update'])->name('biodata.update');
        Route::post('/biodata/photo', [StudentBiodataController::class, 'uploadPhoto'])->name('biodata.photo.store');
        Route::get('/biodata/photo', [StudentBiodataController::class, 'photo'])->name('biodata.photo.show');
        Route::post('/biodata/documents', [StudentBiodataController::class, 'uploadDocument'])->name('biodata.documents.store');
        Route::get('/biodata/documents/{document}/download', [StudentBiodataController::class, 'downloadDocument'])->name('biodata.documents.download');
        Route::delete('/biodata/documents/{document}', [StudentBiodataController::class, 'destroyDocument'])->name('biodata.documents.destroy');
        Route::get('/nilai', [StudentScoreController::class, 'index'])->name('scores.index');
        Route::post('/nilai/save', [StudentScoreController::class, 'save'])->name('scores.save');
        Route::post('/nilai/request-edit', [StudentScoreController::class, 'requestEdit'])->name('scores.request-edit');
    });

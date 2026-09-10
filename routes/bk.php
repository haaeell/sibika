<?php

use App\Http\Controllers\AcademicYearController;
use Illuminate\Support\Facades\Route;

Route::prefix('bk')
    ->name('bk.')
    ->middleware(['auth', 'role:bk|super_admin'])
    ->group(function (): void {
        Route::view('/dashboard', 'bk.dashboard')->name('dashboard');
        Route::get('academic-years/data', [AcademicYearController::class, 'data'])->name('academic-years.data');
        Route::resource('academic-years', AcademicYearController::class)->except('show');
    });

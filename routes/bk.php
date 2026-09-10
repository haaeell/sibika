<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::prefix('bk')
    ->name('bk.')
    ->middleware(['auth', 'role:bk|super_admin'])
    ->group(function (): void {
        Route::view('/dashboard', 'bk.dashboard')->name('dashboard');
        Route::get('academic-years/data', [AcademicYearController::class, 'data'])->name('academic-years.data');
        Route::resource('academic-years', AcademicYearController::class)->except('show');
        Route::get('cohorts/data', [CohortController::class, 'data'])->name('cohorts.data');
        Route::resource('cohorts', CohortController::class)->except('show');
        Route::get('school-classes/data', [SchoolClassController::class, 'data'])->name('school-classes.data');
        Route::resource('school-classes', SchoolClassController::class)->except('show');
        Route::get('teachers/data', [TeacherController::class, 'data'])->name('teachers.data');
        Route::resource('teachers', TeacherController::class)->except('show');
        Route::get('students/data', [StudentController::class, 'data'])->name('students.data');
        Route::resource('students', StudentController::class)->except('show');
    });

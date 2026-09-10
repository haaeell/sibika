<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\ScoreSubjectSettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentBiodataAdminController;
use App\Http\Controllers\StudentScoreAdminController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::prefix('bk')
    ->name('bk.')
    ->middleware(['auth', 'role:bk|super_admin'])
    ->group(function (): void {
        Route::view('/dashboard', 'bk.dashboard')->name('dashboard');
        Route::get('{resource}/export/{format}', [ExportController::class, 'download'])
            ->whereIn('resource', ['academic-years', 'cohorts', 'majors', 'subjects', 'school-classes', 'teachers', 'students', 'biodata'])
            ->whereIn('format', ['xlsx', 'pdf'])
            ->name('exports.download');
        Route::get('academic-years/data', [AcademicYearController::class, 'data'])->name('academic-years.data');
        Route::resource('academic-years', AcademicYearController::class)->except('show');
        Route::get('cohorts/data', [CohortController::class, 'data'])->name('cohorts.data');
        Route::resource('cohorts', CohortController::class)->except('show');
        Route::get('school-classes/data', [SchoolClassController::class, 'data'])->name('school-classes.data');
        Route::resource('school-classes', SchoolClassController::class)->except('show');
        Route::get('teachers/data', [TeacherController::class, 'data'])->name('teachers.data');
        Route::resource('teachers', TeacherController::class)->except('show');
        Route::get('students/data', [StudentController::class, 'data'])->name('students.data');
        Route::get('biodata/data', [StudentBiodataAdminController::class, 'data'])->name('biodata.data');
        Route::get('biodata', [StudentBiodataAdminController::class, 'index'])->name('biodata.index');
        Route::get('students/{student}/biodata', [StudentBiodataAdminController::class, 'show'])->name('students.biodata.show');
        Route::get('students/{student}/biodata/edit', [StudentBiodataAdminController::class, 'edit'])->name('students.biodata.edit');
        Route::put('students/{student}/biodata', [StudentBiodataAdminController::class, 'update'])->name('students.biodata.update');
        Route::resource('students', StudentController::class)->except('show');
        Route::get('majors/data', [MajorController::class, 'data'])->name('majors.data');
        Route::resource('majors', MajorController::class)->except('show');
        Route::get('subjects/data', [SubjectController::class, 'data'])->name('subjects.data');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::get('student-scores/data', [StudentScoreAdminController::class, 'data'])->name('student-scores.data');
        Route::get('student-scores/{student}', [StudentScoreAdminController::class, 'show'])->name('student-scores.show');
        Route::get('student-scores', [StudentScoreAdminController::class, 'index'])->name('student-scores.index');
        Route::resource('score-subject-settings', ScoreSubjectSettingController::class)->except('show');
    });

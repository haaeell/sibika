<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BiodataReportController;
use App\Http\Controllers\BkDashboardController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\ScoreEditApprovalController;
use App\Http\Controllers\ScoreSubjectSettingController;
use App\Http\Controllers\StudentBiodataAdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentScoreAdminController;
use App\Http\Controllers\StudentScoreExportController;
use App\Http\Controllers\StudentScoreReportController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TkaSubjectController;
use App\Http\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;

Route::prefix('bk')
    ->name('bk.')
    ->middleware(['auth', 'role:bk|super_admin'])
    ->group(function (): void {
        Route::get('/dashboard', BkDashboardController::class)->name('dashboard');
        Route::post('articles/upload-image', [ArticleController::class, 'uploadImage'])->name('articles.upload-image');
        Route::get('articles/{article}/preview', [ArticleController::class, 'preview'])->name('articles.preview');
        Route::resource('articles', ArticleController::class)->except('show');
        Route::get('{resource}/export/{format}', [ExportController::class, 'download'])
            ->whereIn('resource', ['academic-years', 'cohorts', 'majors', 'subjects', 'universities', 'school-classes', 'teachers', 'students', 'biodata'])
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
        Route::get('biodata/report', [BiodataReportController::class, 'index'])->name('biodata.report');
        Route::get('biodata', [StudentBiodataAdminController::class, 'index'])->name('biodata.index');
        Route::get('students/{student}/biodata', [StudentBiodataAdminController::class, 'show'])->name('students.biodata.show');
        Route::get('students/{student}/biodata/edit', [StudentBiodataAdminController::class, 'edit'])->name('students.biodata.edit');
        Route::put('students/{student}/biodata', [StudentBiodataAdminController::class, 'update'])->name('students.biodata.update');
        Route::get('students/{student}/biodata/photo', [StudentBiodataAdminController::class, 'photo'])->name('students.biodata.photo.show');
        Route::get('students/{student}/biodata/documents/{document}/download', [StudentBiodataAdminController::class, 'downloadDocument'])->name('students.biodata.documents.download');
        Route::delete('students/{student}/biodata/documents/{document}', [StudentBiodataAdminController::class, 'destroyDocument'])->name('students.biodata.documents.destroy');
        Route::resource('students', StudentController::class)->except('show');
        Route::post('students/{student}/reset-account', [StudentController::class, 'resetAccount'])->name('students.reset-account');
        Route::get('students/import', [StudentImportController::class, 'create'])->name('students.import.create');
        Route::post('students/import/prepare', [StudentImportController::class, 'prepare'])->name('students.import.prepare');
        Route::post('students/import/batch', [StudentImportController::class, 'batch'])->name('students.import.batch');
        Route::get('students/template', [StudentImportController::class, 'template'])->name('students.template');
        Route::get('majors/data', [MajorController::class, 'data'])->name('majors.data');
        Route::resource('majors', MajorController::class)->except('show');
        Route::get('subjects/data', [SubjectController::class, 'data'])->name('subjects.data');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::get('tka-subjects/data', [TkaSubjectController::class, 'data'])->name('tka-subjects.data');
        Route::resource('tka-subjects', TkaSubjectController::class)->except('show');
        Route::get('universities/data', [UniversityController::class, 'data'])->name('universities.data');
        Route::resource('universities', UniversityController::class)->except('show');
        Route::get('student-scores/data', [StudentScoreAdminController::class, 'data'])->name('student-scores.data');
        Route::get('student-scores/export', [StudentScoreExportController::class, 'download'])->name('student-scores.export');
        Route::get('student-scores/report', [StudentScoreReportController::class, 'index'])->name('student-scores.report');
        Route::get('score-edit-requests', [ScoreEditApprovalController::class, 'index'])->name('score-edit-requests.index');
        Route::post('score-edit-requests/{editRequest}/approve', [ScoreEditApprovalController::class, 'approve'])->name('score-edit-requests.approve');
        Route::post('score-edit-requests/{editRequest}/reject', [ScoreEditApprovalController::class, 'reject'])->name('score-edit-requests.reject');
        Route::get('student-scores/{student}', [StudentScoreAdminController::class, 'show'])->name('student-scores.show');
        Route::get('student-scores', [StudentScoreAdminController::class, 'index'])->name('student-scores.index');
        Route::put('score-subject-settings/average-subjects', [ScoreSubjectSettingController::class, 'updateAverageSubjects'])->name('score-subject-settings.average-subjects.update');
        Route::resource('score-subject-settings', ScoreSubjectSettingController::class)->except('show');
    });

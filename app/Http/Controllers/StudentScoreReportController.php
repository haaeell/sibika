<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Services\StudentScoreReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScoreReportController extends Controller
{
    public function __construct(private readonly StudentScoreReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'major_id' => ['nullable', 'integer', 'exists:majors,id'],
            'status' => ['nullable', 'in:active,graduated,inactive'],
            'completeness' => ['nullable', 'in:complete,incomplete'],
        ]);

        return view('bk.student-scores.report', [
            ...$this->reportService->generate($filters),
            'filters' => $filters,
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

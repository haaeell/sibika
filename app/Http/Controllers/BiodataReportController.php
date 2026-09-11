<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Services\BiodataReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BiodataReportController extends Controller
{
    public function __construct(private readonly BiodataReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'],
            'status' => ['nullable', 'in:active,graduated,inactive'],
            'completeness' => ['nullable', 'in:complete,incomplete'],
            'mcu_status' => ['nullable', 'in:belum,proses,sudah'],
        ]);

        return view('bk.biodata.report', [
            ...$this->reportService->generate($filters),
            'filters' => $filters,
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\BiodataReportService;
use App\Services\StudentProgressService;
use App\Services\StudentScoreReportService;
use App\Services\StudentScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class WaliKelasMonitoringController extends Controller
{
    public function __construct(
        private readonly StudentProgressService $progressService,
        private readonly StudentScoreService $scoreService,
        private readonly BiodataReportService $biodataReportService,
        private readonly StudentScoreReportService $scoreReportService,
    ) {}

    public function biodata(Request $request): View
    {
        return view('bk.biodata.index', [
            'schoolClasses' => $this->classes($request),
            'cohorts' => Cohort::whereIn('id', $this->students($request)->pluck('cohort_id')->filter()->unique())->orderByDesc('entry_year')->get(),
            'isMonitoring' => true,
        ]);
    }

    public function biodataData(Request $request): JsonResponse
    {
        $classIds = array_intersect($this->classIds($request), array_map('intval', array_filter((array) $request->input('class_id', [])))) ?: $this->classIds($request);
        $cohortIds = array_filter((array) $request->input('cohort_id', []));

        return DataTables::eloquent($this->students($request)
            ->when($classIds, fn ($query) => $query->whereIn('class_id', $classIds))
            ->when($cohortIds, fn ($query) => $query->whereIn('cohort_id', $cohortIds))
            ->with(['schoolClass', 'cohort', 'profile', 'parents', 'documents'])
            ->orderBy('name'))
            ->addIndexColumn()
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('cohort_name', fn (Student $student) => $student->cohort?->name ?? '-')
            ->addColumn('progress', fn (Student $student) => $this->progressBadge($this->progressService->calculate($student)['percentage']))
            ->addColumn('action', fn (Student $student) => '<a href="'.route('wali-kelas.biodata.show', $student).'" class="btn-icon" aria-label="Lihat biodata '.e($student->name).'"><i class="fa-solid fa-eye"></i></a>')
            ->rawColumns(['progress', 'action'])
            ->toJson();
    }

    public function biodataShow(Request $request, Student $student): View
    {
        $this->ensureStudent($request, $student);

        return app(StudentBiodataAdminController::class)->show($student, true);
    }

    public function biodataReport(Request $request): View
    {
        $filters = $request->validate(['cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'], 'status' => ['nullable', 'in:active,graduated,inactive'], 'completeness' => ['nullable', 'in:complete,incomplete'], 'mcu_status' => ['nullable', 'in:belum,proses,sudah']]);
        $filters['class_ids'] = $this->classIds($request);

        return view('bk.biodata.report', [...$this->biodataReportService->generate($filters), 'filters' => $filters, 'schoolClasses' => $this->classes($request), 'cohorts' => Cohort::whereIn('id', $this->students($request)->pluck('cohort_id')->filter()->unique())->orderByDesc('entry_year')->get(), 'isMonitoring' => true]);
    }

    public function scores(Request $request): View
    {
        return view('bk.student-scores.index', ['academicYears' => AcademicYear::whereIn('id', $this->classes($request)->pluck('academic_year_id')->filter()->unique())->orderByDesc('is_active')->orderByDesc('start_year')->get(), 'schoolClasses' => $this->classes($request), 'majors' => Major::whereIn('id', $this->classes($request)->pluck('major_id')->filter()->unique())->orderBy('name')->get(), 'isMonitoring' => true]);
    }

    public function scoresData(Request $request): JsonResponse
    {
        $filters = $request->only(['academic_year_id', 'major_id', 'status']);
        $filters['class_id'] = array_intersect($this->classIds($request), array_map('intval', array_filter((array) $request->input('class_id', [])))) ?: $this->classIds($request);
        $students = $this->scoreService->filteredStudents($filters)->with(['schoolClass.academicYear', 'schoolClass.major', 'scores.subject'])->orderBy('name')->get();
        $averages = $this->scoreService->averagesForMany($students);

        return DataTables::of($students)->addIndexColumn()
            ->addColumn('student', fn (Student $student) => '<div class="font-bold text-slate-900">'.e($student->name).'</div><div class="text-xs font-semibold text-slate-400">'.e($student->nis).'</div>')
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('major_name', fn (Student $student) => $student->schoolClass?->major?->name ?? '-')
            ->addColumn('overall_average', fn (Student $student) => '<div class="text-sm font-extrabold text-slate-900">'.(is_null($averages[$student->id]['overall'] ?? null) ? '-' : number_format((float) $averages[$student->id]['overall'], 2)).'</div>')
            ->addColumn('semester_1', fn (Student $student) => $this->averageCell($averages[$student->id]['semesters'][1] ?? null))
            ->addColumn('semester_2', fn (Student $student) => $this->averageCell($averages[$student->id]['semesters'][2] ?? null))
            ->addColumn('semester_3', fn (Student $student) => $this->averageCell($averages[$student->id]['semesters'][3] ?? null))
            ->addColumn('semester_4', fn (Student $student) => $this->averageCell($averages[$student->id]['semesters'][4] ?? null))
            ->addColumn('semester_5', fn (Student $student) => $this->averageCell($averages[$student->id]['semesters'][5] ?? null))
            ->addColumn('action', fn (Student $student) => '<a href="'.route('wali-kelas.scores.show', $student).'" class="btn-icon" aria-label="Lihat nilai '.e($student->name).'"><i class="fa-solid fa-eye"></i></a>')
            ->rawColumns(['student', 'overall_average', 'semester_1', 'semester_2', 'semester_3', 'semester_4', 'semester_5', 'action'])->toJson();
    }

    public function scoresShow(Request $request, Student $student): View
    {
        $this->ensureStudent($request, $student);

        return app(StudentScoreAdminController::class)->show($student, true);
    }

    public function scoresReport(Request $request): View
    {
        $filters = $request->validate(['academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'], 'major_id' => ['nullable', 'integer', 'exists:majors,id'], 'status' => ['nullable', 'in:active,graduated,inactive'], 'completeness' => ['nullable', 'in:complete,incomplete']]);
        $filters['class_ids'] = $this->classIds($request);

        return view('bk.student-scores.report', [...$this->scoreReportService->generate($filters, false), 'filters' => $filters, 'academicYears' => AcademicYear::whereIn('id', $this->classes($request)->pluck('academic_year_id')->filter()->unique())->orderByDesc('is_active')->orderByDesc('start_year')->get(), 'schoolClasses' => $this->classes($request), 'majors' => Major::whereIn('id', $this->classes($request)->pluck('major_id')->filter()->unique())->orderBy('name')->get(), 'isMonitoring' => true]);
    }

    private function classes(Request $request)
    {
        return SchoolClass::with('academicYear')->where('homeroom_teacher_id', Teacher::where('user_id', $request->user()->id)->value('id'))->orderBy('name')->get();
    }

    private function classIds(Request $request): array
    {
        return $this->classes($request)->pluck('id')->all();
    }

    private function students(Request $request)
    {
        return Student::query()->whereIn('class_id', $this->classIds($request));
    }

    private function ensureStudent(Request $request, Student $student): void
    {
        abort_unless(in_array($student->class_id, $this->classIds($request), true), 404);
    }

    private function progressBadge(int $percentage): string
    {
        $class = $percentage >= 80 ? 'bg-emerald-50 text-emerald-700' : ($percentage >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$percentage.'%</span>';
    }

    private function averageCell(?float $value): string
    {
        return '<div class="text-sm font-extrabold text-slate-900">'.(is_null($value) ? '-' : number_format($value, 2)).'</div>';
    }

}

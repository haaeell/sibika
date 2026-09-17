<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentScoreAdminController extends Controller
{
    public function __construct(private readonly StudentScoreService $scoreService) {}

    public function index(): View
    {
        return view('bk.student-scores.index', [
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $students = $this->scoreService->filteredStudents($request->only([
            'academic_year_id', 'class_id', 'major_id', 'status',
        ]))->with(['schoolClass.academicYear', 'schoolClass.major', 'scores.subject'])->orderBy('name')->get();

        // Batch: seluruh rata-rata + ranking dihitung in-memory sekali jalan.
        $averages = $this->scoreService->averagesForMany($students);
        $ranks = $this->scoreService->ranksForMany($students, $averages);

        $avgCell = function (Student $student) use ($averages): string {
            $average = $averages[$student->id]['overall'] ?? null;

            return '<div class="text-sm font-extrabold text-slate-900">'.(is_null($average) ? '-' : number_format((float) $average, 2)).'</div>';
        };
        $rankCell = function (Student $student, string $key, string $totalKey) use ($ranks): string {
            $row = $ranks[$student->id];

            return '<div class="text-sm font-extrabold text-slate-900">'.($row[$key] ?? '-').'</div><div class="text-xs font-semibold text-slate-400">dari '.$row[$totalKey].'</div>';
        };
        $semesterCell = function (Student $student, int $semester) use ($averages): string {
            $average = $averages[$student->id]['semesters'][$semester] ?? null;

            return '<div class="text-sm font-extrabold text-slate-900">'.(is_null($average) ? '-' : number_format((float) $average, 2)).'</div>';
        };

        return DataTables::of($students)
            ->addIndexColumn()
            ->addColumn('student', fn (Student $student) => '<div class="font-bold text-slate-900">'.e($student->name).'</div><div class="text-xs font-semibold text-slate-400">'.e($student->nis).'</div>')
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('major_name', fn (Student $student) => $student->schoolClass?->major?->name ?? '-')
            ->addColumn('overall_average', fn (Student $student) => $avgCell($student))
            ->addColumn('class_rank', fn (Student $student) => $rankCell($student, 'class_rank', 'class_total'))
            ->addColumn('major_rank', fn (Student $student) => $rankCell($student, 'major_rank', 'major_total'))
            ->addColumn('cohort_rank', fn (Student $student) => $rankCell($student, 'cohort_rank', 'cohort_total'))
            ->addColumn('semester_1', fn (Student $student) => $semesterCell($student, 1))
            ->addColumn('semester_2', fn (Student $student) => $semesterCell($student, 2))
            ->addColumn('semester_3', fn (Student $student) => $semesterCell($student, 3))
            ->addColumn('semester_4', fn (Student $student) => $semesterCell($student, 4))
            ->addColumn('semester_5', fn (Student $student) => $semesterCell($student, 5))
            ->addColumn('action', fn (Student $student) => '<a href="'.route('bk.student-scores.show', $student).'" class="btn-icon has-tooltip" data-tooltip="Detail" aria-label="Detail"><i class="fa-solid fa-eye"></i></a>')
            ->rawColumns(['student', 'overall_average', 'class_rank', 'major_rank', 'cohort_rank', 'semester_1', 'semester_2', 'semester_3', 'semester_4', 'semester_5', 'action'])
            ->toJson();
    }

    public function show(Student $student, bool $isMonitoring = false): View
    {
        $student->load(['schoolClass.major', 'schoolClass.academicYear', 'scores.subject']);

        return view('bk.student-scores.show', [
            'student' => $student,
            'summary' => $this->scoreService->overallSummary($student),
            'averages' => collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
                $semester => $this->scoreService->semesterAverage($student, $semester),
            ]),
            'semesters' => collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
                $semester => [
                    'settings' => $this->scoreService->subjectsFor($student, $semester),
                    'scores' => $this->scoreService->scoresFor($student, $semester),
                    'included' => $this->scoreService->includedMapFor($student, $semester),
                ],
            ]),
            'isMonitoring' => $isMonitoring,
        ]);
    }
}

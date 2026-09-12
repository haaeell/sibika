<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentScore;
use App\Services\StudentScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentScoreAdminController extends Controller
{
    public function __construct(private readonly StudentScoreService $scoreService)
    {
    }

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
        return DataTables::eloquent($this->scoreService->filteredStudents($request->only([
            'academic_year_id', 'class_id', 'major_id', 'status',
        ]))->with(['schoolClass.academicYear', 'schoolClass.major', 'scores.subject'])->latest())
            ->addIndexColumn()
            ->addColumn('student', fn (Student $student) => '<div class="font-bold text-slate-900">'.e($student->name).'</div><div class="text-xs font-semibold text-slate-400">'.e($student->nis).'</div>')
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('major_name', fn (Student $student) => $student->schoolClass?->major?->name ?? '-')
            ->addColumn('overall_average', function (Student $student) {
                $average = $this->scoreService->overallSummary($student)['average'];

                return '<div class="text-sm font-extrabold text-slate-900">'.(is_null($average) ? '-' : number_format((float) $average, 2)).'</div>';
            })
            ->addColumn('class_rank', function (Student $student) {
                $summary = $this->scoreService->overallSummary($student);

                return '<div class="text-sm font-extrabold text-slate-900">'.($summary['class_rank'] ?? '-').'</div><div class="text-xs font-semibold text-slate-400">dari '.$summary['class_total'].'</div>';
            })
            ->addColumn('major_rank', function (Student $student) {
                $summary = $this->scoreService->overallSummary($student);

                return '<div class="text-sm font-extrabold text-slate-900">'.($summary['major_rank'] ?? '-').'</div><div class="text-xs font-semibold text-slate-400">dari '.$summary['major_total'].'</div>';
            })
            ->addColumn('semester_1', fn (Student $student) => $this->semesterCard($student, 1))
            ->addColumn('semester_2', fn (Student $student) => $this->semesterCard($student, 2))
            ->addColumn('semester_3', fn (Student $student) => $this->semesterCard($student, 3))
            ->addColumn('semester_4', fn (Student $student) => $this->semesterCard($student, 4))
            ->addColumn('semester_5', fn (Student $student) => $this->semesterCard($student, 5))
            ->addColumn('action', fn (Student $student) => '<a href="'.route('bk.student-scores.show', $student).'" class="btn-icon has-tooltip" data-tooltip="Detail" aria-label="Detail"><i class="fa-solid fa-eye"></i></a>')
            ->rawColumns(['student', 'overall_average', 'class_rank', 'major_rank', 'semester_1', 'semester_2', 'semester_3', 'semester_4', 'semester_5', 'action'])
            ->toJson();
    }

    public function show(Student $student): View
    {
        $student->load(['schoolClass.major', 'schoolClass.academicYear']);

        return view('bk.student-scores.show', [
            'student' => $student,
            'summary' => $this->scoreService->overallSummary($student),
            'semesters' => collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
                $semester => [
                    'settings' => $this->scoreService->subjectsFor($student, $semester),
                    'scores' => $this->scoreService->scoresFor($student, $semester),
                ],
            ]),
        ]);
    }

    private function semesterCard(Student $student, int $semester): string
    {
        $scores = $student->scores->where('semester_number', $semester);
        $averageSubjectIds = $this->scoreService->averageSubjectsFor($student, $semester)->pluck('subject_id');
        $average = $scores->whereIn('subject_id', $averageSubjectIds)
            ->filter(fn (StudentScore $score) => filled($score->score))
            ->avg('score');

        return '<div class="text-sm font-extrabold text-slate-900">'.($average ? number_format((float) $average, 2) : '-').'</div>';
    }
}

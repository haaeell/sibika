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
            ->addColumn('overall_average', fn (Student $student) => $this->summaryCard($this->scoreService->overallSummary($student)))
            ->addColumn('semester_1', fn (Student $student) => $this->semesterCard($student, 1))
            ->addColumn('semester_2', fn (Student $student) => $this->semesterCard($student, 2))
            ->addColumn('semester_3', fn (Student $student) => $this->semesterCard($student, 3))
            ->addColumn('semester_4', fn (Student $student) => $this->semesterCard($student, 4))
            ->addColumn('semester_5', fn (Student $student) => $this->semesterCard($student, 5))
            ->addColumn('action', fn (Student $student) => '<a href="'.route('bk.student-scores.show', $student).'" class="btn-icon has-tooltip" data-tooltip="Detail" aria-label="Detail"><i class="fa-solid fa-eye"></i></a>')
            ->rawColumns(['student', 'overall_average', 'semester_1', 'semester_2', 'semester_3', 'semester_4', 'semester_5', 'action'])
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
        $averageScores = $scores->whereIn('subject_id', $averageSubjectIds);
        $total = $averageSubjectIds->count();
        $filled = $averageScores->filter(fn (StudentScore $score) => filled($score->score))->count();
        $average = $averageScores->filter(fn (StudentScore $score) => filled($score->score))->avg('score');
        [$label, $class] = $this->semesterStatus($total, $filled);

        return '<div class="min-w-32 rounded-2xl bg-slate-50 px-3 py-2">'
            .'<div class="text-sm font-extrabold text-slate-900">'.($average ? number_format((float) $average, 2) : '-').'</div>'
            .'<div class="mt-0.5 text-xs font-semibold text-slate-500">'.$filled.'/'.$total.' mapel</div>'
            .'<span class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-extrabold '.$class.'">'.$label.'</span>'
            .'</div>';
    }

    private function summaryCard(array $summary): string
    {
        return '<div class="min-w-36 rounded-2xl bg-blue-50 px-3 py-2">'
            .'<div class="text-sm font-extrabold text-blue-950">'.(is_null($summary['average']) ? '-' : number_format((float) $summary['average'], 2)).'</div>'
            .'<div class="mt-0.5 text-xs font-semibold text-blue-700">Kelas: '.($summary['class_rank'] ?? '-').'/'.$summary['class_total'].'</div>'
            .'<div class="mt-0.5 text-xs font-semibold text-blue-700">Jurusan: '.($summary['major_rank'] ?? '-').'/'.$summary['major_total'].'</div>'
            .'</div>';
    }

    private function semesterStatus(int $total, int $filled): array
    {
        if ($filled === 0) {
            return ['Belum Diisi', 'bg-slate-100 text-slate-600'];
        }
        if ($total > 0 && $filled >= $total) {
            return ['Lengkap', 'bg-emerald-50 text-emerald-700'];
        }

        return ['Sebagian', 'bg-blue-50 text-blue-700'];
    }
}

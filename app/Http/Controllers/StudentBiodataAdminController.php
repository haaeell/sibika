<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnBiodataRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentBiodataAdminController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function index(): View
    {
        return view('bk.biodata.index', [
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $classIds = array_filter((array) $request->input('class_id', []));
        $cohortIds = array_filter((array) $request->input('cohort_id', []));

        return DataTables::eloquent(Student::query()
            ->when($classIds, fn ($query) => $query->whereIn('class_id', array_map('intval', $classIds)))
            ->when($cohortIds, fn ($query) => $query->whereIn('cohort_id', array_map('intval', $cohortIds)))
            ->with(['schoolClass', 'cohort', 'profile', 'parents', 'documents'])
            ->orderBy('name'))
            ->addIndexColumn()
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('cohort_name', fn (Student $student) => $student->cohort?->name ?? '-')
            ->addColumn('progress', fn (Student $student) => $this->progressBadge($this->progressService->calculate($student)['percentage']))
            ->addColumn('action', fn (Student $student) => view('bk.biodata._actions', compact('student'))->render())
            ->rawColumns(['progress', 'action'])
            ->toJson();
    }

    public function show(Student $student): View
    {
        $student->load(['profile', 'parents', 'documents', 'schoolClass.academicYear', 'cohort']);

        return view('bk.students.biodata', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
        ]);
    }

    public function edit(Student $student): View
    {
        $student->load(['profile', 'parents', 'documents', 'schoolClass.academicYear', 'cohort', 'user']);

        return view('siswa.biodata.index', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
            'isAdmin' => true,
            'biodataUpdateRoute' => route('bk.students.biodata.update', $student),
            'biodataBackRoute' => route('bk.students.biodata.show', $student),
        ]);
    }

    public function update(UpdateOwnBiodataRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();
        $profileData = collect($data)->except(['father', 'mother', 'guardian'])->all();

        DB::transaction(function () use ($student, $profileData, $data): void {
            $student->profile()->updateOrCreate([], $profileData);

            foreach (['father', 'mother', 'guardian'] as $type) {
                $parent = $data[$type] ?? [];
                $student->parents()->updateOrCreate(
                    ['parent_type' => $type],
                    [
                        'name' => $parent['name'] ?? null,
                        'phone' => $parent['phone'] ?? null,
                        'occupation' => $parent['occupation'] ?? null,
                        'education' => $parent['education'] ?? null,
                        'income_range' => $parent['income_range'] ?? null,
                        'relation' => $parent['relation'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('bk.students.biodata.show', $student)->with('success', 'Biodata siswa berhasil diperbarui.');
    }

    private function progressBadge(int $percentage): string
    {
        $class = $percentage >= 80 ? 'bg-emerald-50 text-emerald-700' : ($percentage >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$percentage.'%</span>';
    }
}

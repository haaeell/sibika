<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('bk.students.index', [
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return DataTables::eloquent(Student::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('cohort_id'), fn ($query) => $query->where('cohort_id', $request->integer('cohort_id')))
            ->with(['schoolClass', 'cohort'])
            ->latest())
            ->addIndexColumn()
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('cohort_name', fn (Student $student) => $student->cohort?->name ?? '-')
            ->editColumn('status', fn (Student $student) => $this->statusBadge($student->status))
            ->addColumn('action', fn (Student $student) => view('bk.students._actions', compact('student'))->render())
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.students.create', [
            'student' => new Student(),
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('bk.students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('bk.students.edit', [
            'student' => $student,
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('bk.students.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('bk.students.index')->with('success', 'Siswa berhasil dihapus.');
    }

    private function statusBadge(string $status): string
    {
        $classes = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'graduated' => 'bg-blue-50 text-blue-700',
            'inactive' => 'bg-slate-100 text-slate-600',
        ];
        $labels = ['active' => 'Aktif', 'graduated' => 'Lulus', 'inactive' => 'Nonaktif'];

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$classes[$status].'">'.$labels[$status].'</span>';
    }
}

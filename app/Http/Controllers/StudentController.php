<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $statuses = array_filter((array) $request->input('status', []));
        $classIds = array_filter((array) $request->input('class_id', []));
        $cohortIds = array_filter((array) $request->input('cohort_id', []));

        return DataTables::eloquent(Student::query()
            ->when($statuses, fn ($query) => $query->whereIn('status', $statuses))
            ->when($classIds, fn ($query) => $query->whereIn('class_id', array_map('intval', $classIds)))
            ->when($cohortIds, fn ($query) => $query->whereIn('cohort_id', array_map('intval', $cohortIds)))
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
            'student' => new Student,
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request, StudentAccountService $accounts): RedirectResponse
    {
        $student = DB::transaction(function () use ($request, $accounts) {
            $student = Student::create($request->validated());
            $student->profile()->firstOrCreate([]);
            $accounts->ensureAccount($student);

            return $student;
        });

        return redirect()->route('bk.students.index')->with(
            'success',
            "Siswa {$student->name} berhasil ditambahkan. Akun login dibuat otomatis (email: ".StudentAccountService::emailFor($student->nisn, $student->nis).', password awal: NIS).'
        );
    }

    public function edit(Student $student): View
    {
        return view('bk.students.edit', [
            'student' => $student,
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student, StudentAccountService $accounts): RedirectResponse
    {
        DB::transaction(function () use ($request, $student, $accounts) {
            $student->update($request->validated());
            $accounts->ensureAccount($student);
        });

        return redirect()->route('bk.students.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function resetAccount(Student $student, StudentAccountService $accounts): RedirectResponse
    {
        $accounts->resetToDefault($student);

        return back()->with('success', "Akun {$student->name} direset. Password awal kembali ke NIS dan wajib diganti saat login.");
    }

    public function destroy(Student $student): RedirectResponse
    {
        $user = $student->user;
        $student->delete();
        $user?->delete();

        return redirect()->route('bk.students.index')->with('success', 'Siswa beserta akun loginnya berhasil dihapus.');
    }

    public function destroyAll(): RedirectResponse
    {
        $userIds = Student::query()->whereNotNull('user_id')->pluck('user_id');
        $count = Student::query()->count();

        DB::transaction(function () use ($userIds) {
            Student::query()->delete();
            User::query()->whereKey($userIds)->delete();
        });

        return redirect()->route('bk.students.index')->with(
            'success',
            $count ? "{$count} siswa beserta akun loginnya berhasil dihapus." : 'Tidak ada data siswa untuk dihapus.'
        );
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

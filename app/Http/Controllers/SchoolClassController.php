<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\TeacherAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        return view('bk.school-classes.index', [
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $academicYearIds = array_filter((array) $request->input('academic_year_id', []));
        $majorIds = array_filter((array) $request->input('major_id', []));
        $gradeLevels = array_filter((array) $request->input('grade_level', []));

        return DataTables::eloquent(SchoolClass::query()
            ->when($academicYearIds, fn ($query) => $query->whereIn('academic_year_id', array_map('intval', $academicYearIds)))
            ->when($majorIds, fn ($query) => $query->whereIn('major_id', array_map('intval', $majorIds)))
            ->when($gradeLevels, fn ($query) => $query->whereIn('grade_level', $gradeLevels))
            ->with(['academicYear', 'major', 'homeroomTeacher'])
            ->latest())
            ->addIndexColumn()
            ->addColumn('academic_year', fn (SchoolClass $schoolClass) => $schoolClass->academicYear?->name ?? '-')
            ->addColumn('major_name', fn (SchoolClass $schoolClass) => $schoolClass->major?->name ?? '-')
            ->addColumn('homeroom_teacher', fn (SchoolClass $schoolClass) => $schoolClass->homeroomTeacher?->name ?? '-')
            ->addColumn('action', fn (SchoolClass $schoolClass) => view('bk.school-classes._actions', compact('schoolClass'))->render())
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.school-classes.create', [
            'schoolClass' => new SchoolClass,
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSchoolClassRequest $request, TeacherAccountService $accounts): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validated());
        if ($schoolClass->homeroomTeacher) {
            $accounts->ensureAccount($schoolClass->homeroomTeacher);
        }

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('bk.school-classes.edit', [
            'schoolClass' => $schoolClass,
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass, TeacherAccountService $accounts): RedirectResponse
    {
        $previousTeacher = $schoolClass->homeroomTeacher;
        $schoolClass->update($request->validated());
        foreach (collect([$previousTeacher, $schoolClass->homeroomTeacher])->filter()->unique('id') as $teacher) {
            $accounts->ensureAccount($teacher);
        }

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass, TeacherAccountService $accounts): RedirectResponse
    {
        if ($schoolClass->students()->exists()) {
            return back()->with('error', 'Kelas yang masih memiliki siswa tidak dapat dihapus.');
        }

        $teacher = $schoolClass->homeroomTeacher;
        $schoolClass->delete();
        if ($teacher) {
            $accounts->ensureAccount($teacher);
        }

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}

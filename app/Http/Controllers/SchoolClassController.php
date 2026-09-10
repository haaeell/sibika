<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        return view('bk.school-classes.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(SchoolClass::query()->with(['academicYear', 'homeroomTeacher'])->latest())
            ->addIndexColumn()
            ->addColumn('academic_year', fn (SchoolClass $schoolClass) => $schoolClass->academicYear?->name ?? '-')
            ->addColumn('homeroom_teacher', fn (SchoolClass $schoolClass) => $schoolClass->homeroomTeacher?->name ?? '-')
            ->addColumn('action', fn (SchoolClass $schoolClass) => view('bk.school-classes._actions', compact('schoolClass'))->render())
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.school-classes.create', [
            'schoolClass' => new SchoolClass(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::create($request->validated());

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('bk.school-classes.edit', [
            'schoolClass' => $schoolClass,
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->students()->exists()) {
            return back()->with('error', 'Kelas yang masih memiliki siswa tidak dapat dihapus.');
        }

        $schoolClass->delete();

        return redirect()->route('bk.school-classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}

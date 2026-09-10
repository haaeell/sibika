<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function index(): View
    {
        return view('bk.teachers.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(Teacher::query()->withCount('homeroomClasses')->latest())
            ->addIndexColumn()
            ->editColumn('status', fn (Teacher $teacher) => $this->statusBadge($teacher->status))
            ->addColumn('classes', fn (Teacher $teacher) => $teacher->homeroom_classes_count)
            ->addColumn('action', fn (Teacher $teacher) => view('bk.teachers._actions', compact('teacher'))->render())
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.teachers.create', ['teacher' => new Teacher()]);
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        Teacher::create($request->validated());

        return redirect()->route('bk.teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher): View
    {
        return view('bk.teachers.edit', compact('teacher'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update($request->validated());

        return redirect()->route('bk.teachers.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        if ($teacher->homeroomClasses()->exists()) {
            return back()->with('error', 'Guru yang masih menjadi wali kelas tidak dapat dihapus.');
        }

        $teacher->delete();

        return redirect()->route('bk.teachers.index')->with('success', 'Guru berhasil dihapus.');
    }

    private function statusBadge(string $status): string
    {
        $class = $status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        $label = $status === 'active' ? 'Aktif' : 'Nonaktif';

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$label.'</span>';
    }
}

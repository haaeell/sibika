<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\TeacherAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function index(): View
    {
        return view('bk.teachers.index', [
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $statuses = array_filter((array) $request->input('status', []));
        $subjectIds = array_filter((array) $request->input('subject_id', []));

        return DataTables::eloquent(Teacher::query()
            ->when($statuses, fn ($query) => $query->whereIn('status', $statuses))
            ->when($subjectIds, fn ($query) => $query->whereHas('subjects', fn ($subjectQuery) => $subjectQuery->whereIn('subjects.id', array_map('intval', $subjectIds))))
            ->with('subjects')
            ->withCount('homeroomClasses')
            ->latest())
            ->addIndexColumn()
            ->editColumn('status', fn (Teacher $teacher) => $this->statusBadge($teacher->status))
            ->addColumn('classes', fn (Teacher $teacher) => $teacher->homeroom_classes_count)
            ->addColumn('subjects_list', fn (Teacher $teacher) => $teacher->subjects->pluck('name')->join(', ') ?: '-')
            ->addColumn('action', fn (Teacher $teacher) => view('bk.teachers._actions', compact('teacher'))->render())
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.teachers.create', [
            'teacher' => new Teacher,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTeacherRequest $request, TeacherAccountService $accounts): RedirectResponse
    {
        $data = $request->validated();
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);
        $teacher = Teacher::create($data);
        $teacher->subjects()->sync($subjectIds);
        $accounts->ensureAccount($teacher);

        return redirect()->route('bk.teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher): View
    {
        return view('bk.teachers.edit', [
            'teacher' => $teacher->load('subjects'),
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher, TeacherAccountService $accounts): RedirectResponse
    {
        $data = $request->validated();
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);
        $teacher->update($data);
        $teacher->subjects()->sync($subjectIds);
        $accounts->ensureAccount($teacher);

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

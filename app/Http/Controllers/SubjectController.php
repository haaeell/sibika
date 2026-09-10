<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    public function index(): View { return view('bk.subjects.index'); }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(Subject::query()->withCount('teachers')->latest())
            ->addIndexColumn()
            ->editColumn('category', fn (Subject $subject) => $this->categoryLabel($subject->category))
            ->editColumn('is_active', fn (Subject $subject) => $this->statusBadge($subject->is_active))
            ->addColumn('teachers', fn (Subject $subject) => $subject->teachers_count)
            ->addColumn('action', fn (Subject $subject) => view('bk.subjects._actions', compact('subject'))->render())
            ->rawColumns(['category', 'is_active', 'action'])
            ->toJson();
    }

    public function create(): View { return view('bk.subjects.create', ['subject' => new Subject(['is_active' => true])]); }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        Subject::create($data);

        return redirect()->route('bk.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View { return view('bk.subjects.edit', compact('subject')); }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $subject->update($data);

        return redirect()->route('bk.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->teachers()->exists()) {
            return back()->with('error', 'Mata pelajaran yang masih diampu guru tidak dapat dihapus.');
        }

        $subject->delete();

        return redirect()->route('bk.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    private function statusBadge(bool $active): string
    {
        $class = $active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        $label = $active ? 'Aktif' : 'Nonaktif';

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$label.'</span>';
    }

    private function categoryLabel(string $category): string
    {
        $labels = ['general' => 'Umum', 'tka_mandatory' => 'TKA Wajib', 'tka_optional' => 'TKA Pilihan'];

        return '<span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">'.$labels[$category].'</span>';
    }
}

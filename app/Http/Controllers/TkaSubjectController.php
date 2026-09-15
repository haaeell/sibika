<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTkaSubjectRequest;
use App\Http\Requests\UpdateTkaSubjectRequest;
use App\Models\TkaSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TkaSubjectController extends Controller
{
    public function index(): View
    {
        return view('bk.tka-subjects.index');
    }

    public function data(Request $request): JsonResponse
    {
        $activeValues = array_filter((array) $request->input('is_active', []), fn ($value) => $value !== '');

        return DataTables::eloquent(TkaSubject::query()
            ->when($activeValues, fn ($query) => $query->whereIn('is_active', array_map('intval', $activeValues)))
            ->withCount('studentSelections')
            ->latest())
            ->addIndexColumn()
            ->addColumn('students', fn (TkaSubject $subject) => $subject->student_selections_count)
            ->editColumn('is_active', fn (TkaSubject $subject) => $this->statusBadge($subject->is_active))
            ->addColumn('action', fn (TkaSubject $subject) => view('bk.tka-subjects._actions', ['tkaSubject' => $subject])->render())
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.tka-subjects.create', ['tkaSubject' => new TkaSubject(['is_active' => true])]);
    }

    public function store(StoreTkaSubjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        TkaSubject::create($data);

        return redirect()->route('bk.tka-subjects.index')->with('success', 'Mapel TKA berhasil ditambahkan.');
    }

    public function edit(TkaSubject $tkaSubject): View
    {
        return view('bk.tka-subjects.edit', compact('tkaSubject'));
    }

    public function update(UpdateTkaSubjectRequest $request, TkaSubject $tkaSubject): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $tkaSubject->update($data);

        return redirect()->route('bk.tka-subjects.index')->with('success', 'Mapel TKA berhasil diperbarui.');
    }

    public function destroy(TkaSubject $tkaSubject): RedirectResponse
    {
        if ($tkaSubject->studentSelections()->exists()) {
            return back()->with('error', 'Mapel TKA yang sudah dipilih siswa tidak dapat dihapus. Nonaktifkan mapel jika tidak ingin menampilkannya.');
        }

        $tkaSubject->delete();

        return redirect()->route('bk.tka-subjects.index')->with('success', 'Mapel TKA berhasil dihapus.');
    }

    private function statusBadge(bool $active): string
    {
        $class = $active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        $label = $active ? 'Aktif' : 'Nonaktif';

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$label.'</span>';
    }
}

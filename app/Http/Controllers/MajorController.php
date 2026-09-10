<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMajorRequest;
use App\Http\Requests\UpdateMajorRequest;
use App\Models\Major;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MajorController extends Controller
{
    public function index(): View { return view('bk.majors.index'); }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(Major::query()->withCount('classes')->latest())
            ->addIndexColumn()
            ->editColumn('is_active', fn (Major $major) => $this->statusBadge($major->is_active))
            ->addColumn('classes', fn (Major $major) => $major->classes_count)
            ->addColumn('action', fn (Major $major) => view('bk.majors._actions', compact('major'))->render())
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    public function create(): View { return view('bk.majors.create', ['major' => new Major(['is_active' => true])]); }

    public function store(StoreMajorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        Major::create($data);

        return redirect()->route('bk.majors.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Major $major): View { return view('bk.majors.edit', compact('major')); }

    public function update(UpdateMajorRequest $request, Major $major): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $major->update($data);

        return redirect()->route('bk.majors.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major): RedirectResponse
    {
        if ($major->classes()->exists()) {
            return back()->with('error', 'Jurusan yang masih dipakai kelas tidak dapat dihapus.');
        }

        $major->delete();

        return redirect()->route('bk.majors.index')->with('success', 'Jurusan berhasil dihapus.');
    }

    private function statusBadge(bool $active): string
    {
        $class = $active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        $label = $active ? 'Aktif' : 'Nonaktif';

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$label.'</span>';
    }
}

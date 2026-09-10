<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCohortRequest;
use App\Http\Requests\UpdateCohortRequest;
use App\Models\Cohort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CohortController extends Controller
{
    public function index(): View
    {
        return view('bk.cohorts.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(Cohort::query()->latest())
            ->addIndexColumn()
            ->editColumn('status', fn (Cohort $cohort) => $this->statusBadge($cohort->status))
            ->addColumn('period', fn (Cohort $cohort) => $cohort->entry_year.' - '.$cohort->graduation_year)
            ->addColumn('action', fn (Cohort $cohort) => view('bk.cohorts._actions', compact('cohort'))->render())
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.cohorts.create', ['cohort' => new Cohort()]);
    }

    public function store(StoreCohortRequest $request): RedirectResponse
    {
        Cohort::create($request->validated());

        return redirect()->route('bk.cohorts.index')->with('success', 'Angkatan berhasil ditambahkan.');
    }

    public function edit(Cohort $cohort): View
    {
        return view('bk.cohorts.edit', compact('cohort'));
    }

    public function update(UpdateCohortRequest $request, Cohort $cohort): RedirectResponse
    {
        $cohort->update($request->validated());

        return redirect()->route('bk.cohorts.index')->with('success', 'Angkatan berhasil diperbarui.');
    }

    public function destroy(Cohort $cohort): RedirectResponse
    {
        if ($cohort->students()->exists()) {
            return back()->with('error', 'Angkatan yang masih memiliki siswa tidak dapat dihapus.');
        }

        $cohort->delete();

        return redirect()->route('bk.cohorts.index')->with('success', 'Angkatan berhasil dihapus.');
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

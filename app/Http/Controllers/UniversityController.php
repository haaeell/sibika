<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UniversityController extends Controller
{
    public function index(): View
    {
        return view('bk.universities.index');
    }

    public function data(Request $request): JsonResponse
    {
        $types = array_filter((array) $request->input('type', []));
        $activeValues = array_filter((array) $request->input('is_active', []), fn ($value) => $value !== '');

        return DataTables::eloquent(University::query()
            ->when($types, fn ($query) => $query->whereIn('type', $types))
            ->when($activeValues, fn ($query) => $query->whereIn('is_active', array_map('intval', $activeValues)))
            ->withCount(['firstChoiceProfiles', 'secondChoiceProfiles'])
            ->latest())
            ->addIndexColumn()
            ->editColumn('type', fn (University $university) => $this->typeBadge($university->type))
            ->editColumn('is_active', fn (University $university) => $this->statusBadge($university->is_active))
            ->addColumn('students', fn (University $university) => $university->first_choice_profiles_count + $university->second_choice_profiles_count)
            ->addColumn('action', fn (University $university) => view('bk.universities._actions', compact('university'))->render())
            ->rawColumns(['type', 'is_active', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.universities.create', ['university' => new University(['is_active' => true])]);
    }

    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        University::create($data);

        return redirect()->route('bk.universities.index')->with('success', 'Kampus berhasil ditambahkan.');
    }

    public function edit(University $university): View
    {
        return view('bk.universities.edit', compact('university'));
    }

    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $university->update($data);

        return redirect()->route('bk.universities.index')->with('success', 'Kampus berhasil diperbarui.');
    }

    public function destroy(University $university): RedirectResponse
    {
        if ($university->firstChoiceProfiles()->exists() || $university->secondChoiceProfiles()->exists()) {
            return back()->with('error', 'Kampus yang masih dipilih siswa tidak dapat dihapus. Nonaktifkan kampus jika tidak ingin menampilkannya.');
        }

        $university->delete();

        return redirect()->route('bk.universities.index')->with('success', 'Kampus berhasil dihapus.');
    }

    private function typeBadge(string $type): string
    {
        $labels = ['negeri' => 'Negeri', 'swasta' => 'Swasta', 'kedinasan' => 'Kedinasan', 'lainnya' => 'Lainnya'];
        $classes = ['negeri' => 'bg-blue-50 text-blue-700', 'swasta' => 'bg-violet-50 text-violet-700', 'kedinasan' => 'bg-amber-50 text-amber-700', 'lainnya' => 'bg-slate-100 text-slate-600'];

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.($classes[$type] ?? $classes['lainnya']).'">'.($labels[$type] ?? 'Lainnya').'</span>';
    }

    private function statusBadge(bool $active): string
    {
        $class = $active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        $label = $active ? 'Aktif' : 'Nonaktif';

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$label.'</span>';
    }
}

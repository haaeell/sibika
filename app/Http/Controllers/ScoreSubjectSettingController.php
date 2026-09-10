<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoreSubjectSettingRequest;
use App\Http\Requests\UpdateScoreSubjectSettingRequest;
use App\Models\Major;
use App\Models\ScoreSubjectSetting;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ScoreSubjectSettingController extends Controller
{
    public function index(): View
    {
        return view('bk.score-subject-settings.index', [
            'majors' => Major::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $semesters = array_filter((array) $request->input('semester_number', []));
        $majorIds = array_filter((array) $request->input('major_id', []), fn ($value) => $value !== '');
        $scopes = array_filter((array) $request->input('scope', []));
        $activeValues = array_filter((array) $request->input('is_active', []), fn ($value) => $value !== '');

        return DataTables::eloquent(ScoreSubjectSetting::query()
            ->with(['subject', 'major'])
            ->when($semesters, fn ($query) => $query->whereIn('semester_number', array_map('intval', $semesters)))
            ->when($majorIds, fn ($query) => $query->whereIn('major_id', array_map('intval', $majorIds)))
            ->when($scopes, function ($query) use ($scopes) {
                if (in_array('general', $scopes, true) && ! in_array('major', $scopes, true)) {
                    $query->whereNull('major_id');
                }
                if (in_array('major', $scopes, true) && ! in_array('general', $scopes, true)) {
                    $query->whereNotNull('major_id');
                }
            })
            ->when($activeValues, fn ($query) => $query->whereIn('is_active', array_map('intval', $activeValues)))
            ->orderBy('semester_number')
            ->orderBy('major_id')
            ->latest())
            ->addIndexColumn()
            ->addColumn('semester_badge', fn (ScoreSubjectSetting $setting) => '<span class="inline-flex size-9 items-center justify-center rounded-xl bg-blue-50 text-sm font-extrabold text-blue-900">'.$setting->semester_number.'</span>')
            ->addColumn('subject_name', fn (ScoreSubjectSetting $setting) => '<div class="font-bold text-slate-900">'.e($setting->subject?->name).'</div><div class="text-xs font-semibold text-slate-400">'.e($setting->subject?->code).'</div>')
            ->addColumn('major_name', fn (ScoreSubjectSetting $setting) => $setting->major ? '<span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">'.e($setting->major->name).'</span>' : '<span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">Umum</span>')
            ->editColumn('is_required', fn (ScoreSubjectSetting $setting) => $this->badge($setting->is_required, 'Wajib', 'Pilihan'))
            ->editColumn('is_active', fn (ScoreSubjectSetting $setting) => $this->badge($setting->is_active, 'Aktif', 'Nonaktif'))
            ->addColumn('action', fn (ScoreSubjectSetting $setting) => view('bk.score-subject-settings._actions', compact('setting'))->render())
            ->rawColumns(['semester_badge', 'subject_name', 'major_name', 'is_required', 'is_active', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        return view('bk.score-subject-settings.create', $this->formData(new ScoreSubjectSetting(['is_required' => true, 'is_active' => true])));
    }

    public function store(StoreScoreSubjectSettingRequest $request): RedirectResponse
    {
        $data = $this->payload($request);

        DB::transaction(function () use ($data, $request): void {
            foreach ($request->input('subject_ids', []) as $subjectId) {
                ScoreSubjectSetting::updateOrCreate([
                    'subject_id' => $subjectId,
                    'major_id' => $data['major_id'],
                    'semester_number' => $data['semester_number'],
                ], [
                    'is_required' => $data['is_required'],
                    'is_active' => $data['is_active'],
                ]);
            }
        });

        return redirect()->route('bk.score-subject-settings.index')->with('success', 'Setting nilai berhasil disimpan.');
    }

    public function edit(ScoreSubjectSetting $scoreSubjectSetting): View
    {
        return view('bk.score-subject-settings.edit', $this->formData($scoreSubjectSetting));
    }

    public function update(UpdateScoreSubjectSettingRequest $request, ScoreSubjectSetting $scoreSubjectSetting): RedirectResponse
    {
        $scoreSubjectSetting->update($this->payload($request));

        return redirect()->route('bk.score-subject-settings.index')->with('success', 'Setting nilai berhasil diperbarui.');
    }

    public function destroy(ScoreSubjectSetting $scoreSubjectSetting): RedirectResponse
    {
        $scoreSubjectSetting->delete();

        return redirect()->route('bk.score-subject-settings.index')->with('success', 'Setting nilai berhasil dihapus.');
    }

    private function formData(ScoreSubjectSetting $setting): array
    {
        return ['setting' => $setting, 'subjects' => Subject::where('is_active', true)->orderBy('name')->get(), 'majors' => Major::where('is_active', true)->orderBy('name')->get()];
    }

    private function payload(Request $request): array
    {
        $data = $request->validated();
        $data['major_id'] = (int) $data['semester_number'] <= 2 ? null : ($data['major_id'] ?? null);
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function badge(bool $active, string $yes, string $no): string
    {
        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.($active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600').'">'.($active ? $yes : $no).'</span>';
    }
}

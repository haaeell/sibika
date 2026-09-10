<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoreSubjectSettingRequest;
use App\Http\Requests\UpdateScoreSubjectSettingRequest;
use App\Models\Major;
use App\Models\ScoreSubjectSetting;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScoreSubjectSettingController extends Controller
{
    public function index(): View
    {
        return view('bk.score-subject-settings.index', [
            'settingsBySemester' => ScoreSubjectSetting::with(['subject', 'major'])
                ->orderBy('semester_number')
                ->orderBy('major_id')
                ->get()
                ->groupBy('semester_number'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('bk.score-subject-settings.create', $this->formData(new ScoreSubjectSetting([
            'semester_number' => $request->integer('semester') ?: null,
            'is_required' => true,
            'is_active' => true,
        ])));
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

}

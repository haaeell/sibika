<?php

namespace App\Services;

use App\Models\ScoreAverageSubjectSetting;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentScoreService
{
    public function subjectsFor(Student $student, int $semester): Collection
    {
        return $this->settingsFor($student, $semester);
    }

    public function averageSubjectsFor(Student $student, int $semester): Collection
    {
        $settings = $this->settingsFor($student, $semester);

        return $settings->filter(fn (ScoreSubjectSetting $setting) => $this->isIncludedInAverage($setting))->values();
    }

    public function isIncludedInAverage(ScoreSubjectSetting $setting): bool
    {
        return ScoreAverageSubjectSetting::query()
            ->where('subject_id', $setting->subject_id)
            ->where('major_id', $setting->major_id)
            ->value('include_in_average') ?? true;
    }

    private function settingsFor(Student $student, int $semester): Collection
    {
        $majorId = $student->schoolClass?->major_id;

        return ScoreSubjectSetting::query()
            ->with('subject')
            ->where('semester_number', $semester)
            ->where('is_active', true)
            ->where(function ($query) use ($majorId, $semester) {
                if ($semester <= 2 || ! $majorId) {
                    $query->whereNull('major_id');
                    return;
                }

                $query->whereNull('major_id')->orWhere('major_id', $majorId);
            })
            ->get()
            ->unique('subject_id')
            ->sortBy(fn (ScoreSubjectSetting $setting) => $setting->subject->name)
            ->values();
    }

    public function scoresFor(Student $student, int $semester): Collection
    {
        return $student->scores()->with('subject')->where('semester_number', $semester)->get()->keyBy('subject_id');
    }

    public function saveDraft(Student $student, int $semester, array $scores): void
    {
        $settings = $this->subjectsFor($student, $semester)->keyBy('subject_id');

        DB::transaction(function () use ($student, $semester, $scores, $settings): void {
            foreach ($scores as $subjectId => $score) {
                if (! $settings->has((int) $subjectId) || $score === null || $score === '') {
                    continue;
                }

                $record = $student->scores()->firstOrNew([
                    'subject_id' => $subjectId,
                    'semester_number' => $semester,
                ]);

                if ($record->exists && in_array($record->status, ['submitted', 'verified'], true)) {
                    continue;
                }

                $record->score = $score;
                $record->status = 'draft';
                $record->verification_note = null;
                $record->save();
            }
        });
    }

    public function submit(Student $student, int $semester): void
    {
        $settings = $this->subjectsFor($student, $semester);
        $scores = $student->scores()->where('semester_number', $semester)->get()->keyBy('subject_id');
        $missing = $settings->filter(fn (ScoreSubjectSetting $setting) => $setting->is_required && ! filled($scores->get($setting->subject_id)?->score));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['scores' => 'Semua mata pelajaran wajib harus diisi sebelum diajukan.']);
        }

        $student->scores()->where('semester_number', $semester)->whereIn('status', ['draft', 'rejected'])->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }

    public function verify(StudentScore $score, int $userId, ?string $note = null): void
    {
        if ($score->status !== 'submitted') {
            throw ValidationException::withMessages(['status' => 'Nilai ini tidak sedang menunggu verifikasi.']);
        }

        $score->update(['status' => 'verified', 'verified_by' => $userId, 'verified_at' => now(), 'verification_note' => $note]);
    }

    public function reject(StudentScore $score, int $userId, string $note): void
    {
        if ($score->status !== 'submitted') {
            throw ValidationException::withMessages(['status' => 'Nilai ini tidak sedang menunggu verifikasi.']);
        }

        $score->update(['status' => 'rejected', 'verified_by' => $userId, 'verified_at' => now(), 'verification_note' => $note]);
    }
}

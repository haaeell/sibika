<?php

namespace App\Services;

use App\Models\ScoreAverageSubjectSetting;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentScoreService
{
    /**
     * Query siswa dengan filter halaman Data Nilai
     * (tahun ajaran, kelas, jurusan, status) — dipakai tabel + export.
     */
    public function filteredStudents(array $filters = [])
    {
        $academicYearIds = array_filter((array) ($filters['academic_year_id'] ?? []));
        $classIds = array_filter((array) ($filters['class_id'] ?? []));
        $majorIds = array_filter((array) ($filters['major_id'] ?? []));
        $statuses = array_filter((array) ($filters['status'] ?? []));

        return Student::query()
            ->when($statuses, fn ($query) => $query->whereIn('status', $statuses))
            ->when($classIds, fn ($query) => $query->whereIn('class_id', array_map('intval', $classIds)))
            ->when($majorIds, fn ($query) => $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->whereIn('major_id', array_map('intval', $majorIds))))
            ->when($academicYearIds, fn ($query) => $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->whereIn('academic_year_id', array_map('intval', $academicYearIds))));
    }

    public function subjectsFor(Student $student, int $semester): Collection
    {
        return $this->settingsFor($student, $semester);
    }

    public function averageSubjectsFor(Student $student, int $semester): Collection
    {
        if (! $student->schoolClass?->major_id) {
            return collect();
        }

        $settings = $this->settingsFor($student, $semester);

        return $settings->filter(fn (ScoreSubjectSetting $setting) => $this->isIncludedInAverageForStudent($student, $setting))->values();
    }

    public function isIncludedInAverageForStudent(Student $student, ScoreSubjectSetting $setting): bool
    {
        $majorId = $student->schoolClass?->major_id;

        if (! $majorId) {
            return false;
        }

        return ScoreAverageSubjectSetting::query()
            ->where('subject_id', $setting->subject_id)
            ->where('major_id', $majorId)
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

    public function overallSummary(Student $student): array
    {
        return [
            'average' => $this->overallAverage($student),
            'class_rank' => $this->rankInClass($student),
            'class_total' => $student->class_id ? Student::where('class_id', $student->class_id)->count() : 0,
            'major_rank' => $this->rankInMajor($student),
            'major_total' => $student->schoolClass?->major_id ? Student::whereHas('schoolClass', fn ($query) => $query->where('major_id', $student->schoolClass->major_id))->count() : 0,
        ];
    }

    public function overallAverage(Student $student): ?float
    {
        if (! $student->relationLoaded('schoolClass')) {
            $student->load('schoolClass.major');
        }

        $subjectIds = collect(range(1, 5))
            ->flatMap(fn (int $semester) => $this->averageSubjectsFor($student, $semester)->pluck('subject_id'))
            ->unique()
            ->values();

        if ($subjectIds->isEmpty()) {
            return null;
        }

        $scores = $student->scores()
            ->whereIn('subject_id', $subjectIds)
            ->whereBetween('semester_number', [1, 5])
            ->whereNotNull('score')
            ->pluck('score');

        return $scores->isEmpty() ? null : round((float) $scores->avg(), 2);
    }

    public function rankInClass(Student $student): ?int
    {
        if (! $student->class_id) {
            return null;
        }

        return $this->denseRank($student, Student::with('schoolClass.major')->where('class_id', $student->class_id)->get());
    }

    public function rankInMajor(Student $student): ?int
    {
        $majorId = $student->schoolClass?->major_id;

        if (! $majorId) {
            return null;
        }

        return $this->denseRank($student, Student::with('schoolClass.major')->whereHas('schoolClass', fn ($query) => $query->where('major_id', $majorId))->get());
    }

    private function denseRank(Student $target, Collection $students): ?int
    {
        $ranked = $students
            ->map(fn (Student $student) => ['id' => $student->id, 'average' => $this->overallAverage($student)])
            ->filter(fn (array $row) => ! is_null($row['average']))
            ->sortByDesc('average')
            ->values();

        $rank = 0;
        $lastAverage = null;

        foreach ($ranked as $row) {
            if ($row['average'] !== $lastAverage) {
                $rank++;
                $lastAverage = $row['average'];
            }

            if ($row['id'] === $target->id) {
                return $rank;
            }
        }

        return null;
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

                $record->score = $score;
                $record->save();
            }
        });
    }
}

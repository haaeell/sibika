<?php

namespace App\Services;

use App\Models\ScoreAverageSubjectSetting;
use App\Models\ScoreEditRequest;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentScoreService
{
    /** @var array<string, Collection> cache settings per "semester:majorId" */
    private array $settingsCache = [];

    /** @var array<string, bool>|null peta "majorId:subjectId" => dihitung */
    private ?array $averageIncludeMap = null;

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

        $map = $this->averageIncludeMap();
        $key = $majorId.':'.$setting->subject_id;

        return $map[$key] ?? true;
    }

    /**
     * Peta include_in_average seluruh jurusan+mapel dalam 1 query.
     */
    private function averageIncludeMap(): array
    {
        if (is_null($this->averageIncludeMap)) {
            $this->averageIncludeMap = ScoreAverageSubjectSetting::query()
                ->get(['subject_id', 'major_id', 'include_in_average'])
                ->mapWithKeys(fn ($row) => [$row->major_id.':'.$row->subject_id => (bool) $row->include_in_average])
                ->all();
        }

        return $this->averageIncludeMap;
    }

    private function settingsFor(Student $student, int $semester): Collection
    {
        return $this->settingsForMajor($student->schoolClass?->major_id, $semester);
    }

    private function settingsForMajor(?int $majorId, int $semester): Collection
    {
        $key = $semester.':'.($majorId ?? 'null');

        if (! isset($this->settingsCache[$key])) {
            $this->settingsCache[$key] = ScoreSubjectSetting::query()
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

        return $this->settingsCache[$key];
    }

    /**
     * Peta subject_id => dihitung dalam rata-rata untuk 1 siswa + semester.
     * Dipakai view agar tidak ada query di dalam Blade.
     *
     * @return array<int, bool>
     */
    public function includedMapFor(Student $student, int $semester): array
    {
        $majorId = $student->schoolClass?->major_id;

        if (! $majorId) {
            return [];
        }

        $map = $this->averageIncludeMap();

        return $this->settingsForMajor($majorId, $semester)
            ->mapWithKeys(fn (ScoreSubjectSetting $setting) => [
                $setting->subject_id => $map[$majorId.':'.$setting->subject_id] ?? true,
            ])->all();
    }

    /**
     * Hangatkan cache settings + peta average untuk sekumpulan siswa.
     * Panggil sekali sebelum loop batch (tabel, export, laporan).
     */
    public function preloadFor(Collection $students): void
    {
        $majorIds = $students->map(fn (Student $student) => $student->schoolClass?->major_id)->unique()->values();

        foreach (range(1, 5) as $semester) {
            foreach ($majorIds as $majorId) {
                $this->settingsForMajor($majorId, $semester);
            }
        }

        $this->averageIncludeMap();
    }

    /**
     * Rata-rata semester + overall untuk banyak siswa murni dari
     * relasi yang sudah di-load (scores, schoolClass). Tanpa query baru.
     *
     * @return array<int, array{semesters: array<int, ?float>, overall: ?float}>
     */
    public function averagesForMany(Collection $students): array
    {
        $this->preloadFor($students);

        $result = [];
        foreach ($students as $student) {
            $semesters = [];
            $overallSubjectIds = [];
            foreach (range(1, 5) as $semester) {
                $averageIds = $this->averageSubjectIdsFor($student, $semester);
                $overallSubjectIds = array_merge($overallSubjectIds, $averageIds);
                $semesters[$semester] = $this->avgFromLoaded($student, $semester, $averageIds);
            }

            $overallSubjectIds = array_values(array_unique($overallSubjectIds));
            $overall = null;
            if ($overallSubjectIds !== []) {
                $scores = $student->scores
                    ->whereIn('subject_id', $overallSubjectIds)
                    ->whereBetween('semester_number', [1, 5])
                    ->filter(fn ($score) => filled($score->score));
                $overall = $scores->isEmpty() ? null : round((float) $scores->avg('score'), 2);
            }

            $result[$student->id] = ['semesters' => $semesters, 'overall' => $overall];
        }

        return $result;
    }

    /**
     * Dense rank in-memory per kelas, jurusan, dan angkatan untuk banyak siswa.
     * Total memakai hitungan seluruh DB (2 query agregat) agar sama
     * seperti perilaku sebelumnya.
     *
     * @return array<int, array{class_rank: ?int, class_total: int, major_rank: ?int, major_total: int, cohort_rank: ?int, cohort_total: int}>
     */
    public function ranksForMany(Collection $students, array $averages): array
    {
        $classTotals = Student::query()
            ->selectRaw('class_id, COUNT(*) as total')
            ->whereNotNull('class_id')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');
        $majorTotals = Student::query()
            ->select('classes.major_id')
            ->selectRaw('COUNT(*) as total')
            ->join('classes', 'classes.id', '=', 'students.class_id')
            ->whereNotNull('classes.major_id')
            ->groupBy('classes.major_id')
            ->pluck('total', 'major_id');
        $cohortTotals = Student::query()
            ->selectRaw('cohort_id, COUNT(*) as total')
            ->whereNotNull('cohort_id')
            ->groupBy('cohort_id')
            ->pluck('total', 'cohort_id');

        $classRanks = $this->denseRanksIn($students, $averages, fn (Student $student) => $student->class_id);
        $majorRanks = $this->denseRanksIn($students, $averages, fn (Student $student) => $student->schoolClass?->major_id);
        $cohortIds = $students->pluck('cohort_id')->filter()->unique();
        $cohortStudents = $cohortIds->isEmpty() ? collect() : Student::with(['scores.subject', 'schoolClass.major'])
            ->whereIn('cohort_id', $cohortIds)->get();
        $cohortRanks = $this->denseRanksIn($cohortStudents, $this->averagesForMany($cohortStudents), fn (Student $student) => $student->cohort_id);

        $result = [];
        foreach ($students as $student) {
            $classId = $student->class_id;
            $majorId = $student->schoolClass?->major_id;
            $cohortId = $student->cohort_id;
            $result[$student->id] = [
                'class_rank' => $classId ? ($classRanks[$student->id] ?? null) : null,
                'class_total' => $classId ? (int) ($classTotals[$classId] ?? 0) : 0,
                'major_rank' => $majorId ? ($majorRanks[$student->id] ?? null) : null,
                'major_total' => $majorId ? (int) ($majorTotals[$majorId] ?? 0) : 0,
                'cohort_rank' => $cohortId ? ($cohortRanks[$student->id] ?? null) : null,
                'cohort_total' => $cohortId ? (int) ($cohortTotals[$cohortId] ?? 0) : 0,
            ];
        }

        return $result;
    }

    /** @return array<int> subject_id yang dihitung dalam rata-rata */
    private function averageSubjectIdsFor(Student $student, int $semester): array
    {
        $majorId = $student->schoolClass?->major_id;

        if (! $majorId) {
            return [];
        }

        $map = $this->averageIncludeMap();

        return $this->settingsForMajor($majorId, $semester)
            ->filter(fn (ScoreSubjectSetting $setting) => $map[$majorId.':'.$setting->subject_id] ?? true)
            ->pluck('subject_id')->all();
    }

    private function avgFromLoaded(Student $student, int $semester, array $subjectIds): ?float
    {
        if ($subjectIds === []) {
            return null;
        }

        $scores = $student->scores
            ->where('semester_number', $semester)
            ->whereIn('subject_id', $subjectIds)
            ->filter(fn ($score) => filled($score->score));

        return $scores->isEmpty() ? null : round((float) $scores->avg('score'), 2);
    }

    /** @return array<int,int> student_id => rank */
    private function denseRanksIn(Collection $students, array $averages, callable $groupKey): array
    {
        $ranks = [];

        foreach ($students->filter(fn (Student $student) => ! is_null($groupKey($student)))->groupBy($groupKey) as $items) {
            $ranked = $items
                ->filter(fn (Student $student) => ! is_null($averages[$student->id]['overall'] ?? null))
                ->sortByDesc(fn (Student $student) => $averages[$student->id]['overall'])
                ->values();

            $rank = 0;
            $lastAverage = null;
            foreach ($ranked as $student) {
                $average = $averages[$student->id]['overall'];
                if ($average !== $lastAverage) {
                    $rank++;
                    $lastAverage = $average;
                }
                $ranks[$student->id] = $rank;
            }
        }

        return $ranks;
    }

    public function scoresFor(Student $student, int $semester): Collection
    {
        return $student->scores()->with('subject')->where('semester_number', $semester)->get()->keyBy('subject_id');
    }

    /**
     * Rata-rata semester dari mapel yang dihitung dalam rata-rata.
     * Bekerja dari relasi scores yang sudah di-load maupun query langsung.
     */
    public function semesterAverage(Student $student, int $semester): ?float
    {
        $averageSubjectIds = $this->averageSubjectsFor($student, $semester)->pluck('subject_id');

        if ($student->relationLoaded('scores')) {
            $scores = $student->scores
                ->where('semester_number', $semester)
                ->whereIn('subject_id', $averageSubjectIds->all())
                ->filter(fn ($score) => filled($score->score));

            return $scores->isEmpty() ? null : round((float) $scores->avg('score'), 2);
        }

        $scores = $student->scores()
            ->where('semester_number', $semester)
            ->whereIn('subject_id', $averageSubjectIds)
            ->whereNotNull('score')
            ->pluck('score');

        return $scores->isEmpty() ? null : round((float) $scores->avg(), 2);
    }

    public function overallSummary(Student $student): array
    {
        $student->loadMissing(['scores.subject', 'schoolClass.major', 'cohort']);

        $pool = collect([$student]);
        if ($student->class_id) {
            $pool = $pool->merge(Student::with(['scores.subject', 'schoolClass'])->where('class_id', $student->class_id)->where('id', '!=', $student->id)->get());
        }
        $majorId = $student->schoolClass?->major_id;
        if ($majorId) {
            $pool = $pool->merge(Student::with(['scores.subject', 'schoolClass'])->whereHas('schoolClass', fn ($query) => $query->where('major_id', $majorId))->whereNotIn('id', $pool->pluck('id')->all())->get());
        }
        if ($student->cohort_id) {
            $pool = $pool->merge(Student::with(['scores.subject', 'schoolClass.major'])->where('cohort_id', $student->cohort_id)->whereNotIn('id', $pool->pluck('id')->all())->get());
        }

        $averages = $this->averagesForMany($pool);
        $ranks = $this->ranksForMany($pool, $averages);
        $row = $ranks[$student->id];

        return [
            'average' => $averages[$student->id]['overall'],
            'class_rank' => $row['class_rank'],
            'class_total' => $row['class_total'],
            'major_rank' => $row['major_rank'],
            'major_total' => $row['major_total'],
            'cohort_rank' => $row['cohort_rank'],
            'cohort_total' => $row['cohort_total'],
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

        return $this->overallSummary($student)['class_rank'];
    }

    public function rankInMajor(Student $student): ?int
    {
        if (! $student->schoolClass?->major_id) {
            return null;
        }

        return $this->overallSummary($student)['major_rank'];
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

    /**
     * Semester terkunci bila sudah ada nilai terisi dan
     * tidak ada persetujuan edit yang masih berlaku.
     */
    public function isSemesterLocked(Student $student, int $semester): bool
    {
        if (! $this->hasCompletedRequiredScores($student, $semester)) {
            return false;
        }

        return is_null($this->usableApproval($student, $semester));
    }

    public function hasCompletedRequiredScores(Student $student, int $semester): bool
    {
        $requiredSubjectIds = $this->subjectsFor($student, $semester)
            ->where('is_required', true)
            ->pluck('subject_id');

        if ($requiredSubjectIds->isEmpty()) {
            return $this->hasFilledScores($student, $semester);
        }

        return $student->scores()
            ->where('semester_number', $semester)
            ->whereIn('subject_id', $requiredSubjectIds)
            ->whereNotNull('score')
            ->count() === $requiredSubjectIds->count();
    }

    public function hasFilledScores(Student $student, int $semester): bool
    {
        return $student->scores()
            ->where('semester_number', $semester)
            ->whereNotNull('score')
            ->exists();
    }

    /**
     * Seluruh pengajuan edit 1 siswa, dikelompokkan per semester.
     * 1 query untuk seluruh panel semester di halaman nilai siswa.
     *
     * @return Collection<int, Collection> semester => requests terbaru dulu
     */
    public function editRequestsFor(Student $student): Collection
    {
        return ScoreEditRequest::query()
            ->where('student_id', $student->id)
            ->latest()
            ->get()
            ->groupBy('semester_number');
    }

    public function usableApproval(Student $student, int $semester): ?ScoreEditRequest
    {
        return ScoreEditRequest::query()
            ->where('student_id', $student->id)
            ->where('semester_number', $semester)
            ->where('status', 'approved')
            ->whereNull('consumed_at')
            ->latest()
            ->first();
    }

    public function pendingRequest(Student $student, int $semester): ?ScoreEditRequest
    {
        return ScoreEditRequest::query()
            ->where('student_id', $student->id)
            ->where('semester_number', $semester)
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    public function latestRejectedRequest(Student $student, int $semester): ?ScoreEditRequest
    {
        return ScoreEditRequest::query()
            ->where('student_id', $student->id)
            ->where('semester_number', $semester)
            ->where('status', 'rejected')
            ->latest()
            ->first();
    }
}

<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;

class EligibleRankingService
{
    public function __construct(private readonly StudentScoreService $scores) {}

    public function forCohort(int $cohortId): Collection
    {
        $students = Student::where('cohort_id', $cohortId)
            ->with(['schoolClass.major', 'scores.subject'])->get();
        $averages = $this->scores->averagesForMany($students);

        $rows = $students->map(function (Student $student) use ($averages): array {
            $semesterAverages = $averages[$student->id]['semesters'];
            $complete = collect(range(1, 5))->every(function (int $semester) use ($student, $semesterAverages): bool {
                $settings = $this->scores->subjectsFor($student, $semester);
                $filled = $student->scores->where('semester_number', $semester)
                    ->filter(fn ($score) => filled($score->score))->pluck('subject_id');

                return $settings->isNotEmpty()
                    && ! is_null($semesterAverages[$semester])
                    && $settings->every(fn ($setting) => $filled->contains($setting->subject_id));
            });

            $subjectIds = collect(range(1, 5))
                ->flatMap(fn (int $semester) => $this->scores->averageSubjectsFor($student, $semester)->pluck('subject_id'))
                ->unique();
            $total = $complete ? $student->scores->whereBetween('semester_number', [1, 5])
                ->whereIn('subject_id', $subjectIds)->sum('score') : null;

            return ['student' => $student, 'average' => $complete ? $averages[$student->id]['overall'] : null,
                'total_score' => $total];
        });

        $ranked = $rows->filter(fn (array $row) => ! is_null($row['average']))
            ->sort(function (array $a, array $b): int {
                return ($b['average'] <=> $a['average'])
                    ?: (strcasecmp($a['student']->name, $b['student']->name))
                    ?: (strcmp($a['student']->nis, $b['student']->nis))
                    ?: ($a['student']->id <=> $b['student']->id);
            })->values()->map(fn (array $row, int $index) => [...$row, 'rank' => $index + 1]);

        return $ranked->concat($rows->filter(fn (array $row) => is_null($row['average']))
            ->map(fn (array $row) => [...$row, 'rank' => null]))->values();
    }

    public static function category(?int $rank, int $eligible, int $reserve): string
    {
        if (is_null($rank)) {
            return 'belum lengkap';
        }
        if ($rank <= $eligible) {
            return 'eligible';
        }
        if ($rank <= $eligible + $reserve) {
            return 'cadangan';
        }

        return 'tidak eligible';
    }
}

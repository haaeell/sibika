<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;

class StudentScoreReportService
{
    public function __construct(private readonly StudentScoreService $scoreService)
    {
    }

    public function generate(array $filters): array
    {
        $students = $this->scoreService
            ->filteredStudents([
                'academic_year_id' => isset($filters['academic_year_id']) ? [$filters['academic_year_id']] : [],
                'class_id' => isset($filters['class_id']) ? [$filters['class_id']] : [],
                'major_id' => isset($filters['major_id']) ? [$filters['major_id']] : [],
                'status' => isset($filters['status']) ? [$filters['status']] : [],
            ])
            ->with(['schoolClass.academicYear', 'schoolClass.major', 'scores.subject'])
            ->orderBy('name')
            ->get()
            ->map(function (Student $student): Student {
                $semesters = collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
                    $semester => $this->semesterAverage($student, $semester),
                ]);
                $student->setAttribute('semester_averages', $semesters);
                $student->setAttribute('overall_average', $this->overallAverage($student));

                return $student;
            });

        if (($filters['completeness'] ?? null) === 'complete') {
            $students = $students->filter(fn (Student $student) => ! is_null($student->overall_average));
        } elseif (($filters['completeness'] ?? null) === 'incomplete') {
            $students = $students->filter(fn (Student $student) => is_null($student->overall_average));
        }

        $students = $students->values();
        $total = $students->count();

        $averages = $students->map(fn (Student $student) => $student->overall_average)->filter(fn ($value) => ! is_null($value));
        $complete = $averages->count();

        // Ranking in-memory per kelas & jurusan (hindari query berulang).
        $classRanks = $this->denseRanks($students, fn (Student $student) => $student->class_id);
        $majorRanks = $this->denseRanks($students, fn (Student $student) => $student->schoolClass?->major_id);

        $summary = [
            'total' => $total,
            'complete' => $complete,
            'incomplete' => $total - $complete,
            'without_scores' => $students->filter(fn (Student $student) => $student->scores->isEmpty())->count(),
            'average' => $averages->isEmpty() ? null : round((float) $averages->avg(), 2),
            'highest' => $averages->isEmpty() ? null : round((float) $averages->max(), 2),
            'lowest' => $averages->isEmpty() ? null : round((float) $averages->min(), 2),
        ];

        $semesterAverages = collect(range(1, 5))->mapWithKeys(function (int $semester) use ($students) {
            $values = $students->map(fn (Student $student) => $student->semester_averages[$semester])->filter(fn ($value) => ! is_null($value));

            return [$semester => $values->isEmpty() ? null : round((float) $values->avg(), 2)];
        });

        $classAverages = $students
            ->groupBy(fn (Student $student) => $student->schoolClass?->name ?? 'Tanpa kelas')
            ->map(function (Collection $items) {
                $values = $items->map(fn (Student $student) => $student->overall_average)->filter(fn ($value) => ! is_null($value));

                return $values->isEmpty() ? 0 : round((float) $values->avg(), 2);
            })
            ->sortKeys();

        $majorAverages = $students
            ->groupBy(fn (Student $student) => $student->schoolClass?->major?->name ?? 'Tanpa jurusan')
            ->map(function (Collection $items) {
                $values = $items->map(fn (Student $student) => $student->overall_average)->filter(fn ($value) => ! is_null($value));

                return $values->isEmpty() ? 0 : round((float) $values->avg(), 2);
            })
            ->sortKeys();

        $distribution = $this->chart(
            ['< 70', '70–79', '80–89', '≥ 90'],
            [
                $averages->filter(fn ($value) => $value < 70)->count(),
                $averages->filter(fn ($value) => $value >= 70 && $value < 80)->count(),
                $averages->filter(fn ($value) => $value >= 80 && $value < 90)->count(),
                $averages->filter(fn ($value) => $value >= 90)->count(),
            ]
        );

        $ranked = $students->filter(fn (Student $student) => ! is_null($student->overall_average))->sortByDesc('overall_average')->values();
        $topStudents = $this->chart(
            $ranked->take(8)->map(fn (Student $student) => $student->name)->all(),
            $ranked->take(8)->map(fn (Student $student) => $student->overall_average)->all()
        );
        $bottomStudents = $this->chart(
            $ranked->reverse()->values()->take(8)->map(fn (Student $student) => $student->name)->all(),
            $ranked->reverse()->values()->take(8)->map(fn (Student $student) => $student->overall_average)->all()
        );

        $subjectAverages = $this->subjectAverages($students);

        $charts = [
            'completion' => $this->chart(['Lengkap', 'Belum lengkap'], [$complete, $total - $complete]),
            'semester_averages' => $this->chart(
                collect(range(1, 5))->map(fn (int $semester) => 'Semester '.$semester)->all(),
                collect(range(1, 5))->map(fn (int $semester) => $semesterAverages[$semester] ?? 0)->all()
            ),
            'class_averages' => $this->chart($classAverages->keys()->all(), $classAverages->values()->all()),
            'major_averages' => $this->chart($majorAverages->keys()->all(), $majorAverages->values()->all()),
            'distribution' => $distribution,
            'top_students' => $topStudents,
            'bottom_students' => $bottomStudents,
            'subjects' => $this->chart($subjectAverages->keys()->all(), $subjectAverages->values()->all()),
        ];

        $insights = [
            'top_class' => $classAverages->isEmpty() ? '-' : $classAverages->sortDesc()->keys()->first().' ('.$classAverages->max().')',
            'lowest_class' => $classAverages->isEmpty() ? '-' : $classAverages->sort()->keys()->first().' ('.$classAverages->min().')',
            'incomplete' => $total - $complete,
            'without_scores' => $summary['without_scores'],
            'lowest_subject' => $subjectAverages->isEmpty() ? '-' : $subjectAverages->sort()->keys()->first().' ('.$subjectAverages->min().')',
        ];

        $rows = $students->map(function (Student $student) use ($classRanks, $majorRanks): array {
            $missing = [];
            foreach (range(1, 5) as $semester) {
                $settings = $this->scoreService->subjectsFor($student, $semester);
                if ($settings->isEmpty()) {
                    continue;
                }
                $filledIds = $student->scores->where('semester_number', $semester)
                    ->filter(fn ($score) => filled($score->score))->pluck('subject_id')->all();
                $empty = $settings->reject(fn ($setting) => in_array($setting->subject_id, $filledIds, true));
                if ($empty->isNotEmpty()) {
                    $missing[] = 'Smt '.$semester.': '.$empty->map(fn ($setting) => $setting->subject?->name)->filter()->join(', ');
                }
            }

            return [
                'student' => $student,
                'semesters' => $student->semester_averages,
                'average' => $student->overall_average,
                'class_rank' => $classRanks[$student->id] ?? null,
                'major_rank' => $majorRanks[$student->id] ?? null,
                'missing' => $missing,
            ];
        });

        return compact('summary', 'charts', 'insights', 'rows');
    }

    private function semesterAverage(Student $student, int $semester): ?float
    {
        $averageSubjectIds = $this->scoreService->averageSubjectsFor($student, $semester)->pluck('subject_id');

        $scores = $student->scores
            ->where('semester_number', $semester)
            ->whereIn('subject_id', $averageSubjectIds->all())
            ->filter(fn ($score) => filled($score->score));

        return $scores->isEmpty() ? null : round((float) $scores->avg('score'), 2);
    }

    private function overallAverage(Student $student): ?float
    {
        $subjectIds = collect(range(1, 5))
            ->flatMap(fn (int $semester) => $this->scoreService->averageSubjectsFor($student, $semester)->pluck('subject_id'))
            ->unique()
            ->values();

        if ($subjectIds->isEmpty()) {
            return null;
        }

        $scores = $student->scores
            ->whereIn('subject_id', $subjectIds->all())
            ->whereBetween('semester_number', [1, 5])
            ->filter(fn ($score) => filled($score->score));

        return $scores->isEmpty() ? null : round((float) $scores->avg('score'), 2);
    }

    /**
     * Dense rank in-memory dalam tiap grup (kelas / jurusan).
     *
     * @return array<int,int> student_id => rank
     */
    private function denseRanks(Collection $students, callable $groupKey): array
    {
        $ranks = [];

        foreach ($students->groupBy($groupKey) as $items) {
            $ranked = $items->filter(fn (Student $student) => ! is_null($student->overall_average))
                ->sortByDesc('overall_average')->values();

            $rank = 0;
            $lastAverage = null;
            foreach ($ranked as $student) {
                if ($student->overall_average !== $lastAverage) {
                    $rank++;
                    $lastAverage = $student->overall_average;
                }
                $ranks[$student->id] = $rank;
            }
        }

        return $ranks;
    }

    private function subjectAverages(Collection $students): Collection
    {
        $sums = [];
        $counts = [];

        foreach ($students as $student) {
            foreach ($student->scores->filter(fn ($score) => filled($score->score)) as $score) {
                $name = $score->subject?->name ?? 'Mapel '.$score->subject_id;
                $sums[$name] = ($sums[$name] ?? 0) + (float) $score->score;
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        return collect($sums)
            ->map(fn ($sum, $name) => round($sum / $counts[$name], 2))
            ->sortDesc()
            ->take(10);
    }

    private function chart(array $labels, array $values): array
    {
        return ['labels' => $labels, 'values' => $values];
    }
}

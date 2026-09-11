<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;

class BiodataReportService
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function generate(array $filters): array
    {
        $students = Student::query()
            ->with(['profile.universityChoice1', 'profile.universityChoice2', 'schoolClass', 'cohort', 'documents'])
            ->when($filters['class_id'] ?? null, fn ($query, $value) => $query->where('class_id', $value))
            ->when($filters['cohort_id'] ?? null, fn ($query, $value) => $query->where('cohort_id', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['mcu_status'] ?? null, fn ($query, $value) => $query->whereHas('profile', fn ($profile) => $profile->where('mcu_status', $value)))
            ->orderBy('name')
            ->get()
            ->map(function (Student $student): Student {
                $student->setAttribute('biodata_progress', $this->progressService->calculate($student));

                return $student;
            });

        if ($filters['completeness'] ?? null) {
            $students = $students->filter(fn (Student $student) => ($filters['completeness'] === 'complete')
                ? $student->biodata_progress['percentage'] === 100
                : $student->biodata_progress['percentage'] < 100);
        }

        $students = $students->values();
        $total = $students->count();
        $complete = $students->filter(fn (Student $student) => $student->biodata_progress['percentage'] === 100)->count();
        $withCertificates = $students->filter(fn (Student $student) => $this->certificates($student)->isNotEmpty())->count();
        $withMedicalHistory = $students->filter(fn (Student $student) => $this->hasAnswer($student->profile?->medical_history))->count();

        $summary = [
            'total' => $total,
            'complete' => $complete,
            'incomplete' => $total - $complete,
            'average_progress' => $total ? (int) round($students->avg(fn (Student $student) => $student->biodata_progress['percentage'])) : 0,
            'with_certificates' => $withCertificates,
            'mcu_complete' => $students->where('profile.mcu_status', 'sudah')->count(),
            'medical_attention' => $withMedicalHistory,
        ];

        $classProgress = $students
            ->groupBy(fn (Student $student) => $student->schoolClass?->name ?? 'Tanpa kelas')
            ->map(fn (Collection $items) => (int) round($items->avg(fn (Student $student) => $student->biodata_progress['percentage'])))
            ->sortKeys();

        $campusChoice1 = $this->topUniversityChoices($students, 'universityChoice1');
        $campusChoice2 = $this->topUniversityChoices($students, 'universityChoice2');
        $provinces = $this->topTextValues($students, 'province');
        $cities = $this->topTextValues($students, 'city');

        $charts = [
            'completion' => $this->chart(['Lengkap', 'Belum lengkap'], [$complete, $total - $complete]),
            'class_progress' => $this->chart($classProgress->keys()->all(), $classProgress->values()->all()),
            'gender' => $this->chart(['Laki-laki', 'Perempuan', 'Belum diisi'], [
                $students->where('profile.gender', 'male')->count(),
                $students->where('profile.gender', 'female')->count(),
                $students->filter(fn (Student $student) => blank($student->profile?->gender))->count(),
            ]),
            'mcu' => $this->chart(['Sudah', 'Proses', 'Belum', 'Belum diisi'], [
                $students->where('profile.mcu_status', 'sudah')->count(),
                $students->where('profile.mcu_status', 'proses')->count(),
                $students->where('profile.mcu_status', 'belum')->count(),
                $students->filter(fn (Student $student) => blank($student->profile?->mcu_status))->count(),
            ]),
            'health' => $this->chart(['Ada riwayat', 'Tidak ada / -'], [$withMedicalHistory, $total - $withMedicalHistory]),
            'achievement' => $this->presenceChart($students, 'school_achievements'),
            'organization' => $this->chart(['Ya', 'Tidak', 'Belum diisi'], [
                $students->where('profile.organization_status', 'ya')->count(),
                $students->where('profile.organization_status', 'tidak')->count(),
                $students->filter(fn (Student $student) => blank($student->profile?->organization_status))->count(),
            ]),
            'organization_names' => $this->topTextValues($students, 'organization_name'),
            'province' => $this->chart($provinces->keys()->all(), $provinces->values()->all()),
            'city' => $this->chart($cities->keys()->all(), $cities->values()->all()),
            'campus_choice_1' => $this->chart($campusChoice1->keys()->all(), $campusChoice1->values()->all()),
            'campus_choice_2' => $this->chart($campusChoice2->keys()->all(), $campusChoice2->values()->all()),
            'height' => $this->rangeChart($students->pluck('profile.height_cm'), [150, 160, 170, 180], ['< 150 cm', '150-159 cm', '160-169 cm', '170-179 cm', '>= 180 cm']),
            'weight' => $this->rangeChart($students->pluck('profile.weight_kg'), [45, 55, 65, 75], ['< 45 kg', '45-54 kg', '55-64 kg', '65-74 kg', '>= 75 kg']),
        ];

        $lowestClass = $classProgress->sort()->first();
        $lowestClassName = $lowestClass === null ? null : $classProgress->search($lowestClass);

        $insights = [
            'lowest_class' => $lowestClassName ? $lowestClassName.' ('.$lowestClass.'%)' : '-',
            'top_campus' => $campusChoice1->keys()->first() ?? '-',
            'medical_attention' => $withMedicalHistory,
            'mcu_pending' => $total - $summary['mcu_complete'],
            'without_certificates' => $total - $withCertificates,
        ];

        $rows = $students->map(function (Student $student): array {
            $missing = collect($this->progressService->requiredFields($student))
                ->filter(fn ($value) => blank($value))
                ->keys()
                ->values();

            return [
                'student' => $student,
                'progress' => $student->biodata_progress['percentage'],
                'missing' => $missing,
                'certificate_count' => $this->certificates($student)->count(),
            ];
        });

        return compact('summary', 'charts', 'insights', 'rows');
    }

    private function hasAnswer(mixed $value): bool
    {
        return filled($value) && trim((string) $value) !== '-';
    }

    private function certificates(Student $student): Collection
    {
        return $student->documents->reject(fn ($document) => in_array($document->document_type, ['kip', 'kartu_keluarga', 'dokumen_lainnya'], true));
    }

    private function topTextValues(Collection $students, string $field): Collection
    {
        return $students
            ->map(fn (Student $student) => trim((string) $student->profile?->{$field}))
            ->filter(fn (string $value) => $value !== '' && $value !== '-')
            ->countBy()
            ->sortDesc()
            ->take(8);
    }

    private function topUniversityChoices(Collection $students, string $relation): Collection
    {
        return $students
            ->map(fn (Student $student) => $student->profile?->{$relation}?->name)
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8);
    }

    private function presenceChart(Collection $students, string $field): array
    {
        $present = $students->filter(fn (Student $student) => $this->hasAnswer($student->profile?->{$field}))->count();

        return $this->chart(['Ada', 'Tidak ada / -'], [$present, $students->count() - $present]);
    }

    private function rangeChart(Collection $values, array $limits, array $labels): array
    {
        $counts = array_fill(0, count($labels), 0);

        foreach ($values->filter(fn ($value) => filled($value)) as $value) {
            $index = 0;
            while ($index < count($limits) && $value >= $limits[$index]) {
                $index++;
            }
            $counts[$index]++;
        }

        return $this->chart($labels, $counts);
    }

    private function chart(array $labels, array $values): array
    {
        return ['labels' => $labels, 'values' => $values];
    }
}

<?php

namespace App\Services;

use App\Models\GraduationAnnouncement;
use App\Models\Student;

class GraduationService
{
    public function __construct(private readonly EligibleRankingService $ranking) {}

    public function row(Student $student): array
    {
        $student->loadMissing(['profile', 'parents', 'cohort', 'schoolClass']);
        $scores = $student->cohort_id
            ? $this->ranking->forCohort($student->cohort_id)->first(fn (array $row) => $row['student']->id === $student->id)
            : null;
        $parent = $student->parents->firstWhere('parent_type', 'father')?->name
            ?: $student->parents->firstWhere('parent_type', 'mother')?->name
            ?: $student->profile?->parent_father_name
            ?: $student->profile?->parent_mother_name;

        return [
            'total' => $scores['total_score'] ?? null,
            'average' => $scores['average'] ?? null,
            'rank' => $scores['rank'] ?? null,
            'parent' => $parent,
            'certificate_ready' => filled($student->nisn) && filled($student->profile?->birth_place)
                && filled($student->profile?->birth_date) && filled($parent) && filled($scores['average'] ?? null),
        ];
    }

    public function visible(?GraduationAnnouncement $announcement): bool
    {
        return $announcement?->is_active && $announcement->published_at->isPast();
    }

    public function certificateConfigured(GraduationAnnouncement $announcement): bool
    {
        return filled($announcement->principal_name) && filled($announcement->issued_place)
            && filled($announcement->meeting_date) && filled($announcement->signature_path)
            && filled($announcement->stamp_path);
    }
}

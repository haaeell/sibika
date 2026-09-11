<?php

namespace App\Services;

use App\Models\Student;

class StudentProgressService
{
    public function calculate(Student $student): array
    {
        $student->loadMissing(['profile', 'parents', 'documents']);
        $profile = $student->profile;
        $parents = $student->parents->keyBy('parent_type');

        $sections = [
            'personal' => $this->filled([
                $student->nis, $student->nisn, $student->name, $profile?->gender,
                $profile?->birth_place, $profile?->birth_date, $profile?->phone,
            ]),
            'address' => $this->filled([
                $profile?->province, $profile?->city, $profile?->district,
                $profile?->village, $profile?->postal_code, $profile?->address,
            ]),
            'physical' => $this->filled([
                $profile?->height_cm, $profile?->weight_kg, $profile?->medical_history, $profile?->mcu_status,
            ]),
            'campus_choice' => $this->filled([
                $profile?->university_choice_1, $profile?->university_choice_2,
            ]),
            'career_preparation' => $this->filled([
                $profile?->grade_11_preparation, $profile?->career_concern,
            ]),
            'school_activity' => $this->filled([
                $profile?->school_achievements, $profile?->organization_participation, $profile?->self_improvement_notes,
            ]),
            'documents' => $student->documents->isNotEmpty(),
        ];

        $completed = collect($sections)->filter()->count();
        $percentage = (int) round(($completed / count($sections)) * 100);

        return compact('sections', 'percentage');
    }

    private function filled(array $values): bool
    {
        return collect($values)->every(fn ($value) => filled($value));
    }
}

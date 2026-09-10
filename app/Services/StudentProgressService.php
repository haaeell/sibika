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
                $profile?->birth_place, $profile?->birth_date, $profile?->phone, $profile?->email,
            ]),
            'address' => $this->filled([
                $profile?->province, $profile?->city, $profile?->district,
                $profile?->village, $profile?->postal_code, $profile?->address,
            ]),
            'parents' => $this->filled([
                $parents->get('father')?->name, $parents->get('father')?->phone,
                $parents->get('mother')?->name, $parents->get('mother')?->phone,
            ]),
            'education' => $this->filled([
                $profile?->previous_school, $profile?->previous_school_address,
                $profile?->graduation_year,
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

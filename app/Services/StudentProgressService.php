<?php

namespace App\Services;

use App\Models\Student;

class StudentProgressService
{
    public function calculate(Student $student): array
    {
        $student->loadMissing(['profile', 'documents']);
        $profile = $student->profile;

        $requiredFields = [
            $profile?->gender,
            $profile?->birth_place,
            $profile?->birth_date,
            $profile?->phone,
            $profile?->province,
            $profile?->city,
            $profile?->district,
            $profile?->village,
            $profile?->postal_code,
            $profile?->address,
            $profile?->height_cm,
            $profile?->weight_kg,
            $profile?->medical_history,
            $profile?->mcu_status,
            $profile?->university_choice_1,
            $profile?->university_choice_2,
            $profile?->grade_11_preparation,
            $profile?->career_concern,
            $profile?->school_achievements,
            $profile?->organization_participation,
            $profile?->self_improvement_notes,
        ];

        $sections = [
            'personal' => $this->filled([
                $profile?->gender, $profile?->birth_place, $profile?->birth_date, $profile?->phone,
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
            'documents' => $student->documents
                ->reject(fn ($document) => in_array($document->document_type, ['kip', 'kartu_keluarga', 'dokumen_lainnya'], true))
                ->isNotEmpty(),
        ];

        $completed = collect($requiredFields)->filter(fn ($value) => filled($value))->count();
        $total = count($requiredFields);
        $percentage = (int) round(($completed / $total) * 100);

        return compact('sections', 'percentage', 'completed', 'total');
    }

    private function filled(array $values): bool
    {
        return collect($values)->every(fn ($value) => filled($value));
    }
}

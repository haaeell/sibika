<?php

namespace App\Services;

use App\Models\Student;

class StudentProgressService
{
    public function calculate(Student $student): array
    {
        $student->loadMissing(['profile', 'documents']);
        $profile = $student->profile;

        $requiredFields = $this->requiredFields($student);

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
                $profile?->university_choice_1_id, $profile?->university_choice_2_id,
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

    public function requiredFields(Student $student): array
    {
        $student->loadMissing('profile');
        $profile = $student->profile;

        return [
            'Jenis kelamin' => $profile?->gender,
            'Tempat lahir' => $profile?->birth_place,
            'Tanggal lahir' => $profile?->birth_date,
            'No WA aktif' => $profile?->phone,
            'Provinsi' => $profile?->province,
            'Kota / Kabupaten' => $profile?->city,
            'Kecamatan' => $profile?->district,
            'Kelurahan / Desa' => $profile?->village,
            'Kode pos' => $profile?->postal_code,
            'Alamat rumah' => $profile?->address,
            'Tinggi badan' => $profile?->height_cm,
            'Berat badan' => $profile?->weight_kg,
            'Riwayat kesehatan' => $profile?->medical_history,
            'Status MCU' => $profile?->mcu_status,
            'Pilihan kampus 1' => $profile?->university_choice_1_id,
            'Pilihan kampus 2' => $profile?->university_choice_2_id,
            'Persiapan kelas 11' => $profile?->grade_11_preparation,
            'Kekhawatiran karir' => $profile?->career_concern,
            'Prestasi sekolah' => $profile?->school_achievements,
            'Organisasi sekolah' => $profile?->organization_participation,
            'Evaluasi diri' => $profile?->self_improvement_notes,
        ];
    }

    private function filled(array $values): bool
    {
        return collect($values)->every(fn ($value) => filled($value));
    }
}

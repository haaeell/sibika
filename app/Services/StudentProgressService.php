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
            'parents' => $this->filled([
                $profile?->parent_father_name, $profile?->parent_father_occupation, $profile?->parent_mother_name,
                $profile?->parent_mother_occupation, $profile?->parent_phone, $profile?->parent_address,
            ]),
            'campus_choice' => $this->filled([
                $profile?->university_choice_1_id, $profile?->university_choice_2_id, $profile?->university_choice_3_id,
            ]),
            'school_activity' => $this->filled([
                $profile?->school_achievements, $profile?->organization_status, $this->organizationNameValue($profile), $profile?->self_improvement_notes,
            ]),
            'documents' => $this->requiredDocumentsFilled($student),
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
            'Nama ayah' => $profile?->parent_father_name,
            'Pekerjaan ayah' => $profile?->parent_father_occupation,
            'Nama ibu' => $profile?->parent_mother_name,
            'Pekerjaan ibu' => $profile?->parent_mother_occupation,
            'Nomor orang tua' => $profile?->parent_phone,
            'Alamat orang tua' => $profile?->parent_address,
            'Pilihan kampus 1' => $profile?->university_choice_1_id,
            'Pilihan kampus 2' => $profile?->university_choice_2_id,
            'Pilihan kampus 3' => $profile?->university_choice_3_id,
            'Prestasi sekolah' => $profile?->school_achievements,
            'Ikut organisasi' => $profile?->organization_status,
            'Nama organisasi' => $this->organizationNameValue($profile),
            'Evaluasi diri' => $profile?->self_improvement_notes,
            'Ijazah SMP' => $student->documents->firstWhere('document_type', 'Ijazah SMP')?->id,
            'Akte' => $student->documents->firstWhere('document_type', 'Akte')?->id,
            'Kartu Keluarga' => $student->documents->firstWhere('document_type', 'Kartu Keluarga')?->id,
        ];
    }

    private function requiredDocumentsFilled(Student $student): bool
    {
        return collect(['Ijazah SMP', 'Akte', 'Kartu Keluarga'])
            ->every(fn (string $type) => $student->documents->contains('document_type', $type));
    }

    private function organizationNameValue($profile): ?string
    {
        if ($profile?->organization_status === 'tidak') {
            return '-';
        }

        return $profile?->organization_name;
    }

    private function filled(array $values): bool
    {
        return collect($values)->every(fn ($value) => filled($value));
    }
}

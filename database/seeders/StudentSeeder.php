<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['nis' => '24001', 'nisn' => '0123456789', 'name' => 'Alya Putri', 'class' => 'X IPA 1', 'cohort' => 'Angkatan 2024'],
            ['nis' => '24002', 'nisn' => '0123456790', 'name' => 'Raka Maulana', 'class' => 'X IPA 1', 'cohort' => 'Angkatan 2024'],
            ['nis' => '24003', 'nisn' => '0123456791', 'name' => 'Nadia Safitri', 'class' => 'X IPS 1', 'cohort' => 'Angkatan 2024'],
            ['nis' => '25001', 'nisn' => '0123456792', 'name' => 'Fajar Ramadhan', 'class' => 'XI IPA 1', 'cohort' => 'Angkatan 2025'],
            ['nis' => '25002', 'nisn' => '0123456793', 'name' => 'Citra Maharani', 'class' => 'XI IPS 1', 'cohort' => 'Angkatan 2025'],
            ['nis' => '23001', 'nisn' => '0123456794', 'name' => 'Dimas Saputra', 'class' => 'XII IPA 1', 'cohort' => 'Angkatan 2023'],
            ['nis' => '23002', 'nisn' => '0123456795', 'name' => 'Intan Permata', 'class' => 'XII IPS 1', 'cohort' => 'Angkatan 2023'],
        ];

        foreach ($students as $studentData) {
            $className = $studentData['class'];
            $cohortName = $studentData['cohort'];
            unset($studentData['class'], $studentData['cohort']);

            Student::updateOrCreate(
                ['nis' => $studentData['nis']],
                [
                    ...$studentData,
                    'class_id' => SchoolClass::where('name', $className)->value('id'),
                    'cohort_id' => Cohort::where('name', $cohortName)->value('id'),
                    'status' => 'active',
                ]
            );
        }
    }
}

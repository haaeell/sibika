<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['code' => 'AGM', 'name' => 'Pendidikan Agama dan Budi Pekerti', 'category' => 'general'],
            ['code' => 'PPKN', 'name' => 'Pendidikan Pancasila', 'category' => 'general'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'category' => 'general'],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'category' => 'general'],
            ['code' => 'MAT', 'name' => 'Matematika', 'category' => 'general'],
            ['code' => 'SEJ', 'name' => 'Sejarah', 'category' => 'general'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'category' => 'general'],
            ['code' => 'SENI', 'name' => 'Seni dan Prakarya', 'category' => 'general'],
            ['code' => 'FIS', 'name' => 'Fisika', 'category' => 'general'],
            ['code' => 'KIM', 'name' => 'Kimia', 'category' => 'general'],
            ['code' => 'BIO', 'name' => 'Biologi', 'category' => 'general'],
            ['code' => 'EKO', 'name' => 'Ekonomi', 'category' => 'general'],
            ['code' => 'SOS', 'name' => 'Sosiologi', 'category' => 'general'],
            ['code' => 'GEO', 'name' => 'Geografi', 'category' => 'general'],
            ['code' => 'TKA-MAT', 'name' => 'TKA Matematika', 'category' => 'tka_mandatory'],
            ['code' => 'TKA-BIN', 'name' => 'TKA Bahasa Indonesia', 'category' => 'tka_mandatory'],
            ['code' => 'TKA-BIG', 'name' => 'TKA Bahasa Inggris', 'category' => 'tka_mandatory'],
            ['code' => 'TKA-FIS', 'name' => 'TKA Fisika', 'category' => 'tka_optional'],
            ['code' => 'TKA-KIM', 'name' => 'TKA Kimia', 'category' => 'tka_optional'],
            ['code' => 'TKA-BIO', 'name' => 'TKA Biologi', 'category' => 'tka_optional'],
            ['code' => 'TKA-EKO', 'name' => 'TKA Ekonomi', 'category' => 'tka_optional'],
            ['code' => 'TKA-SOS', 'name' => 'TKA Sosiologi', 'category' => 'tka_optional'],
            ['code' => 'TKA-GEO', 'name' => 'TKA Geografi', 'category' => 'tka_optional'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                [
                    'name' => $subject['name'],
                    'category' => $subject['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}

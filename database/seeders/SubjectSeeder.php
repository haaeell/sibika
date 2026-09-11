<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        ];

        $tkaSubjectIds = Subject::query()
            ->where('code', 'like', 'TKA-%')
            ->orWhereIn('category', ['tka_mandatory', 'tka_optional'])
            ->pluck('id');

        if ($tkaSubjectIds->isNotEmpty()) {
            DB::table('student_scores')->whereIn('subject_id', $tkaSubjectIds)->delete();
            DB::table('score_subject_settings')->whereIn('subject_id', $tkaSubjectIds)->delete();
            Subject::whereKey($tkaSubjectIds)->delete();
        }

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

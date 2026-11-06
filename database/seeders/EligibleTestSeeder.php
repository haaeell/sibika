<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentScoreService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EligibleTestSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $cohort = Cohort::firstOrCreate(
            ['name' => 'Angkatan Uji Eligible 2026'],
            ['entry_year' => 2023, 'graduation_year' => 2026, 'status' => 'active']
        );
        $year = AcademicYear::firstOrCreate(
            ['name' => '2025 / 2026', 'semester' => 'genap'],
            ['start_year' => 2025, 'end_year' => 2026, 'is_active' => false]
        );
        $major = Major::firstOrCreate(
            ['code' => 'UJI-ELIG'],
            ['name' => 'Jurusan Uji Eligible', 'is_active' => true]
        );
        $class = SchoolClass::firstOrCreate(
            ['name' => 'XII Uji Eligible', 'academic_year_id' => $year->id],
            ['grade_level' => 'XII', 'major_id' => $major->id]
        );
        $subject = Subject::firstOrCreate(
            ['code' => 'UJI-ELIG'],
            ['name' => 'Mapel Uji Eligible', 'category' => 'general', 'is_active' => true]
        );

        // ponytail: satu mapel uji per semester; saat mapel aktif bertambah, isi seluruh mapel yang dikembalikan subjectsFor().
        foreach (range(1, 5) as $semester) {
            ScoreSubjectSetting::firstOrCreate(
                ['subject_id' => $subject->id, 'major_id' => null, 'semester_number' => $semester],
                ['is_required' => true, 'include_in_average' => true, 'is_active' => true]
            );
        }

        $names = [
            'Alya Pratama', 'Bagas Nugraha', 'Citra Lestari', 'Daffa Ramadhan',
            'Elisa Maharani', 'Fajar Saputra', 'Gita Permata', 'Hana Safitri',
            'Indra Wijaya', 'Jihan Azzahra', 'Kirana Putri', 'Lutfi Hakim',
            'Maya Anindita', 'Nadia Rahma', 'Oscar Firmansyah', 'Putri Amelia',
            'Raka Maulana', 'Salsa Nuraini', 'Tania Zahra', 'Yoga Mahendra',
        ];

        $scoreService = app(StudentScoreService::class);
        foreach ($names as $index => $name) {
            $nis = 'ELG260'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $user = User::firstOrCreate(
                ['email' => strtolower($nis).'@smaplusasthahannas.id'],
                ['name' => $name, 'password' => $nis, 'must_change_password' => false]
            );
            $user->assignRole('siswa');
            $student = Student::firstOrCreate(
                ['nis' => $nis],
                ['nisn' => '0092600'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 'name' => $name,
                    'class_id' => $class->id, 'cohort_id' => $cohort->id, 'status' => 'active', 'user_id' => $user->id]
            );

            $student->profile()->updateOrCreate([], [
                'gender' => $index % 2 ? 'male' : 'female',
                'birth_place' => 'Jakarta',
                'birth_date' => sprintf('2008-%02d-%02d', ($index % 12) + 1, ($index % 27) + 1),
                'phone' => '08129000'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'province' => 'DKI Jakarta', 'city' => 'Jakarta Selatan',
                'district' => 'Pasar Minggu', 'village' => 'Pejaten Barat', 'postal_code' => '12510',
                'address' => 'Jl. Pendidikan Uji No. '.($index + 1),
            ]);
            foreach (['father' => 'Bapak', 'mother' => 'Ibu'] as $type => $title) {
                $student->parents()->updateOrCreate(['parent_type' => $type], [
                    'name' => $title.' '.$name, 'phone' => '08129001'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'occupation' => 'Wiraswasta',
                ]);
            }

            $student->setRelation('schoolClass', $class);
            foreach (range(1, 5) as $semester) {
                foreach ($scoreService->subjectsFor($student, $semester) as $setting) {
                    $student->scores()->firstOrCreate(
                        ['semester_number' => $semester, 'subject_id' => $setting->subject_id],
                        ['score' => max(0, 95 - intdiv($index, 2) * 2 - ($setting->subject_id % 3))]
                    );
                }
            }
        }
    }
}

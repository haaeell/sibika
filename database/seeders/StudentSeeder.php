<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Alya Putri Ramadhani', 'Raka Maulana', 'Nadia Safitri', 'Fajar Ramadhan',
            'Citra Maharani', 'Dimas Saputra', 'Intan Permata Sari', 'Bagas Aditya',
            'Salsabila Azzahra', 'Rizky Pratama', 'Nabila Rahma', 'Arya Nugraha',
            'Zahra Nuraini', 'Galang Mahendra', 'Keisya Anindita', 'Farhan Akbar',
            'Aisyah Humaira', 'Reza Pahlevi', 'Putri Amelia', 'Ilham Hidayat',
            'Najwa Khairunnisa', 'Daffa Alfarizi', 'Shafa Maharani', 'Kevin Abimanyu',
            'Tiara Oktaviani', 'Aditya Firmansyah', 'Anisa Fitriani', 'Muhammad Rafli',
            'Gita Larasati', 'Yoga Prasetyo',
        ];
        $classes = ['XI IPA 1', 'XI IPA 2', 'XI IPS 1', 'XI IPS 2'];
        $birthPlaces = ['Jakarta', 'Bandung', 'Bogor', 'Depok', 'Bekasi', 'Tangerang'];
        $locations = [
            ['DKI Jakarta', 'Jakarta Selatan', 'Pasar Minggu', 'Pejaten Barat', '12510'],
            ['Jawa Barat', 'Kota Bekasi', 'Bekasi Selatan', 'Pekayon Jaya', '17148'],
            ['Jawa Barat', 'Kota Depok', 'Beji', 'Kukusan', '16425'],
            ['Banten', 'Kota Tangerang Selatan', 'Pamulang', 'Pamulang Barat', '15417'],
        ];
        $universities = [
            'Universitas Indonesia',
            'Institut Teknologi Bandung',
            'Universitas Gadjah Mada',
            'IPB University',
            'BINUS University',
            'Telkom University',
            'Universitas Trisakti',
            'Universitas Islam Indonesia',
            'Politeknik Keuangan Negara STAN',
            'Institut Pemerintahan Dalam Negeri',
            'Politeknik Statistika STIS',
            'Sekolah Tinggi Intelijen Negara',
        ];
        $medicalHistories = ['Tidak ada', 'Alergi debu', 'Asma ringan', 'Rabun jauh'];
        $achievements = [
            'Juara 1 Olimpiade Matematika tingkat kota',
            'Juara 2 lomba pidato Bahasa Indonesia',
            'Finalis kompetisi karya ilmiah remaja',
            'Tidak ada',
        ];
        $organizations = ['OSIS bidang akademik', 'Pramuka', 'Paskibra', 'Klub sains sekolah'];
        $cohortId = Cohort::where('name', 'Angkatan 2025')->value('id');
        $classIds = SchoolClass::whereIn('name', $classes)->pluck('id', 'name');
        $universityIds = University::whereIn('name', $universities)->pluck('id', 'name');

        foreach ($names as $index => $name) {
            $number = $index + 1;
            $nis = '250'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $gender = $index % 2 === 0 ? 'female' : 'male';
            [$province, $city, $district, $village, $postalCode] = $locations[$index % count($locations)];

            $user = User::updateOrCreate(
                ['email' => "siswa.$nis@asthahannas.sch.id"],
                ['name' => $name, 'password' => 'password']
            );
            $user->syncRoles(['siswa']);

            $student = Student::updateOrCreate(
                ['nis' => $nis],
                [
                    'nisn' => '006'.str_pad((string) $number, 7, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'class_id' => $classIds[$classes[$index % count($classes)]],
                    'cohort_id' => $cohortId,
                    'status' => 'active',
                    'user_id' => $user->id,
                ]
            );

            $student->profile()->updateOrCreate(
                [],
                [
                    'gender' => $gender,
                    'birth_place' => $birthPlaces[$index % count($birthPlaces)],
                    'birth_date' => sprintf('2008-%02d-%02d', ($index % 12) + 1, ($index % 27) + 1),
                    'phone' => '0812'.str_pad((string) (34567000 + $number), 8, '0', STR_PAD_LEFT),
                    'province' => $province,
                    'city' => $city,
                    'district' => $district,
                    'village' => $village,
                    'postal_code' => $postalCode,
                    'address' => 'Jalan Pendidikan No. '.$number.', '.$village.', '.$city,
                    'height_cm' => 150 + ($index % 28),
                    'weight_kg' => 45 + ($index % 26),
                    'medical_history' => $medicalHistories[$index % count($medicalHistories)],
                    'university_choice_1_id' => $universityIds[$universities[$index % count($universities)]],
                    'university_choice_2_id' => $universityIds[$universities[($index + 2) % count($universities)]],
                    'grade_11_preparation' => 'Mengikuti pendalaman materi, try out rutin, dan menyusun jadwal belajar mingguan.',
                    'career_concern' => $index % 3 === 0
                        ? 'Masih mempertimbangkan jurusan yang paling sesuai dengan minat dan kemampuan.'
                        : 'Membutuhkan informasi tentang jalur masuk perguruan tinggi dan peluang beasiswa.',
                    'school_achievements' => $achievements[$index % count($achievements)],
                    'organization_participation' => $organizations[$index % count($organizations)],
                    'self_improvement_notes' => 'Perlu meningkatkan konsistensi belajar, manajemen waktu, dan kepercayaan diri.',
                    'mcu_status' => ['sudah', 'proses', 'belum'][$index % 3],
                ]
            );

            if ($index % 3 === 0) {
                $path = "student-documents/$student->id/sertifikat-prestasi-$number.pdf";
                $content = "%PDF-1.4\n% Sertifikat prestasi contoh untuk $name\n%%EOF";
                Storage::disk('public')->put($path, $content);

                StudentDocument::updateOrCreate(
                    ['student_id' => $student->id, 'file_path' => $path],
                    [
                        'document_type' => 'sertifikat_prestasi',
                        'original_name' => "sertifikat-prestasi-$nis.pdf",
                        'mime_type' => 'application/pdf',
                        'file_size' => strlen($content),
                        'uploaded_by' => $user->id,
                    ]
                );
            }
        }
    }
}

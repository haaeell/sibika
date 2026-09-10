<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['code' => '198501001', 'name' => 'Andi Pratama', 'email' => 'andi.pratama@asthahannas.sch.id', 'phone' => '081234567801', 'status' => 'active', 'subjects' => ['MAT', 'TKA-MAT']],
            ['code' => '198602002', 'name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@asthahannas.sch.id', 'phone' => '081234567802', 'status' => 'active', 'subjects' => ['BIN', 'TKA-BIN']],
            ['code' => '198703003', 'name' => 'Budi Santoso', 'email' => 'budi.santoso@asthahannas.sch.id', 'phone' => '081234567803', 'status' => 'active', 'subjects' => ['FIS', 'TKA-FIS']],
            ['code' => '198804004', 'name' => 'Dewi Lestari', 'email' => 'dewi.lestari@asthahannas.sch.id', 'phone' => '081234567804', 'status' => 'active', 'subjects' => ['BIO', 'TKA-BIO']],
            ['code' => '198905005', 'name' => 'Rudi Hermawan', 'email' => 'rudi.hermawan@asthahannas.sch.id', 'phone' => '081234567805', 'status' => 'active', 'subjects' => ['EKO', 'TKA-EKO']],
        ];

        foreach ($teachers as $teacherData) {
            $subjectCodes = $teacherData['subjects'];
            unset($teacherData['subjects']);

            $teacher = Teacher::updateOrCreate(['code' => $teacherData['code']], $teacherData);
            $teacher->subjects()->sync(Subject::whereIn('code', $subjectCodes)->pluck('id'));
        }
    }
}

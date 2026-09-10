<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2026 / 2027')->firstOrFail();
        $classes = [
            ['name' => 'X IPA 1', 'grade_level' => 'X', 'major' => 'IPA', 'teacher' => '198501001'],
            ['name' => 'X IPS 1', 'grade_level' => 'X', 'major' => 'IPS', 'teacher' => '198905005'],
            ['name' => 'XI IPA 1', 'grade_level' => 'XI', 'major' => 'IPA', 'teacher' => '198703003'],
            ['name' => 'XI IPS 1', 'grade_level' => 'XI', 'major' => 'IPS', 'teacher' => '198905005'],
            ['name' => 'XII IPA 1', 'grade_level' => 'XII', 'major' => 'IPA', 'teacher' => '198804004'],
            ['name' => 'XII IPS 1', 'grade_level' => 'XII', 'major' => 'IPS', 'teacher' => '198905005'],
        ];

        foreach ($classes as $classData) {
            $majorId = Major::where('code', $classData['major'])->value('id');
            $teacherId = Teacher::where('code', $classData['teacher'])->value('id');

            SchoolClass::updateOrCreate(
                ['name' => $classData['name'], 'academic_year_id' => $academicYear->id],
                [
                    'grade_level' => $classData['grade_level'],
                    'major_id' => $majorId,
                    'homeroom_teacher_id' => $teacherId,
                ]
            );
        }
    }
}

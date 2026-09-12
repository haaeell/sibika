<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\StudentProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliKelasDashboardController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function __invoke(Request $request): View
    {
        $teacher = Teacher::where('user_id', $request->user()->id)->first();
        $classes = SchoolClass::with(['students.profile', 'students.documents', 'academicYear'])->where('homeroom_teacher_id', $teacher?->id)->get();
        $students = $classes->flatMap->students->map(function (Student $student): Student {
            $student->setAttribute('progress', $this->progressService->calculate($student));
            return $student;
        });

        return view('wali-kelas.dashboard', [
            'teacher' => $teacher,
            'classes' => $classes,
            'students' => $students,
            'stats' => [
                ['label' => 'Kelas Diampu', 'value' => $classes->count(), 'icon' => 'fa-school', 'class' => 'bg-blue-50 text-blue-800'],
                ['label' => 'Total Siswa', 'value' => $students->count(), 'icon' => 'fa-users', 'class' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Biodata Lengkap', 'value' => $students->filter(fn (Student $student) => $student->progress['percentage'] === 100)->count(), 'icon' => 'fa-circle-check', 'class' => 'bg-sky-50 text-sky-700'],
                ['label' => 'MCU Selesai', 'value' => $students->where('profile.mcu_status', 'sudah')->count(), 'icon' => 'fa-file-medical', 'class' => 'bg-violet-50 text-violet-700'],
            ],
            'averageProgress' => $students->count() ? (int) round($students->avg(fn (Student $student) => $student->progress['percentage'])) : 0,
            'incompleteStudents' => $students->filter(fn (Student $student) => $student->progress['percentage'] < 100)->sortBy('progress.percentage')->take(10),
        ]);
    }
}

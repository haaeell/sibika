<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentProgressService;
use Illuminate\View\View;

class BkDashboardController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function __invoke(): View
    {
        $students = Student::with(['profile', 'documents', 'schoolClass'])->where('status', 'active')->get()
            ->map(function (Student $student): Student {
                $student->setAttribute('progress', $this->progressService->calculate($student));
                return $student;
            });

        $complete = $students->filter(fn (Student $student) => $student->progress['percentage'] === 100)->count();
        $total = $students->count();

        return view('bk.dashboard', [
            'stats' => [
                ['label' => 'Siswa Aktif', 'value' => $total, 'icon' => 'fa-users', 'class' => 'bg-blue-50 text-blue-800'],
                ['label' => 'Biodata Lengkap', 'value' => $complete, 'icon' => 'fa-circle-check', 'class' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Belum Lengkap', 'value' => $total - $complete, 'icon' => 'fa-circle-exclamation', 'class' => 'bg-rose-50 text-rose-700'],
                ['label' => 'MCU Selesai', 'value' => $students->where('profile.mcu_status', 'sudah')->count(), 'icon' => 'fa-file-medical', 'class' => 'bg-sky-50 text-sky-700'],
            ],
            'averageProgress' => $total ? (int) round($students->avg(fn (Student $student) => $student->progress['percentage'])) : 0,
            'healthAttention' => $students->filter(fn (Student $student) => filled($student->profile?->medical_history) && trim((string) $student->profile->medical_history) !== '-')->count(),
            'classProgress' => SchoolClass::with('students.profile', 'students.documents')->withCount('students')->orderBy('name')->get()->map(function (SchoolClass $class) {
                $items = $class->students->map(fn (Student $student) => $this->progressService->calculate($student)['percentage']);
                return ['name' => $class->name, 'students' => $class->students_count, 'progress' => $items->count() ? (int) round($items->avg()) : 0];
            }),
            'incompleteStudents' => $students->filter(fn (Student $student) => $student->progress['percentage'] < 100)->sortBy('progress.percentage')->take(8),
        ]);
    }
}

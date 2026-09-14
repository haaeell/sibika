<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\University;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Total Siswa', 'value' => Student::count(), 'icon' => 'fa-users', 'class' => 'bg-blue-50 text-blue-800'],
                ['label' => 'Guru', 'value' => Teacher::count(), 'icon' => 'fa-chalkboard-user', 'class' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Kelas', 'value' => SchoolClass::count(), 'icon' => 'fa-school', 'class' => 'bg-sky-50 text-sky-700'],
                ['label' => 'Akun User', 'value' => User::count(), 'icon' => 'fa-user-shield', 'class' => 'bg-violet-50 text-violet-700'],
            ],
            'masters' => [
                ['label' => 'Tahun Ajaran', 'value' => AcademicYear::count(), 'route' => route('bk.academic-years.index'), 'icon' => 'fa-calendar-days'],
                ['label' => 'Mata Pelajaran', 'value' => Subject::count(), 'route' => route('bk.subjects.index'), 'icon' => 'fa-book-open'],
                ['label' => 'Master Kampus', 'value' => University::count(), 'route' => route('bk.universities.index'), 'icon' => 'fa-building-columns'],
                ['label' => 'Pengaturan Login', 'value' => 'Edit', 'route' => route('admin.login-settings.edit'), 'icon' => 'fa-gear'],
            ],
            'studentStatuses' => Student::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'recentClasses' => SchoolClass::withCount('students')->with(['academicYear', 'homeroomTeacher'])->latest()->take(5)->get(),
        ]);
    }
}

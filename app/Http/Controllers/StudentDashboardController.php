<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Student;
use App\Services\StudentProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService) {}

    public function __invoke(Request $request): View
    {
        $student = Student::where('user_id', $request->user()->id)
            ->with(['schoolClass.academicYear', 'cohort', 'profile.universityChoice1', 'profile.universityChoice2', 'profile.universityChoice3', 'parents', 'documents'])
            ->firstOrFail();

        return view('siswa.dashboard', [
            'student' => $student,
            'progress' => $this->progressService->calculate($student),
            'articles' => Article::published()->latest('is_pinned')->latest('published_at')->take(3)->get(),
        ]);
    }
}

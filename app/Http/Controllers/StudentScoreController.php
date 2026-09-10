<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveStudentScoresRequest;
use App\Models\Student;
use App\Services\StudentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScoreController extends Controller
{
    public function __construct(private readonly StudentScoreService $scoreService)
    {
    }

    public function index(Request $request): View
    {
        $student = $this->studentFor($request)->load('schoolClass.major');
        $activeSemester = max(1, min(5, (int) $request->integer('semester', 1)));
        $semesters = collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
            $semester => [
                'settings' => $this->scoreService->subjectsFor($student, $semester),
                'scores' => $this->scoreService->scoresFor($student, $semester),
            ],
        ]);
        $summary = $this->scoreService->overallSummary($student);

        return view('siswa.scores.index', compact('student', 'activeSemester', 'semesters', 'summary'));
    }

    public function save(SaveStudentScoresRequest $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $this->scoreService->saveDraft($student, $request->integer('semester'), $request->input('scores', []));

        return redirect()->route('siswa.scores.index', ['semester' => $request->integer('semester')])->with('success', 'Nilai berhasil disimpan.');
    }

    private function studentFor(Request $request): Student
    {
        return Student::where('user_id', $request->user()->id)->firstOrFail();
    }
}

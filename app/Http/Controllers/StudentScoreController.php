<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveStudentScoresRequest;
use App\Models\Notification;
use App\Models\ScoreEditRequest;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\StudentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScoreController extends Controller
{
    public function __construct(
        private readonly StudentScoreService $scoreService,
        private readonly NotificationService $notifications,
    ) {
    }

    public function index(Request $request): View
    {
        $student = $this->studentFor($request)->load('schoolClass.major');
        $activeSemester = max(1, min(5, (int) $request->integer('semester', 1)));
        $semesters = collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
            $semester => [
                'settings' => $this->scoreService->subjectsFor($student, $semester),
                'scores' => $this->scoreService->scoresFor($student, $semester),
                'locked' => $this->scoreService->isSemesterLocked($student, $semester),
                'has_filled' => $this->scoreService->hasFilledScores($student, $semester),
                'approval' => $this->scoreService->usableApproval($student, $semester),
                'pending_request' => $this->scoreService->pendingRequest($student, $semester),
                'rejected_request' => $this->scoreService->latestRejectedRequest($student, $semester),
            ],
        ]);

        return view('siswa.scores.index', compact('student', 'activeSemester', 'semesters'));
    }

    public function save(SaveStudentScoresRequest $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $semester = $request->integer('semester');

        $approval = $this->scoreService->usableApproval($student, $semester);
        if ($this->scoreService->isSemesterLocked($student, $semester)) {
            return back()->with('error', 'Nilai semester '.$semester.' sudah tersimpan dan terkunci. Ajukan permintaan edit ke BK untuk mengubahnya.');
        }

        $this->scoreService->saveDraft($student, $semester, $request->input('scores', []));

        if ($approval) {
            $approval->update(['consumed_at' => now()]);
        }

        return redirect()->route('siswa.scores.index', ['semester' => $semester])->with('success', 'Nilai berhasil disimpan.');
    }

    public function requestEdit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester' => ['required', 'integer', 'between:1,5'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'semester.required' => 'Semester wajib dipilih.',
            'reason.required' => 'Alasan pengajuan wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter. Jelaskan mengapa nilai perlu diubah.',
        ]);

        $student = $this->studentFor($request);
        $semester = (int) $validated['semester'];

        if (! $this->scoreService->hasFilledScores($student, $semester)) {
            return back()->with('error', 'Belum ada nilai tersimpan pada semester ini. Silakan isi langsung.');
        }

        if ($this->scoreService->pendingRequest($student, $semester)) {
            return back()->with('error', 'Pengajuan edit semester '.$semester.' masih menunggu persetujuan BK.');
        }

        if ($this->scoreService->usableApproval($student, $semester)) {
            return back()->with('error', 'Edit semester '.$semester.' sudah diizinkan. Silakan ubah lalu simpan nilaimu.');
        }

        $editRequest = ScoreEditRequest::create([
            'student_id' => $student->id,
            'semester_number' => $semester,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        $this->notifications->sendMany(
            $this->notifications->bkUsers(),
            Notification::TYPE_SCORE_EDIT_REQUESTED,
            'Pengajuan edit nilai baru',
            $student->name.' ('.$student->nis.') mengajukan edit nilai semester '.$semester.'.',
            route('bk.score-edit-requests.index')
        );

        return back()->with('success', 'Pengajuan edit nilai semester '.$semester.' terkirim. Tunggu persetujuan BK.');
    }

    private function studentFor(Request $request): Student
    {
        return Student::where('user_id', $request->user()->id)->firstOrFail();
    }
}

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
        $student = $this->studentFor($request)->load(['schoolClass.major', 'scores.subject']);
        $activeSemester = max(1, min(5, (int) $request->integer('semester', 1)));

        // 1 query pengajuan untuk seluruh semester + hitung dari relasi ter-load.
        $editRequests = $this->scoreService->editRequestsFor($student);
        $filledSemesters = $student->scores
            ->filter(fn ($score) => filled($score->score))
            ->pluck('semester_number')->unique()->flip();

        $approvalFor = function (int $semester) use ($editRequests) {
            return $editRequests->get($semester, collect())
                ->first(fn ($item) => $item->status === 'approved' && is_null($item->consumed_at));
        };

        $semesters = collect(range(1, 5))->mapWithKeys(function (int $semester) use ($student, $editRequests, $filledSemesters, $approvalFor) {
            $hasFilled = $filledSemesters->has($semester);
            $approval = $approvalFor($semester);

            return [
                $semester => [
                    'settings' => $this->scoreService->subjectsFor($student, $semester),
                    'scores' => $student->scores->where('semester_number', $semester)->keyBy('subject_id'),
                    'locked' => $hasFilled && is_null($approval),
                    'has_filled' => $hasFilled,
                    'approval' => $approval,
                    'pending_request' => $editRequests->get($semester, collect())->firstWhere('status', 'pending'),
                    'rejected_request' => $editRequests->get($semester, collect())->firstWhere('status', 'rejected'),
                ],
            ];
        });

        $averages = collect(range(1, 5))->mapWithKeys(fn (int $semester) => [
            $semester => $this->scoreService->semesterAverage($student, $semester),
        ]);

        return view('siswa.scores.index', compact('student', 'activeSemester', 'averages', 'semesters'));
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

        if ($approval && $this->scoreService->hasCompletedRequiredScores($student, $semester)) {
            $approval->update(['consumed_at' => now()]);
        }

        $message = $this->scoreService->isSemesterLocked($student, $semester)
            ? 'Nilai lengkap berhasil disimpan dan semester terkunci.'
            : 'Draft nilai berhasil disimpan. Lengkapi mapel wajib untuk mengunci semester.';

        return redirect()->route('siswa.scores.index', ['semester' => $semester])->with('success', $message);
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

<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ScoreEditRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScoreEditApprovalController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(): View
    {
        return view('bk.score-edit-requests.index', [
            'pending' => ScoreEditRequest::with(['student.schoolClass', 'student.user'])
                ->where('status', 'pending')->latest()->get(),
            'history' => ScoreEditRequest::with(['student.schoolClass', 'reviewer'])
                ->whereIn('status', ['approved', 'rejected'])->latest()->take(50)->get(),
        ]);
    }

    public function approve(ScoreEditRequest $editRequest): RedirectResponse
    {
        if (! $editRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $editRequest->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'review_note' => null]);

        $this->notifyStudent($editRequest, Notification::TYPE_SCORE_EDIT_APPROVED, 'Pengajuan edit nilai disetujui', 'BK menyetujui edit nilai semester '.$editRequest->semester_number.'. Silakan ubah lalu simpan — izin berlaku sekali simpan.');

        return back()->with('success', 'Pengajuan disetujui. Siswa dapat mengedit nilai semester '.$editRequest->semester_number.' sekali simpan.');
    }

    public function reject(Request $request, ScoreEditRequest $editRequest): RedirectResponse
    {
        $validated = $request->validate([
            'review_note' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'review_note.required' => 'Alasan penolakan wajib diisi agar siswa memahami.',
            'review_note.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        if (! $editRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $editRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'],
        ]);

        $this->notifyStudent($editRequest, Notification::TYPE_SCORE_EDIT_REJECTED, 'Pengajuan edit nilai ditolak', 'BK menolak edit nilai semester '.$editRequest->semester_number.'. Catatan: '.$validated['review_note']);

        return back()->with('success', 'Pengajuan ditolak dan siswa telah diberi tahu.');
    }

    private function notifyStudent(ScoreEditRequest $editRequest, string $type, string $title, string $message): void
    {
        $user = $editRequest->student->user;
        if (! $user) {
            return;
        }

        $this->notifications->send(
            $user,
            $type,
            $title,
            $message,
            route('siswa.scores.index', ['semester' => $editRequest->semester_number])
        );
    }
}

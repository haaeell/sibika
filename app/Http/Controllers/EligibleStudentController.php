<?php

namespace App\Http\Controllers;

use App\Models\EligibleAnnouncement;
use App\Models\EligibleResult;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EligibleStudentController extends Controller
{
    public function index(Request $request): View
    {
        [$student, $announcement, $result] = $this->visibleResult($request);

        return view('siswa.eligible.index', compact('student', 'announcement', 'result'));
    }

    public function respond(Request $request): RedirectResponse
    {
        $data = $request->validate(['response' => ['required', 'in:diterima,ditolak']]);
        [$student, $announcement, $result] = $this->visibleResult($request);
        abort_unless($result && in_array($result->category, ['eligible', 'cadangan'], true), 404);

        DB::transaction(function () use ($result, $data): void {
            $locked = EligibleResult::whereKey($result->id)->lockForUpdate()->firstOrFail();
            if ($locked->response !== null) {
                throw ValidationException::withMessages(['response' => 'Pilihan sudah tersimpan dan tidak dapat diubah.']);
            }
            $locked->update(['response' => $data['response']]);
        });

        return redirect()->route('siswa.eligible.index')->with('success', 'Pilihan berhasil disimpan.');
    }

    private function visibleResult(Request $request): array
    {
        $student = Student::with(['schoolClass', 'cohort'])->where('user_id', $request->user()->id)->firstOrFail();
        $announcement = $student->cohort_id
            ? EligibleAnnouncement::where('cohort_id', $student->cohort_id)->first()
            : null;
        if (! $announcement || $announcement->published_at->isFuture()) {
            return [$student, null, null];
        }

        return [$student, $announcement, $announcement->results()->where('student_id', $student->id)->first()];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentViolationController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();

        return view('siswa.violations.index', [
            'violations' => $student->violations()->orderByDesc('occurred_at')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function download(Request $request, StudentViolation $violation)
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($violation->student_id === $student->id, 403);
        abort_unless($violation->warning_letter_path && Storage::disk('local')->exists($violation->warning_letter_path), 404);

        return Storage::disk('local')->download($violation->warning_letter_path, $violation->warning_letter_name);
    }
}

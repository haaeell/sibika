<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCounselingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return view('siswa.counseling.index', [
            'sessions' => CounselingSession::historyFor($student, $filters),
            'filters' => $filters,
        ]);
    }
}

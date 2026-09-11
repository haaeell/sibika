<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnBiodataRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\University;
use App\Services\StudentProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentBiodataAdminController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function index(): View
    {
        return view('bk.biodata.index', [
            'schoolClasses' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $classIds = array_filter((array) $request->input('class_id', []));
        $cohortIds = array_filter((array) $request->input('cohort_id', []));

        return DataTables::eloquent(Student::query()
            ->when($classIds, fn ($query) => $query->whereIn('class_id', array_map('intval', $classIds)))
            ->when($cohortIds, fn ($query) => $query->whereIn('cohort_id', array_map('intval', $cohortIds)))
            ->with(['schoolClass', 'cohort', 'profile', 'parents', 'documents'])
            ->orderBy('name'))
            ->addIndexColumn()
            ->addColumn('class_name', fn (Student $student) => $student->schoolClass?->name ?? '-')
            ->addColumn('cohort_name', fn (Student $student) => $student->cohort?->name ?? '-')
            ->addColumn('progress', fn (Student $student) => $this->progressBadge($this->progressService->calculate($student)['percentage']))
            ->addColumn('action', fn (Student $student) => view('bk.biodata._actions', compact('student'))->render())
            ->rawColumns(['progress', 'action'])
            ->toJson();
    }

    public function show(Student $student): View
    {
        $student->load(['profile.universityChoice1', 'profile.universityChoice2', 'parents', 'documents', 'schoolClass.academicYear', 'cohort']);

        return view('bk.students.biodata', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
        ]);
    }

    public function edit(Student $student): View
    {
        $student->load(['profile.universityChoice1', 'profile.universityChoice2', 'parents', 'documents', 'schoolClass.academicYear', 'cohort', 'user']);

        return view('siswa.biodata.index', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
            'isAdmin' => true,
            'biodataUpdateRoute' => route('bk.students.biodata.update', $student),
            'biodataBackRoute' => route('bk.students.biodata.show', $student),
            'universities' => University::where('is_active', true)->orderBy('type')->orderBy('name')->get()->groupBy('type'),
        ]);
    }

    public function update(UpdateOwnBiodataRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        $student->profile()->updateOrCreate([], $data);

        return redirect()->route('bk.students.biodata.show', $student)->with('success', 'Biodata siswa berhasil diperbarui.');
    }

    public function downloadDocument(Student $student, StudentDocument $document)
    {
        abort_unless($document->student_id === $student->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument(Student $student, StudentDocument $document): RedirectResponse
    {
        abort_unless($document->student_id === $student->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Sertifikat berhasil dihapus.');
    }

    private function progressBadge(int $percentage): string
    {
        $class = $percentage >= 80 ? 'bg-emerald-50 text-emerald-700' : ($percentage >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');

        return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class.'">'.$percentage.'%</span>';
    }
}

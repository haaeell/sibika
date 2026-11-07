<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ViolationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'class_at_incident' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return view('bk.violations.index', [
            'filters' => $filters,
            'classes' => StudentViolation::distinct()->orderBy('class_at_incident')->pluck('class_at_incident'),
            'topStudents' => StudentViolation::query()
                ->join('students', 'students.id', '=', 'student_violations.student_id')
                ->when($filters['class_at_incident'] ?? null, fn ($q, $class) => $q->where('class_at_incident', $class))
                ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('occurred_at', '>=', $date))
                ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('occurred_at', '<=', $date))
                ->select('students.id', 'students.name', 'students.nis')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('students.id', 'students.name', 'students.nis')
                ->orderByDesc('total')->orderBy('students.name')->limit(5)->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request->merge(['search' => $request->input('search.value', '')]);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_at_incident' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $term = trim((string) ($filters['search'] ?? ''));
        $query = StudentViolation::query()->with(['student' => fn ($q) => $q->withCount('violations'), 'creator'])
            ->when($filters['class_at_incident'] ?? null, fn (Builder $q, $class) => $q->where('class_at_incident', $class))
            ->when($filters['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('occurred_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('occurred_at', '<=', $date))
            ->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
                $q->whereHas('student', fn (Builder $s) => $s->where('name', 'like', '%'.$term.'%')->orWhere('nis', 'like', '%'.$term.'%'))
                    ->orWhere('pasal_perdupsis', 'like', '%'.$term.'%');
            }));

        return DataTables::eloquent($query)
            ->filter(fn () => null)
            ->addIndexColumn()
            ->addColumn('incident', fn (StudentViolation $v) => '<span class="font-semibold text-slate-800">'.e($v->occurred_at->translatedFormat('d F Y')).'</span><span class="mt-0.5 block text-xs text-slate-500">Kelas: '.e($v->class_at_incident).'</span>')
            ->addColumn('reportee', fn (StudentViolation $v) => '<a class="font-bold text-blue-800 hover:underline" href="'.route('bk.violations.student', $v->student).'">'.e($v->student->name).'</a><span class="block text-xs text-slate-500">'.e($v->student->nis).'</span>')
            ->addColumn('total_violations', fn (StudentViolation $v) => '<a href="'.route('bk.violations.student', $v->student).'" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-800 hover:bg-blue-100" aria-label="Lihat '.$v->student->violations_count.' pelanggaran '.e($v->student->name).'">'.$v->student->violations_count.'×</a>')
            ->addColumn('sanction_preview', fn (StudentViolation $v) => '<span class="line-clamp-2 max-w-xs whitespace-pre-line text-slate-600" title="'.e($v->sanction).'">'.e($v->sanction).'</span>')
            ->addColumn('letter', fn (StudentViolation $v) => $v->warning_letter_path
                ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Tersedia</span>'
                : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Belum ada</span>')
            ->addColumn('creator_name', fn (StudentViolation $v) => $v->creator?->name ?? 'Akun dihapus')
            ->addColumn('action', fn (StudentViolation $v) => view('bk.violations._actions', ['violation' => $v])->render())
            ->rawColumns(['incident', 'reportee', 'total_violations', 'sanction_preview', 'letter', 'action'])
            ->toJson();
    }

    public function student(Student $student): View
    {
        return view('bk.violations.student', [
            'student' => $student->load(['schoolClass', 'cohort']),
            'violations' => $student->violations()->with('creator')->orderByDesc('occurred_at')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->form(new StudentViolation);
    }

    public function edit(StudentViolation $violation): View
    {
        return $this->form($violation);
    }

    private function form(StudentViolation $violation): View
    {
        return view('bk.violations.form', [
            'violation' => $violation,
            'students' => Student::with('schoolClass')->orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('name')->pluck('name')->unique(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('warning_letter')) {
            $file = $request->file('warning_letter');
            $data['warning_letter_path'] = $file->store('warning-letters', 'local');
            $data['warning_letter_name'] = $file->getClientOriginalName();
        }
        $violation = StudentViolation::create([...$data, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return redirect()->route('bk.violations.index')->with('success', 'Pelanggaran '.$violation->student->name.' berhasil dicatat.');
    }

    public function update(Request $request, StudentViolation $violation): RedirectResponse
    {
        $data = $this->validated($request);
        $violation->update([...$data, 'updated_by' => $request->user()->id]);

        return redirect()->route('bk.violations.index')->with('success', 'Catatan pelanggaran diperbarui.');
    }

    public function uploadWarningLetter(Request $request, StudentViolation $violation): RedirectResponse
    {
        $data = $request->validate([
            'warning_letter' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ], [
            'warning_letter.mimes' => 'Surat peringatan harus berupa file PDF.',
            'warning_letter.max' => 'Ukuran surat peringatan maksimal 2 MB.',
        ]);
        $file = $data['warning_letter'];
        $path = $file->store('warning-letters', 'local');
        $oldPath = $violation->warning_letter_path;

        $violation->update([
            'warning_letter_path' => $path,
            'warning_letter_name' => $file->getClientOriginalName(),
            'updated_by' => $request->user()->id,
        ]);
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return back()->with('success', 'Surat peringatan berhasil '.($oldPath ? 'diganti.' : 'diunggah.'));
    }

    public function downloadWarningLetter(StudentViolation $violation)
    {
        abort_unless($violation->warning_letter_path && Storage::disk('local')->exists($violation->warning_letter_path), 404);

        return Storage::disk('local')->download($violation->warning_letter_path, $violation->warning_letter_name);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'class_at_incident' => ['required', 'string', 'max:100'],
            'occurred_at' => ['required', 'date_format:Y-m-d'],
            'pasal_perdupsis' => ['required', 'string', 'max:255'],
            'sanction' => ['required', 'string', 'max:5000'],
        ]);
    }
}

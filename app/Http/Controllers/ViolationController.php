<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentViolation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class ViolationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('bk.violations.index', [
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('name')->get(),
            'topStudents' => StudentViolation::query()
                ->join('students', 'students.id', '=', 'student_violations.student_id')
                ->when($filters['class_id'] ?? null, fn ($q, $class) => $q->where('students.class_id', $class))
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
        $filters = $this->filters($request, true);
        $query = $this->filtered($filters)->with(['student' => fn ($q) => $q->with('schoolClass')->withCount('violations'), 'creator']);

        return DataTables::eloquent($query)
            ->filter(fn () => null)
            ->addIndexColumn()
            ->addColumn('incident', fn (StudentViolation $v) => '<span class="font-semibold text-slate-800">'.e($v->occurred_at->translatedFormat('d F Y')).'</span><span class="mt-0.5 block text-xs text-slate-500">Kelas: '.e($v->student->schoolClass?->name ?? '-').'</span>')
            ->addColumn('reportee', fn (StudentViolation $v) => '<a class="font-bold text-blue-800 hover:underline" href="'.route('bk.violations.student', $v->student).'">'.e($v->student->name).'</a><span class="block text-xs text-slate-500">'.e($v->student->nis).'</span>')
            ->addColumn('total_violations', fn (StudentViolation $v) => '<a href="'.route('bk.violations.student', $v->student).'" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-800 hover:bg-blue-100" aria-label="Lihat '.$v->student->violations_count.' pelanggaran '.e($v->student->name).'">'.$v->student->violations_count.'×</a>')
            ->addColumn('sanction_preview', fn (StudentViolation $v) => '<span class="line-clamp-2 max-w-xs whitespace-pre-line text-slate-600" title="'.e($v->sanction).'">'.e($v->sanction).'</span>')
            ->addColumn('letter', fn (StudentViolation $v) => $v->warning_letter_path
                ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Tersedia</span>'
                : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Belum ada</span>')
            ->addColumn('creator_name', fn (StudentViolation $v) => $v->creator?->name ?? 'Akun dihapus')
            ->addColumn('action', fn (StudentViolation $v) => $request->user()->hasRole('kepala_sekolah') ? '' : view('bk.violations._actions', ['violation' => $v])->render())
            ->rawColumns(['incident', 'reportee', 'total_violations', 'sanction_preview', 'letter', 'action'])
            ->toJson();
    }

    public function export(Request $request, string $format): Response|BinaryFileResponse
    {
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 404);
        $records = $this->filtered($this->filters($request, true))->with(['student.schoolClass', 'creator'])
            ->orderByDesc('occurred_at')->orderByDesc('id')->get();
        $filename = 'laporan-pelanggaran-'.now()->format('Ymd-His').'.'.$format;
        if ($format === 'pdf') {
            return Pdf::loadView('bk.violations.pdf', compact('records'))->setPaper('a4', 'landscape')->download($filename);
        }

        $rows = $records->map(fn (StudentViolation $row) => array_map(
            fn ($value) => is_string($value) && preg_match('/^[=+\-@]/', ltrim($value)) ? "'".$value : $value,
            [$row->occurred_at->format('Y-m-d'), $row->student->nis, $row->student->name,
                $row->student->schoolClass?->name ?? '-', $row->pasal_perdupsis, $row->sanction, $row->creator?->name ?? 'Akun dihapus']
        ));

        return Excel::download(new TableExport(['Tanggal', 'NIS', 'Nama', 'Kelas Saat Ini', 'Pasal', 'Sanksi', 'Pencatat'], $rows), $filename, ExcelType::XLSX);
    }

    private function filters(Request $request, bool $search = false): array
    {
        return $request->validate(array_merge([
            'class_id' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ], $search ? ['search' => ['nullable', 'string', 'max:100']] : []));
    }

    private function filtered(array $filters): Builder
    {
        return StudentViolation::query()
            ->when($filters['class_id'] ?? null, fn (Builder $q, $class) => $q->whereHas('student', fn (Builder $s) => $s->where('class_id', $class)))
            ->when($filters['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('occurred_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('occurred_at', '<=', $date))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $q) use ($filters) {
                $term = trim($filters['search']);
                $q->where(fn (Builder $q) => $q->whereHas('student', fn (Builder $s) => $s->where('name', 'like', '%'.$term.'%')->orWhere('nis', 'like', '%'.$term.'%'))
                    ->orWhere('pasal_perdupsis', 'like', '%'.$term.'%'));
            });
    }

    public function destroy(StudentViolation $violation): RedirectResponse
    {
        $path = $violation->warning_letter_path;
        $violation->delete();
        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return back()->with('success', 'Catatan pelanggaran dihapus.');
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
            'students' => Student::orderBy('name')->get(),
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
            'occurred_at' => ['required', 'date_format:Y-m-d'],
            'pasal_perdupsis' => ['required', 'string', 'max:255'],
            'sanction' => ['required', 'string', 'max:5000'],
        ]);
    }
}

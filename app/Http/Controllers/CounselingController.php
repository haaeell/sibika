<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\Cohort;
use App\Models\CounselingSession;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class CounselingController extends Controller
{
    private const TYPES = ['pribadi', 'sosial', 'belajar', 'karier', 'lainnya'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'status' => ['nullable', Rule::in(['pending', 'overdue', 'done', 'none'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return view('bk.counseling.index', [
            'filters' => $filters,
            'totalCount' => CounselingSession::count(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
            'classes' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'types' => self::TYPES,
            'pendingCount' => CounselingSession::whereNotNull('follow_up')->whereNull('completed_at')->count(),
            'overdueCount' => CounselingSession::whereNotNull('follow_up')->whereNull('completed_at')->whereDate('due_at', '<', today())->count(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request->merge(['search' => $request->input('search.value', '')]);

        return DataTables::eloquent($this->filteredSessions($request))
            ->filter(fn ($query) => null)
            ->addColumn('student_name', fn (CounselingSession $session) => '<a class="font-bold text-blue-800 hover:underline" href="'.route('bk.counseling.student', $session->student).'">'.e($session->student->name).'</a><span class="block text-xs text-slate-500">'.e($session->student->nis).' · '.e($session->student->schoolClass?->name ?? 'Tanpa kelas').'</span>')
            ->addColumn('session_date', fn (CounselingSession $session) => $session->held_at->locale('id')->translatedFormat('j M Y, H.i'))
            ->addColumn('session_topic', fn (CounselingSession $session) => '<span class="text-xs text-slate-500">'.e(ucfirst($session->type)).'</span><span class="block font-semibold text-slate-900">'.e($session->topic).'</span>')
            ->addColumn('follow_up_status', function (CounselingSession $session): string {
                [$label, $style] = ! $session->follow_up ? ['Tanpa tindak lanjut', 'bg-slate-100 text-slate-600']
                    : ($session->completed_at ? ['Selesai', 'bg-emerald-50 text-emerald-700']
                        : ($session->due_at?->isBefore(today()) ? ['Lewat tenggat', 'bg-rose-50 text-rose-700'] : ['Perlu tindak lanjut', 'bg-amber-50 text-amber-700']));

                return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold '.$style.'">'.$label.'</span>';
            })
            ->addColumn('creator_name', fn (CounselingSession $session) => $session->creator?->name ?? 'Akun dihapus')
            ->addColumn('action', fn (CounselingSession $session) => '<a class="font-bold text-blue-800 hover:underline" href="'.route('bk.counseling.student', $session->student).'">Riwayat</a>')
            ->rawColumns(['student_name', 'session_topic', 'follow_up_status', 'action'])
            ->toJson();
    }

    public function export(Request $request, string $format): Response|BinaryFileResponse
    {
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);
        $sessions = $this->filteredSessions($request)->orderByDesc('held_at')->orderByDesc('id')->get();
        $filename = 'konseling-'.now()->format('Ymd-His').'.'.$format;

        if ($format === 'pdf') {
            $sessions->each(fn (CounselingSession $session) => $session->setAttribute('export_status', self::followUpStatus($session)));

            return Pdf::loadView('bk.counseling.export-pdf', compact('sessions'))
                ->setPaper('a4', 'portrait')->download($filename);
        }

        $rows = $sessions->map(fn (CounselingSession $session) => array_map(
            fn ($value) => is_string($value) && preg_match('/^[=+\-@]/', ltrim($value)) ? "'".$value : $value,
            [
                $session->held_at->format('Y-m-d H:i'),
                (string) $session->student->nis,
                $session->student->name,
                $session->student->schoolClass?->name ?? '-',
                $session->student->cohort?->name ?? '-',
                ucfirst($session->type),
                $session->topic,
                $session->summary,
                $session->outcome ?? '',
                $session->follow_up ?? '',
                $session->due_at?->format('Y-m-d') ?? '',
                self::followUpStatus($session),
                $session->creator?->name ?? 'Akun dihapus',
            ]
        ));

        return Excel::download(new TableExport(
            ['Tanggal Sesi', 'NIS', 'Nama Siswa', 'Kelas', 'Angkatan', 'Jenis', 'Topik', 'Ringkasan Masalah', 'Hasil Pembahasan', 'Tindak Lanjut', 'Tenggat', 'Status', 'Pencatat'],
            $rows
        ), $filename, ExcelType::XLSX);
    }

    private function filteredSessions(Request $request): Builder
    {
        $filters = $request->validate([
            'cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'status' => ['nullable', Rule::in(['pending', 'overdue', 'done', 'none'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $term = trim((string) ($filters['search'] ?? ''));

        return CounselingSession::query()->with(['student.schoolClass', 'student.cohort', 'creator'])
            ->when($filters['cohort_id'] ?? null, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('cohort_id', $id)))
            ->when($filters['class_id'] ?? null, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('class_id', $id)))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('held_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('held_at', '<=', $date))
            ->when($filters['status'] ?? null, function ($q, $status) {
                match ($status) {
                    'pending' => $q->whereNotNull('follow_up')->whereNull('completed_at'),
                    'overdue' => $q->whereNotNull('follow_up')->whereNull('completed_at')->whereDate('due_at', '<', today()),
                    'done' => $q->whereNotNull('completed_at'),
                    'none' => $q->whereNull('follow_up'),
                };
            })
            ->when($term !== '', fn ($q) => $q->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($student) => $student->where('name', 'like', '%'.$term.'%')->orWhere('nis', 'like', '%'.$term.'%'))
                    ->orWhere('topic', 'like', '%'.$term.'%');
            }));
    }

    private static function followUpStatus(CounselingSession $session): string
    {
        if (! $session->follow_up) {
            return 'Tanpa tindak lanjut';
        }
        if ($session->completed_at) {
            return 'Selesai';
        }

        return $session->due_at?->isBefore(today()) ? 'Lewat tenggat' : 'Perlu tindak lanjut';
    }

    public function create(): View
    {
        return view('bk.counseling.form', [
            'session' => new CounselingSession,
            'students' => Student::with(['schoolClass', 'cohort'])->orderBy('name')->get(),
            'classes' => SchoolClass::with('academicYear')->orderBy('name')->get(),
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $session = CounselingSession::create([...$data, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);

        return redirect()->route('bk.counseling.student', $session->student_id)->with('success', 'Sesi konseling dicatat.');
    }

    public function student(Request $request, Student $student): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $data = [
            'student' => $student->load(['schoolClass', 'cohort']),
            'filters' => $filters,
            'sessions' => CounselingSession::historyFor($student, $filters),
        ];

        return view($request->ajax() ? 'bk.counseling._history' : 'bk.counseling.student', $data);
    }

    public function edit(CounselingSession $session): View
    {
        return view('bk.counseling.form', [
            'session' => $session,
            'students' => Student::with(['schoolClass', 'cohort'])->orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
            'types' => self::TYPES,
        ]);
    }

    public function update(Request $request, CounselingSession $session): RedirectResponse
    {
        $data = $this->validated($request);
        if (! $data['follow_up']) {
            $data['completed_at'] = null;
        }
        $session->update([...$data, 'updated_by' => $request->user()->id]);

        return redirect()->route('bk.counseling.student', $session->student_id)->with('success', 'Sesi konseling diperbarui.');
    }

    public function complete(Request $request, CounselingSession $session): RedirectResponse
    {
        abort_unless(filled($session->follow_up), 422);
        $session->update(['completed_at' => $session->completed_at ? null : now(), 'updated_by' => $request->user()->id]);

        return back()->with('success', 'Status tindak lanjut diperbarui.');
    }

    public function destroy(CounselingSession $session): RedirectResponse
    {
        $student = $session->student;
        $session->delete();

        return redirect()->route('bk.counseling.student', $student)->with('success', 'Sesi konseling dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('class_id', $request->input('class_id'))],
            'held_at' => ['required', 'date'],
            'type' => ['required', Rule::in(self::TYPES)],
            'topic' => ['required', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
        ]);
        unset($data['class_id']);
        $data['follow_up'] = $data['follow_up'] ?? null;
        $data['due_at'] = $data['follow_up'] ? ($data['due_at'] ?? null) : null;

        return $data;
    }
}

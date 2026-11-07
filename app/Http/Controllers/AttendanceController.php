<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceWorkbook;
use App\Exports\StudentScoreSheetExport;
use App\Models\AttendanceAbsence;
use App\Models\AttendanceWeek;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $weeks = AttendanceWeek::orderByDesc('starts_on')->get();
        $allWeeks = ($filters['week_id'] ?? null) === 'all';
        $hasFilters = collect($filters)->except('week_id')->filter(fn ($value) => filled($value))->isNotEmpty()
            || (! $allWeeks && filled($filters['week_id'] ?? null));
        $recordFilters = $filters;
        if (! $hasFilters && ! $allWeeks && $weeks->isNotEmpty()) {
            $recordFilters['week_id'] = $weeks->first()->id;
        }
        $records = $this->filtered($recordFilters)->with(['student.schoolClass', 'week'])
            ->orderByDesc(AttendanceWeek::select('starts_on')->whereColumn('attendance_weeks.id', 'attendance_absences.attendance_week_id'))
            ->orderByDesc('attendance_absences.id')->paginate(20)->withQueryString();
        $summary = $this->summaryForPeriod($this->filtered($filters)->with(['student.schoolClass', 'week'])->get());

        return view('bk.attendance.index', compact('filters', 'recordFilters', 'hasFilters', 'allWeeks', 'weeks', 'records', 'summary') + [
            'students' => Student::orderBy('name')->get(['id', 'name', 'nis']),
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
            'topStudents' => $summary->take(5),
        ]);
    }

    public function export(Request $request, string $format): Response|BinaryFileResponse
    {
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 404);
        $filters = $this->filters($request);
        $records = $this->filtered($filters)->with(['student.schoolClass', 'week'])->get()->sortByDesc(fn ($row) => $row->week->starts_on->format('Y-m-d'))->values();
        $summary = $this->summaryForPeriod($records);
        $filename = 'laporan-absensi-'.now()->format('Ymd-His').'.'.$format;

        if ($format === 'pdf') {
            return Pdf::loadView('bk.attendance.pdf', compact('records', 'summary'))->setPaper('a4', 'landscape')->download($filename);
        }

        $safe = fn ($value) => is_string($value) && preg_match('/^[=+\-@]/', ltrim($value)) ? "'".$value : $value;
        $weeklyRows = $records->map(fn (AttendanceAbsence $row) => array_map($safe, [
            $row->week->week_number,
            $row->week->starts_on->format('d-m-Y').' - '.$row->week->ends_on->format('d-m-Y'),
            $row->student->name,
            $row->student->nis,
            $row->student->schoolClass?->name ?? '-',
            $row->meetings,
            $row->absences,
            number_format($row->percentage, 2, ',', '.').'%',
        ]));
        $summaryRows = $summary->map(fn (array $row) => array_map($safe, [
            $row['name'], $row['nis'], $row['class'], $row['meetings'], $row['absences'], number_format($row['percentage'], 2, ',', '.').'%',
        ]));

        return Excel::download(new AttendanceWorkbook([
            new StudentScoreSheetExport('Data Mingguan', ['Minggu ke', 'Rentang Tanggal', 'Siswa', 'NIS', 'Kelas', 'Pertemuan', 'Tidak Hadir', 'Persentase'], $weeklyRows),
            new StudentScoreSheetExport('Rekap Periode', ['Siswa', 'NIS', 'Kelas', 'Total Pertemuan', 'Total Tidak Hadir', 'Persentase'], $summaryRows),
        ]), $filename, ExcelType::XLSX);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'week_id' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value !== 'all' && (! ctype_digit((string) $value) || ! AttendanceWeek::whereKey($value)->exists())) {
                    $fail('Minggu yang dipilih tidak valid.');
                }
            }],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
        ]);
    }

    private function filtered(array $filters): Builder
    {
        return AttendanceAbsence::query()
            ->when(filled($filters['week_id'] ?? null) && $filters['week_id'] !== 'all', fn ($q) => $q->where('attendance_week_id', $filters['week_id']))
            ->when($filters['student_id'] ?? null, fn ($q, $id) => $q->where('student_id', $id))
            ->when($filters['class_id'] ?? null, fn ($q, $id) => $q->whereHas('student', fn ($student) => $student->where('class_id', $id)))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereHas('week', fn ($w) => $w->whereDate('starts_on', '>=', $date)))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereHas('week', fn ($w) => $w->whereDate('ends_on', '<=', $date)));
    }

    private function summaryForPeriod(Collection $records): Collection
    {
        return $records->groupBy(fn (AttendanceAbsence $row) => $row->student_id)
            ->map(function ($items): array {
                $first = $items->first();
                $meetings = $items->sum('meetings');
                $absences = $items->sum('absences');

                return ['name' => $first->student->name,
                    'nis' => $first->student->nis, 'meetings' => $meetings, 'absences' => $absences,
                    'class' => $first->student->schoolClass?->name ?? '-',
                    'percentage' => round($absences / $meetings * 100, 2)];
            })->sortByDesc('percentage')->values();
    }
}

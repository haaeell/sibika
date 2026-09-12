<?php

namespace App\Http\Controllers;

use App\Exports\StudentScoreSheetExport;
use App\Exports\StudentScoreWorkbook;
use App\Models\Student;
use App\Services\StudentScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentScoreExportController extends Controller
{
    public function __construct(private readonly StudentScoreService $scoreService)
    {
    }

    public function download(Request $request): BinaryFileResponse
    {
        $students = $this->scoreService
            ->filteredStudents($request->only(['academic_year_id', 'class_id', 'major_id', 'status']))
            ->with(['schoolClass.academicYear', 'schoolClass.major', 'scores.subject'])
            ->orderBy('name')
            ->get();

        $sheets = [$this->recapSheet($students)];
        foreach (range(1, 5) as $semester) {
            $sheets[] = $this->semesterSheet($students, $semester);
        }

        $filename = 'export-nilai-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new StudentScoreWorkbook($sheets), $filename, ExcelType::XLSX);
    }

    private function recapSheet(Collection $students): StudentScoreSheetExport
    {
        $headings = ['No', 'NIS', 'NISN', 'Nama', 'Kelas', 'Jurusan', 'Status',
            'Rata-rata Smt 1', 'Rata-rata Smt 2', 'Rata-rata Smt 3', 'Rata-rata Smt 4', 'Rata-rata Smt 5',
            'Rata-rata Keseluruhan', 'Ranking Kelas', 'Ranking Jurusan'];

        $rows = $students->values()->map(function (Student $student, int $index): array {
            $summary = $this->scoreService->overallSummary($student);

            return [
                $index + 1,
                $student->nis,
                $student->nisn ?? '-',
                $student->name,
                $student->schoolClass?->name ?? '-',
                $student->schoolClass?->major?->name ?? '-',
                $this->statusLabel($student->status),
                ...collect(range(1, 5))->map(fn (int $semester) => $this->fmt($this->semesterAverage($student, $semester)))->all(),
                $this->fmt($summary['average']),
                ($summary['class_rank'] ?? '-').'/'.$summary['class_total'],
                ($summary['major_rank'] ?? '-').'/'.$summary['major_total'],
            ];
        });

        return new StudentScoreSheetExport('Rekap', $headings, $rows);
    }

    private function semesterSheet(Collection $students, int $semester): StudentScoreSheetExport
    {
        // Union mapel semester ini lintas jurusan siswa hasil filter.
        $subjects = collect();
        foreach ($students as $student) {
            foreach ($this->scoreService->subjectsFor($student, $semester) as $setting) {
                $subjects[$setting->subject_id] = $setting->subject?->name ?? 'Mapel '.$setting->subject_id;
            }
        }
        $subjects = $subjects->sort()->all(); // subject_id => nama, urut nama

        $headings = ['No', 'NIS', 'Nama', 'Kelas', ...array_values($subjects), 'Rata-rata', 'Kelengkapan'];

        $rows = $students->values()->map(function (Student $student, int $index) use ($subjects, $semester): array {
            $scores = $student->scores->where('semester_number', $semester)->keyBy('subject_id');
            $filled = 0;

            $row = [$index + 1, $student->nis, $student->name, $student->schoolClass?->name ?? '-'];
            foreach (array_keys($subjects) as $subjectId) {
                $score = $scores->get($subjectId)?->score;
                if (filled($score)) {
                    $filled++;
                    $row[] = $this->fmt($score);
                } else {
                    $row[] = '-';
                }
            }

            $total = count($subjects);
            $row[] = $this->fmt($this->semesterAverage($student, $semester));
            $row[] = $filled === 0 ? 'Belum Diisi' : ($total > 0 && $filled >= $total ? 'Lengkap' : 'Sebagian');

            return $row;
        });

        return new StudentScoreSheetExport('Semester '.$semester, $headings, $rows);
    }

    private function semesterAverage(Student $student, int $semester): ?float
    {
        return $this->scoreService->semesterAverage($student, $semester);
    }

    private function fmt(mixed $value): string
    {
        return is_null($value) ? '-' : number_format((float) $value, 2);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'graduated' => 'Lulus',
            'inactive' => 'Nonaktif',
            default => ucfirst($status),
        };
    }
}

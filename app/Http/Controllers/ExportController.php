<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function download(string $resource, string $format): Response|BinaryFileResponse
    {
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);

        [$headings, $rows] = $this->dataFor($resource);
        $extension = $format === 'pdf' ? 'pdf' : 'xlsx';
        $filename = 'export-'.$resource.'-'.now()->format('Ymd-His').'.'.$extension;

        if ($format === 'pdf') {
            return Pdf::loadView('exports.table', [
                'title' => $this->titleFor($resource),
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d M Y H:i'),
            ])->setPaper('a4', 'landscape')->download($filename);
        }

        return ExcelFacade::download(new TableExport($headings, $rows), $filename, Excel::XLSX);
    }

    private function dataFor(string $resource): array
    {
        return match ($resource) {
            'academic-years' => [
                ['Nama', 'Tahun Mulai', 'Tahun Selesai', 'Semester', 'Status'],
                AcademicYear::query()->orderBy('start_year')->orderBy('semester')->get()->map(fn (AcademicYear $item) => [
                    $item->name,
                    $item->start_year,
                    $item->end_year,
                    ucfirst($item->semester),
                    $item->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'cohorts' => [
                ['Nama Angkatan', 'Tahun Masuk', 'Tahun Lulus', 'Status'],
                Cohort::query()->orderBy('entry_year')->get()->map(fn (Cohort $item) => [
                    $item->name,
                    $item->entry_year,
                    $item->graduation_year,
                    $this->statusLabel($item->status),
                ]),
            ],
            'majors' => [
                ['Kode', 'Nama Jurusan', 'Status'],
                Major::query()->orderBy('name')->get()->map(fn (Major $item) => [
                    $item->code,
                    $item->name,
                    $item->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'subjects' => [
                ['Kode', 'Mata Pelajaran', 'Kategori', 'Status'],
                Subject::query()->orderBy('name')->get()->map(fn (Subject $item) => [
                    $item->code,
                    $item->name,
                    $this->categoryLabel($item->category),
                    $item->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'school-classes' => [
                ['Nama Kelas', 'Tingkat', 'Jurusan', 'Tahun Ajaran', 'Wali Kelas'],
                SchoolClass::query()->with(['major', 'academicYear', 'homeroomTeacher'])->orderBy('name')->get()->map(fn (SchoolClass $item) => [
                    $item->name,
                    $item->grade_level,
                    $item->major?->name ?? '-',
                    $item->academicYear?->name ?? '-',
                    $item->homeroomTeacher?->name ?? '-',
                ]),
            ],
            'teachers' => [
                ['NIP / Kode', 'Nama', 'Email', 'Nomor HP', 'Mata Pelajaran', 'Status'],
                Teacher::query()->with('subjects')->orderBy('name')->get()->map(fn (Teacher $item) => [
                    $item->code,
                    $item->name,
                    $item->email,
                    $item->phone ?? '-',
                    $item->subjects->pluck('name')->join(', ') ?: '-',
                    $this->statusLabel($item->status),
                ]),
            ],
            'students' => [
                ['NIS', 'NISN', 'Nama', 'Kelas', 'Angkatan', 'Status'],
                Student::query()->with(['schoolClass', 'cohort'])->orderBy('name')->get()->map(fn (Student $item) => [
                    $item->nis,
                    $item->nisn ?? '-',
                    $item->name,
                    $item->schoolClass?->name ?? '-',
                    $item->cohort?->name ?? '-',
                    $this->statusLabel($item->status),
                ]),
            ],
            default => abort(404),
        };
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

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            'tka_mandatory' => 'TKA Wajib',
            'tka_optional' => 'TKA Pilihan',
            default => 'Umum',
        };
    }

    private function titleFor(string $resource): string
    {
        return [
            'academic-years' => 'Tahun Ajaran',
            'cohorts' => 'Angkatan',
            'majors' => 'Jurusan',
            'subjects' => 'Mata Pelajaran',
            'school-classes' => 'Kelas',
            'teachers' => 'Guru',
            'students' => 'Siswa',
        ][$resource] ?? 'Data';
    }
}

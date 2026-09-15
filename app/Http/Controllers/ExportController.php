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
use App\Models\University;
use App\Services\StudentProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService) {}

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
            'universities' => [
                ['Singkatan', 'Nama Kampus', 'Jenis', 'Status'],
                University::query()->orderBy('name')->get()->map(fn (University $item) => [
                    $item->short_name ?? '-',
                    $item->name,
                    ['negeri' => 'Negeri', 'swasta' => 'Swasta', 'kedinasan' => 'Kedinasan', 'lainnya' => 'Lainnya'][$item->type] ?? 'Lainnya',
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
            'biodata' => $this->biodataData(),
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
        return 'Umum';
    }

    private function titleFor(string $resource): string
    {
        return [
            'academic-years' => 'Tahun Ajaran',
            'cohorts' => 'Angkatan',
            'majors' => 'Jurusan',
            'subjects' => 'Mata Pelajaran',
            'universities' => 'Master Kampus',
            'school-classes' => 'Kelas',
            'teachers' => 'Guru',
            'students' => 'Siswa',
            'biodata' => 'Biodata Siswa',
        ][$resource] ?? 'Data';
    }

    private function biodataData(): array
    {
        $headings = [
            'NIS', 'NISN', 'Nama Lengkap', 'Status', 'Email Akun', 'Kelas', 'Angkatan',
            'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'No WA Aktif',
            'Provinsi', 'Kota / Kabupaten', 'Kecamatan', 'Kelurahan / Desa', 'Kode Pos', 'Alamat Rumah',
            'Tinggi Badan (cm)', 'Berat Badan (kg)', 'Riwayat Kesehatan/Penyakit', 'Status MCU Mandiri',
            'Jumlah MCU', 'Tanggal MCU Terakhir', 'Nama Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Pekerjaan Ibu', 'Nomor Orang Tua', 'Alamat Orang Tua',
            'Pilihan 1 (Kampus)', 'Jurusan Pilihan 1', 'Pilihan 2 (Kampus)', 'Jurusan Pilihan 2', 'Pilihan 3 (Kampus)', 'Jurusan Pilihan 3',
            'TKA',
            'Prestasi Akademik/Non Akademik', 'Organisasi/Ekskul', 'Hal Perlu Ditingkatkan (Evaluasi Diri)',
            'Progress Biodata', 'Ijazah SMP', 'Akte', 'Kartu Keluarga', 'Sertifikat Prestasi',
        ];

        $rows = Student::query()
            ->with(['schoolClass', 'cohort', 'user', 'profile.universityChoice1', 'profile.universityChoice2', 'profile.universityChoice3', 'documents', 'achievements.documents', 'organizations', 'tkaSelections.tkaSubject'])
            ->orderBy('name')
            ->get()
            ->map(function (Student $student): array {
                $profile = $student->profile;

                return [
                    $student->nis,
                    $student->nisn ?? '-',
                    $student->name,
                    $this->statusLabel($student->status),
                    $student->user?->email ?? '-',
                    $student->schoolClass?->name ?? '-',
                    $student->cohort?->name ?? '-',
                    ['male' => 'Laki-laki', 'female' => 'Perempuan'][$profile?->gender] ?? '-',
                    $profile?->birth_place ?? '-',
                    $profile?->birth_date?->format('Y-m-d') ?? '-',
                    $profile?->phone ?? '-',
                    $profile?->province ?? '-',
                    $profile?->city ?? '-',
                    $profile?->district ?? '-',
                    $profile?->village ?? '-',
                    $profile?->postal_code ?? '-',
                    $profile?->address ?? '-',
                    $profile?->height_cm ?? '-',
                    $profile?->weight_kg ?? '-',
                    $profile?->medical_history ?? '-',
                    $profile?->mcu_status ? ucfirst($profile->mcu_status) : '-',
                    $profile?->mcu_count ?? '-',
                    $profile?->mcu_last_date?->format('Y-m-d') ?? '-',
                    $profile?->parent_father_name ?? '-',
                    $profile?->parent_father_occupation ?? '-',
                    $profile?->parent_mother_name ?? '-',
                    $profile?->parent_mother_occupation ?? '-',
                    $profile?->parent_phone ?? '-',
                    $profile?->parent_address ?? '-',
                    $profile?->universityChoice1?->name ?? '-',
                    $profile?->university_major_choice_1 ?? '-',
                    $profile?->universityChoice2?->name ?? '-',
                    $profile?->university_major_choice_2 ?? '-',
                    $profile?->universityChoice3?->name ?? '-',
                    $profile?->university_major_choice_3 ?? '-',
                    $student->tkaSelections->map(fn ($selection) => $selection->tkaSubject?->name)->filter()->join('; ') ?: '-',
                    $student->achievements->map(fn ($a) => ucfirst(str_replace('_', ' ', $a->type)).' - '.$a->name.' - '.strtoupper(str_replace('_', '/', $a->level)).' - '.$a->year)->join('; ') ?: 'Tidak ada',
                    $student->organizations->map(fn ($o) => $o->name.' - '.$o->position.' - '.strtoupper(str_replace('_', '/', $o->level)).' - '.$o->year)->join('; ') ?: ($profile?->organization_status === 'tidak' ? 'Tidak ada' : '-'),
                    $profile?->self_improvement_notes ?? '-',
                    $this->progressService->calculate($student)['percentage'].'%',
                    $student->documents->firstWhere('document_type', 'Ijazah SMP')?->original_name ?? '-',
                    $student->documents->firstWhere('document_type', 'Akte')?->original_name ?? '-',
                    $student->documents->firstWhere('document_type', 'Kartu Keluarga')?->original_name ?? '-',
                    $student->achievements->flatMap->documents->map(fn ($d) => $d->original_name)->join('; ') ?: '-',
                ];
            });

        return [$headings, $rows];
    }
}

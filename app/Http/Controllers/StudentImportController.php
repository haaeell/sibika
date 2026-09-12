<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateWorkbook;
use App\Http\Requests\StudentImportRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    public function create(): View
    {
        return view('bk.students.import', [
            'schoolClasses' => SchoolClass::orderBy('name')->get(),
            'cohorts' => Cohort::orderByDesc('entry_year')->get(),
        ]);
    }

    public function template(): BinaryFileResponse
    {
        $classes = SchoolClass::orderBy('name')->pluck('name');
        $cohorts = Cohort::orderByDesc('entry_year')->pluck('name');

        $examples = [
            ['25001', '0060000001', 'Contoh Siswa Satu', (string) ($classes->first() ?? 'XI IPA 1'), (string) ($cohorts->first() ?? 'Angkatan 2025'), 'active'],
            ['25002', '', 'Contoh Siswa Dua', (string) ($classes->skip(1)->first() ?? ''), (string) ($cohorts->first() ?? ''), 'active'],
        ];

        $export = new StudentTemplateWorkbook($examples);

        return Excel::download($export, 'template-import-siswa.xlsx', ExcelType::XLSX);
    }

    public function store(StudentImportRequest $request, StudentAccountService $accounts): RedirectResponse
    {
        $sheets = Excel::toCollection(null, $request->file('file'));
        /** @var Collection<int,mixed> $rows */
        $rows = $sheets->first() ?? collect();
        if ($rows->isEmpty()) {
            return back()->with('error', 'File Excel kosong atau sheet pertama tidak terbaca.');
        }

        // Deteksi baris header
        $first = $rows->first();
        $firstValues = collect($first instanceof Collection ? $first->all() : (array) $first)
            ->map(fn ($v) => strtolower(trim((string) $v)));
        $hasHeader = $firstValues->contains('nis') && ($firstValues->contains('nama') || $firstValues->contains('name'));

        $dataRows = $hasHeader ? $rows->skip(1) : $rows;

        $classMap = SchoolClass::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $cohortMap = Cohort::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);

        $success = 0;
        $updated = 0;
        $failures = [];

        foreach ($dataRows->values() as $index => $row) {
            $excelRow = $index + ($hasHeader ? 2 : 1);
            $cells = array_values($row instanceof Collection ? $row->all() : (array) $row);
            $cells = array_pad($cells, 6, null);

            $payload = [
                'nis' => trim((string) ($cells[0] ?? '')),
                'nisn' => trim((string) ($cells[1] ?? '')),
                'name' => trim((string) ($cells[2] ?? '')),
                'class_name' => trim((string) ($cells[3] ?? '')),
                'cohort_name' => trim((string) ($cells[4] ?? '')),
                'status' => strtolower(trim((string) ($cells[5] ?? 'active'))) ?: 'active',
            ];

            // Lewati baris kosong total
            if ($payload['nis'] === '' && $payload['name'] === '') {
                continue;
            }

            $validator = Validator::make($payload, [
                'nis' => ['required', 'string', 'max:30'],
                'nisn' => ['nullable', 'string', 'max:30'],
                'name' => ['required', 'string', 'max:100'],
                'status' => ['required', 'in:active,graduated,inactive'],
            ], [
                'nis.required' => 'NIS wajib diisi.',
                'name.required' => 'Nama wajib diisi.',
                'status.in' => 'Status harus active/graduated/inactive.',
            ]);

            if ($validator->fails()) {
                $failures[] = ['row' => $excelRow, 'nis' => $payload['nis'], 'message' => $validator->errors()->first()];

                continue;
            }

            // Resolve kelas & angkatan by nama persis (case-insensitive)
            $classId = null;
            if ($payload['class_name'] !== '') {
                $classId = $classMap[strtolower($payload['class_name'])] ?? null;
                if (! $classId) {
                    $failures[] = ['row' => $excelRow, 'nis' => $payload['nis'], 'message' => "Kelas '{$payload['class_name']}' tidak ditemukan."];

                    continue;
                }
            }
            $cohortId = null;
            if ($payload['cohort_name'] !== '') {
                $cohortId = $cohortMap[strtolower($payload['cohort_name'])] ?? null;
                if (! $cohortId) {
                    $failures[] = ['row' => $excelRow, 'nis' => $payload['nis'], 'message' => "Angkatan '{$payload['cohort_name']}' tidak ditemukan."];

                    continue;
                }
            }

            // Cek unik NISN (kecuali milik sendiri)
            if ($payload['nisn'] !== '') {
                $nisnTaken = Student::where('nisn', $payload['nisn'])
                    ->where('nis', '!=', $payload['nis'])
                    ->exists();
                if ($nisnTaken) {
                    $failures[] = ['row' => $excelRow, 'nis' => $payload['nis'], 'message' => "NISN '{$payload['nisn']}' sudah dipakai siswa lain."];

                    continue;
                }
            }

            try {
                $isUpdate = Student::where('nis', $payload['nis'])->exists();

                DB::transaction(function () use ($payload, $classId, $cohortId, $accounts) {
                    $student = Student::updateOrCreate(
                        ['nis' => $payload['nis']],
                        [
                            'nisn' => $payload['nisn'] !== '' ? $payload['nisn'] : null,
                            'name' => $payload['name'],
                            'class_id' => $classId,
                            'cohort_id' => $cohortId,
                            'status' => $payload['status'],
                        ]
                    );
                    $student->profile()->firstOrCreate([]);
                    $accounts->ensureAccount($student);
                });

                $isUpdate ? $updated++ : $success++;
            } catch (\Throwable $e) {
                $failures[] = ['row' => $excelRow, 'nis' => $payload['nis'], 'message' => 'Gagal menyimpan: '.$e->getMessage()];
            }
        }

        if ($success === 0 && $updated === 0) {
            return back()->with('import_failures', $failures)->with('error', 'Import gagal. Tidak ada baris yang berhasil disimpan. Periksa daftar kesalahan.');
        }

        $message = "Import selesai: {$success} baru, {$updated} di-update".($failures !== [] ? ', '.count($failures).' gagal.' : '.');

        return redirect()->route('bk.students.index')
            ->with('success', $message)
            ->with('import_failures', $failures);
    }
}

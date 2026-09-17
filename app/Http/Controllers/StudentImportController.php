<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateWorkbook;
use App\Http\Requests\StudentImportRequest;
use App\Models\Cohort;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

    public function prepare(StudentImportRequest $request): JsonResponse
    {
        $sheets = Excel::toCollection(null, $request->file('file'));
        /** @var Collection<int,mixed> $rows */
        $rows = $sheets->first() ?? collect();
        if ($rows->isEmpty()) {
            return response()->json(['message' => 'File Excel kosong atau sheet pertama tidak terbaca.'], 422);
        }

        // Deteksi baris header
        $first = $rows->first();
        $firstValues = collect($first instanceof Collection ? $first->all() : (array) $first)
            ->map(fn ($v) => strtolower(trim((string) $v)));
        $hasHeader = $firstValues->contains('nis') && ($firstValues->contains('nama') || $firstValues->contains('name'));

        $dataRows = ($hasHeader ? $rows->skip(1) : $rows)
            ->values()
            ->map(fn ($row) => array_values($row instanceof Collection ? $row->all() : (array) $row))
            ->filter(fn (array $cells) => collect($cells)->contains(fn ($cell) => filled($cell)))
            ->values()
            ->all();

        if ($dataRows === []) {
            return response()->json(['message' => 'File Excel tidak berisi data siswa.'], 422);
        }

        $token = Str::random(40);
        Cache::put($this->cacheKey($request, $token), [
            'rows' => $dataRows,
            'header_offset' => $hasHeader ? 2 : 1,
            'success' => 0,
            'updated' => 0,
            'failures' => [],
        ], now()->addMinutes(30));

        return response()->json(['token' => $token, 'total' => count($dataRows)]);
    }

    public function batch(Request $request, StudentAccountService $accounts): JsonResponse
    {
        $token = $request->validate(['token' => ['required', 'string', 'size:40']])['token'];
        $key = $this->cacheKey($request, $token);
        $import = Cache::get($key);

        if (! $import) {
            return response()->json(['message' => 'Sesi import telah berakhir. Upload file kembali.'], 422);
        }

        $classMap = SchoolClass::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $cohortMap = Cohort::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $offset = (int) ($request->input('offset', 0));
        $rows = array_slice($import['rows'], $offset, 10);

        foreach ($rows as $index => $cells) {
            $excelRow = $offset + $index + $import['header_offset'];
            $result = $this->processRow($cells, $excelRow, $classMap, $cohortMap, $accounts);

            if ($result['status'] === 'success') {
                $import['success']++;
            } elseif ($result['status'] === 'updated') {
                $import['updated']++;
            } else {
                $import['failures'][] = $result['failure'];
            }
        }

        $nextOffset = $offset + count($rows);
        $done = $nextOffset >= count($import['rows']);

        if ($done) {
            Cache::forget($key);
        } else {
            Cache::put($key, $import, now()->addMinutes(30));
        }

        return response()->json([
            'processed' => $nextOffset,
            'total' => count($import['rows']),
            'next_offset' => $nextOffset,
            'done' => $done,
            'success' => $import['success'],
            'updated' => $import['updated'],
            'failures' => $import['failures'],
        ]);
    }

    private function processRow(array $cells, int $excelRow, Collection $classMap, Collection $cohortMap, StudentAccountService $accounts): array
    {
        $cells = array_pad($cells, 6, null);

        $payload = [
            'nis' => trim((string) ($cells[0] ?? '')),
            'nisn' => trim((string) ($cells[1] ?? '')),
            'name' => trim((string) ($cells[2] ?? '')),
            'class_name' => trim((string) ($cells[3] ?? '')),
            'cohort_name' => trim((string) ($cells[4] ?? '')),
            'status' => strtolower(trim((string) ($cells[5] ?? 'active'))) ?: 'active',
        ];

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
            return $this->failure($excelRow, $payload['nis'], $validator->errors()->first());
        }

        $classId = null;
        if ($payload['class_name'] !== '') {
            $classId = $classMap[strtolower($payload['class_name'])] ?? null;
            if (! $classId) {
                return $this->failure($excelRow, $payload['nis'], "Kelas '{$payload['class_name']}' tidak ditemukan.");
            }
        }
        $cohortId = null;
        if ($payload['cohort_name'] !== '') {
            $cohortId = $cohortMap[strtolower($payload['cohort_name'])] ?? null;
            if (! $cohortId) {
                return $this->failure($excelRow, $payload['nis'], "Angkatan '{$payload['cohort_name']}' tidak ditemukan.");
            }
        }

        if ($payload['nisn'] !== '' && Student::where('nisn', $payload['nisn'])->where('nis', '!=', $payload['nis'])->exists()) {
            return $this->failure($excelRow, $payload['nis'], "NISN '{$payload['nisn']}' sudah dipakai siswa lain.");
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

            return ['status' => $isUpdate ? 'updated' : 'success'];
        } catch (\Throwable $e) {
            return $this->failure($excelRow, $payload['nis'], 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    private function failure(int $row, string $nis, string $message): array
    {
        return ['status' => 'failed', 'failure' => compact('row', 'nis', 'message')];
    }

    private function cacheKey(Request $request, string $token): string
    {
        return 'student-import:'.$request->user()->id.':'.$token;
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentImportRequest;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UniversityImportController extends Controller
{
    public function create(): View
    {
        return view('bk.universities.import');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['Nama Kampus', 'Singkatan', 'Jenis', 'Status'];
            }

            public function array(): array
            {
                return [['Universitas Indonesia', 'UI', 'negeri', 'aktif'], ['Institut Teknologi Bandung', 'ITB', 'negeri', 'aktif']];
            }
        }, 'template-import-kampus.xlsx', ExcelType::XLSX);
    }

    public function prepare(StudentImportRequest $request): JsonResponse
    {
        $rows = (Excel::toCollection(null, $request->file('file'))->first() ?? collect())
            ->map(fn ($row) => array_values($row instanceof Collection ? $row->all() : (array) $row));
        $first = collect($rows->first() ?? [])->map(fn ($value) => strtolower(trim((string) $value)));
        $hasHeader = $first->contains('nama kampus') || $first->contains('nama');
        $dataRows = ($hasHeader ? $rows->skip(1) : $rows)->filter(fn (array $cells) => collect($cells)->contains(fn ($cell) => filled($cell)))->values()->all();

        if ($dataRows === []) {
            return response()->json(['message' => 'File Excel tidak berisi data kampus.'], 422);
        }

        $token = Str::random(40);
        Cache::put($this->cacheKey($request, $token), ['rows' => $dataRows, 'header_offset' => $hasHeader ? 2 : 1, 'success' => 0, 'updated' => 0, 'failures' => []], now()->addMinutes(30));

        return response()->json(['token' => $token, 'total' => count($dataRows)]);
    }

    public function batch(Request $request): JsonResponse
    {
        $token = $request->validate(['token' => ['required', 'string', 'size:40']])['token'];
        $key = $this->cacheKey($request, $token);
        $import = Cache::get($key);
        if (! $import) {
            return response()->json(['message' => 'Sesi import telah berakhir. Upload file kembali.'], 422);
        }

        $offset = (int) $request->input('offset', 0);
        foreach (array_slice($import['rows'], $offset, 10) as $index => $cells) {
            $result = $this->processRow($cells, $offset + $index + $import['header_offset']);
            if ($result['status'] === 'success') {
                $import['success']++;
            } elseif ($result['status'] === 'updated') {
                $import['updated']++;
            } else {
                $import['failures'][] = $result['failure'];
            }
        }
        $nextOffset = $offset + count(array_slice($import['rows'], $offset, 10));
        $done = $nextOffset >= count($import['rows']);
        $done ? Cache::forget($key) : Cache::put($key, $import, now()->addMinutes(30));

        return response()->json(['processed' => $nextOffset, 'total' => count($import['rows']), 'next_offset' => $nextOffset, 'done' => $done, 'success' => $import['success'], 'updated' => $import['updated'], 'failures' => $import['failures']]);
    }

    private function processRow(array $cells, int $row): array
    {
        $cells = array_pad($cells, 4, null);
        $data = ['name' => trim((string) $cells[0]), 'short_name' => trim((string) $cells[1]), 'type' => strtolower(trim((string) ($cells[2] ?: 'lainnya'))), 'status' => strtolower(trim((string) ($cells[3] ?: 'aktif')))];
        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:150'], 'short_name' => ['nullable', 'string', 'max:30'], 'type' => ['required', 'in:negeri,swasta,kedinasan,lainnya'], 'status' => ['required', 'in:aktif,nonaktif']], ['name.required' => 'Nama kampus wajib diisi.', 'type.in' => 'Jenis harus negeri/swasta/kedinasan/lainnya.', 'status.in' => 'Status harus aktif/nonaktif.']);
        if ($validator->fails()) {
            return $this->failure($row, $data['name'], $validator->errors()->first());
        }
        if ($data['short_name'] !== '' && University::where('short_name', $data['short_name'])->where('name', '!=', $data['name'])->exists()) {
            return $this->failure($row, $data['name'], "Singkatan '{$data['short_name']}' sudah dipakai kampus lain.");
        }

        $exists = University::where('name', $data['name'])->exists();
        University::updateOrCreate(['name' => $data['name']], ['short_name' => $data['short_name'] ?: null, 'type' => $data['type'], 'is_active' => $data['status'] === 'aktif']);

        return ['status' => $exists ? 'updated' : 'success'];
    }

    private function failure(int $row, string $name, string $message): array
    {
        return ['status' => 'failed', 'failure' => compact('row', 'name', 'message')];
    }

    private function cacheKey(Request $request, string $token): string
    {
        return 'university-import:'.$request->user()->id.':'.$token;
    }
}

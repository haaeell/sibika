<?php

namespace App\Http\Controllers;

use App\Exports\StudentScoreSheetExport;
use App\Exports\StudentScoreWorkbook;
use App\Models\AttendanceWeek;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceImportController extends Controller
{
    private const HEADINGS = ['nama_siswa', 'jumlah_pertemuan', 'jumlah_th'];

    public function template(): BinaryFileResponse
    {
        return Excel::download(new StudentScoreWorkbook([
            new StudentScoreSheetExport('Data Absensi', self::HEADINGS, [['ISI_NAMA_SISWA', 20, 4]]),
            new StudentScoreSheetExport('Panduan', ['Kolom', 'Petunjuk'], [
                ['Periode', 'Minggu ke-, tanggal awal dan akhir diisi sekali pada modal import, bukan di Excel.'],
                ['nama_siswa', 'Nama lengkap harus cocok tepat satu siswa terdaftar. Ganti baris contoh.'],
                ['Kelas', 'Kelas otomatis memakai kelas siswa saat ini.'],
                ['jumlah_pertemuan', 'Jumlah pertemuan siswa pada minggu tersebut, minimal 1.'],
                ['jumlah_th', 'Jumlah tidak hadir tanpa keterangan, 1 sampai jumlah_pertemuan.'],
                ['Catatan', 'Satu file berlaku untuk satu minggu. Isi hanya siswa dengan TH > 0; satu kesalahan membatalkan seluruh file.'],
            ]),
        ]), 'template-absensi.xlsx', ExcelType::XLSX);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'week_number' => ['required', 'integer', 'between:1,53'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);
        // ponytail: minggu lintas bulan perlu jumlah pertemuan per hari agar rekap bulanan tepat.
        if (substr($data['starts_on'], 0, 7) !== substr($data['ends_on'], 0, 7)) {
            return back()->withErrors(['ends_on' => 'Periode harus berada dalam satu bulan.'])->withInput();
        }

        $sheet = Excel::toCollection(null, $data['file'])->first() ?? collect();
        $header = $sheet->first()?->map(fn ($cell) => strtolower(trim((string) $cell)))->values()->all();
        if ($header !== self::HEADINGS) {
            return back()->withErrors(['file' => 'Kolom tidak sesuai template: '.implode(', ', self::HEADINGS).'.']);
        }
        $rows = $sheet->skip(1)->filter(fn ($row) => $row->contains(fn ($cell) => filled($cell)));
        if ($rows->isEmpty()) {
            return back()->withErrors(['file' => 'Sheet Data Absensi kosong.']);
        }

        $students = Student::query()->get()->groupBy(fn (Student $s) => mb_strtolower(trim($s->name)));
        $records = [];
        $seen = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 1;
            $fields = array_combine(self::HEADINGS, array_slice(array_pad($row->values()->all(), count(self::HEADINGS), null), 0, count(self::HEADINGS)));
            $fields['nama_siswa'] = trim((string) $fields['nama_siswa']);

            $validator = Validator::make($fields, [
                'nama_siswa' => ['required', 'string', 'max:100'],
                'jumlah_pertemuan' => ['required', 'integer', 'min:1', 'max:65535'],
                'jumlah_th' => ['required', 'integer', 'min:1', 'lte:jumlah_pertemuan'],
            ]);
            if ($validator->fails()) {
                $errors[] = 'Baris '.$line.': '.$validator->errors()->first();

                continue;
            }
            $matches = $students->get(mb_strtolower($fields['nama_siswa']), collect());
            if ($matches->count() !== 1) {
                $errors[] = 'Baris '.$line.': nama siswa '.($matches->isEmpty() ? 'tidak ditemukan' : 'ambigu').'.';

                continue;
            }
            $studentId = $matches->first()->id;
            if (isset($seen[$studentId])) {
                $errors[] = 'Baris '.$line.': siswa tercantum lebih dari sekali pada minggu yang sama.';

                continue;
            }
            $seen[$studentId] = true;
            $records[] = [
                'line' => $line, 'student_id' => $studentId,
                'meetings' => (int) $fields['jumlah_pertemuan'], 'absences' => (int) $fields['jumlah_th'],
            ];
        }

        if ($errors) {
            return back()->withErrors(['file' => 'Impor dibatalkan; tidak ada data tersimpan.'])->with('import_errors', $errors);
        }

        try {
            DB::transaction(function () use ($data, $records, $request): void {
                $week = AttendanceWeek::whereDate('starts_on', $data['starts_on'])->first();
                if ($week && ($week->week_number !== (int) $data['week_number'] || $week->ends_on->format('Y-m-d') !== $data['ends_on'])) {
                    throw ValidationException::withMessages(['file' => 'Periode tanggal awal ini sudah ada dengan minggu atau tanggal akhir berbeda.']);
                }
                if (! $week) {
                    if (AttendanceWeek::where('starts_on', '<=', $data['ends_on'])->where('ends_on', '>=', $data['starts_on'])->exists()) {
                        throw ValidationException::withMessages(['file' => 'Periode bertumpang tindih dengan minggu lain.']);
                    }
                    $week = AttendanceWeek::create([
                        'week_number' => $data['week_number'], 'starts_on' => $data['starts_on'], 'ends_on' => $data['ends_on'],
                    ]);
                }
                foreach ($records as $record) {
                    if ($week->absences()->where('student_id', $record['student_id'])->exists()) {
                        throw ValidationException::withMessages(['file' => 'Baris '.$record['line'].': siswa sudah tercatat pada minggu ini.']);
                    }
                    $week->absences()->create([
                        'student_id' => $record['student_id'], 'meetings' => $record['meetings'],
                        'absences' => $record['absences'],
                        'created_by' => $request->user()->id,
                    ]);
                }
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('bk.attendance.index')->with('success', count($records).' data TH minggu ke-'.$data['week_number'].' berhasil diimpor.');
    }
}

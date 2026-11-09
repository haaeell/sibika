<?php

namespace App\Http\Controllers;

use App\Exports\StudentScoreSheetExport;
use App\Exports\StudentScoreWorkbook;
use App\Models\Student;
use App\Models\StudentViolation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViolationImportController extends Controller
{
    private const HEADINGS = ['nama_siswa', 'tanggal_kejadian', 'pasal_perdupsis', 'sanksi'];

    public function template(): BinaryFileResponse
    {
        return Excel::download(new StudentScoreWorkbook([
            new StudentScoreSheetExport('Data Pelanggaran', self::HEADINGS, [
                ['ISI_NAMA_SISWA', '03-02-2026', 'Pasal 7', 'Pembinaan tertulis'],
            ]),
            new StudentScoreSheetExport('Panduan', ['Kolom', 'Cara mengisi'], [
                ['nama_siswa', 'Wajib. Nama lengkap siswa yang terdaftar dan unik; ganti ISI_NAMA_SISWA pada contoh.'],
                ['tanggal_kejadian', 'Wajib. Format DD-MM-YYYY atau DD/MM/YYYY, misalnya 03-02-2026.'],
                ['pasal_perdupsis', 'Wajib. Pasal yang dilanggar, misalnya Pasal 7.'],
                ['sanksi', 'Wajib. Sanksi atau tindakan yang diberikan.'],
                ['Catatan', 'Isi hanya sheet Data Pelanggaran. Hapus atau ganti baris contoh sebelum impor.'],
                ['Validasi', 'Jika satu baris salah, seluruh file batal diimpor; lihat nomor baris pada pesan kesalahan.'],
            ]),
        ]), 'template-pelanggaran.xlsx', ExcelType::XLSX);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
        $sheet = Excel::toCollection(null, $request->file('file'))->first() ?? collect();
        $header = $sheet->first()?->map(fn ($cell) => strtolower(trim((string) $cell)))->values()->all();
        if ($header !== self::HEADINGS) {
            return back()->withErrors(['file' => 'Kolom tidak sesuai template. Gunakan: '.implode(', ', self::HEADINGS).'.']);
        }

        $rows = $sheet->skip(1)->filter(fn ($row) => $row->contains(fn ($cell) => filled($cell)));
        if ($rows->isEmpty()) {
            return back()->withErrors(['file' => 'File tidak berisi data pelanggaran.']);
        }

        $students = Student::all()->groupBy(fn (Student $student) => mb_strtolower(trim($student->name)));
        $valid = [];
        $errors = [];
        foreach ($rows as $index => $row) {
            $line = $index + 1;
            $cells = array_pad($row->values()->all(), count(self::HEADINGS), null);
            $data = array_combine(self::HEADINGS, array_slice($cells, 0, count(self::HEADINGS)));
            $data['nama_siswa'] = trim((string) $data['nama_siswa']);
            if (is_numeric($data['tanggal_kejadian']) && (float) $data['tanggal_kejadian'] > 0) {
                $data['tanggal_kejadian'] = ExcelDate::excelToDateTimeObject($data['tanggal_kejadian'])->format('Y-m-d');
            } elseif (is_string($data['tanggal_kejadian'])) {
                try {
                    $data['tanggal_kejadian'] = Carbon::createFromFormat(str_contains($data['tanggal_kejadian'], '/') ? 'd/m/Y' : 'd-m-Y', trim($data['tanggal_kejadian']))->format('Y-m-d');
                } catch (\Throwable) {
                    // Biarkan validator melaporkan format tanggal yang salah.
                }
            }
            $validator = Validator::make($data, [
                'nama_siswa' => ['required', 'string', 'max:100'],
                'tanggal_kejadian' => ['required', 'date_format:Y-m-d'],
                'pasal_perdupsis' => ['required', 'string', 'max:255'],
                'sanksi' => ['required', 'string', 'max:5000'],
            ]);
            if ($validator->fails()) {
                $errors[] = 'Baris '.$line.': '.$validator->errors()->first();
            } else {
                $key = mb_strtolower($data['nama_siswa']);
                $matches = $students->get($key, collect());
                if ($matches->isEmpty()) {
                    $errors[] = 'Baris '.$line.': siswa '.$data['nama_siswa'].' tidak ditemukan.';
                } elseif ($matches->count() > 1) {
                    $errors[] = 'Baris '.$line.': siswa '.$data['nama_siswa'].' lebih dari satu; gunakan nama yang dibedakan di data siswa terlebih dahulu.';
                } else {
                    $student = $matches->first();
                    $valid[] = [
                        'student_id' => $student->id,
                        'occurred_at' => $data['tanggal_kejadian'],
                        'pasal_perdupsis' => trim($data['pasal_perdupsis']),
                        'sanction' => trim($data['sanksi']),
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if ($errors) {
            return back()->withErrors(['file' => 'Impor dibatalkan; tidak ada data tersimpan.'])->with('import_errors', $errors);
        }

        DB::transaction(fn () => collect($valid)->chunk(500)->each(fn ($chunk) => StudentViolation::insert($chunk->all())));

        return redirect()->route('bk.violations.index')->with('success', count($valid).' pelanggaran berhasil diimpor.');
    }
}

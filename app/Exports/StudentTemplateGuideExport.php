<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateGuideExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Panduan';
    }

    public function array(): array
    {
        return [
            ['Kolom', 'Wajib', 'Keterangan'],
            ['NIS', 'Ya', 'Nomor induk siswa, unik. Jika sudah ada di database maka data akan di-update.'],
            ['NISN', 'Tidak', 'Nomor induk nasional, unik bila diisi. Boleh dikosongkan.'],
            ['Nama', 'Ya', 'Nama lengkap siswa (maks 100 karakter).'],
            ['Kelas', 'Tidak', 'Tulis persis sesuai Nama Kelas di aplikasi (contoh: XI IPA 1). Kosongkan bila belum ditempatkan.'],
            ['Angkatan', 'Tidak', 'Tulis persis sesuai Nama Angkatan (contoh: Angkatan 2025). Kosongkan bila belum ditentukan.'],
            ['Status', 'Ya', 'Salah satu: active (Aktif), graduated (Lulus), inactive (Nonaktif). Default: active.'],
            [],
            ['Catatan akun login:'],
            ['Setiap baris baru otomatis dibuatkan akun: email {NISN}@smaplusasthahannas.id (atau NIS bila NISN kosong), password awal = NIS.'],
            ['Siswa wajib mengganti password saat login pertama (popup akan muncul).'],
            ['Baris dengan NIS yang sudah ada akan meng-update data, akun lama tidak di-reset passwordnya.'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']]],
        ];
    }
}

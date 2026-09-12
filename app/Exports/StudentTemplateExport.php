<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    /** @param Collection<int,array> $examples */
    public function __construct(private readonly Collection|array $examples = [])
    {
    }

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function headings(): array
    {
        return ['NIS', 'NISN', 'Nama', 'Kelas', 'Angkatan', 'Status'];
    }

    public function array(): array
    {
        $rows = $this->examples instanceof Collection ? $this->examples->all() : $this->examples;

        return $rows !== [] ? $rows : [
            ['25001', '0060000001', 'Contoh Siswa Satu', 'XI IPA 1', 'Angkatan 2025', 'active'],
            ['25002', '', 'Contoh Siswa Dua', 'XI IPS 1', 'Angkatan 2025', 'active'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $lastRow = count($this->array()) + 1;

                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

                // Validasi Status: active | graduated | inactive
                $validation = $event->sheet->getCell('F2')->getDataValidation();
                $validation->setType('list');
                $validation->setFormula1('"active,graduated,inactive"');
                $validation->setShowDropDown(false);

                for ($row = 2; $row <= max($lastRow, 50); $row++) {
                    $event->sheet->getCell("F{$row}")->setDataValidation(clone $validation);
                }
            },
        ];
    }
}

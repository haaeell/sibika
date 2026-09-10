<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TableExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    public function __construct(
        private readonly array $headings,
        private readonly Collection|array $rows,
    ) {
    }

    public function array(): array
    {
        return $this->rows instanceof Collection ? $this->rows->all() : $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings));
                $lastRow = count($this->array()) + 1;

                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
            },
        ];
    }
}

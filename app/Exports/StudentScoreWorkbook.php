<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentScoreWorkbook implements Export, WithMultipleSheets
{
    use Exportable;

    /** @param StudentScoreSheetExport[] $sheets */
    public function __construct(private readonly array $sheets = [])
    {
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}

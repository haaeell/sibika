<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentTemplateWorkbook implements Export, WithMultipleSheets
{
    use Exportable;

    /** @param array<int,array> $examples */
    public function __construct(private readonly array $examples = [])
    {
    }

    public function sheets(): array
    {
        return [
            new StudentTemplateExport($this->examples),
            new StudentTemplateGuideExport(),
        ];
    }
}

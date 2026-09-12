<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;

class StudentScoreSheetExport extends TableExport implements WithTitle
{
    public function __construct(
        private readonly string $sheetTitle,
        array $headings,
        $rows,
    ) {
        parent::__construct($headings, $rows);
    }

    public function title(): string
    {
        // Batas Excel: maks 31 karakter, tanpa [ ] : * ? / \
        return mb_substr(preg_replace('/[\\[\\]:*?\\/\\\\]/', '-', $this->sheetTitle), 0, 31);
    }
}

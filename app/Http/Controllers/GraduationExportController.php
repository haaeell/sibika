<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\Cohort;
use App\Services\EligibleRankingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class GraduationExportController extends Controller
{
    public function __invoke(Request $request, string $format, EligibleRankingService $ranking): BinaryFileResponse|Response
    {
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 404);
        $data = $request->validate(['cohort_id' => ['required', 'integer', 'exists:cohorts,id']]);
        $cohort = Cohort::findOrFail($data['cohort_id']);
        $rows = $ranking->forCohort($cohort->id);
        $filename = 'rekap-kelulusan-'.$cohort->id.'.'.$format;

        if ($format === 'pdf') {
            return Pdf::loadView('bk.graduation.pdf', compact('cohort', 'rows'))
                ->setPaper('A4', 'landscape')->download($filename);
        }

        return Excel::download(new TableExport(
            ['NIS', 'NISN', 'Nama', 'Kelas', 'Total Nilai', 'Nilai Rata-rata', 'Peringkat', 'Status'],
            $rows->map(fn (array $row) => [
                (string) $row['student']->nis, (string) $row['student']->nisn,
                $row['student']->name, $row['student']->schoolClass?->name ?? '-',
                $row['total_score'] ?? '-', $row['average'] ?? '-', $row['rank'] ?? '-', 'Lulus',
            ])
        ), $filename);
    }
}

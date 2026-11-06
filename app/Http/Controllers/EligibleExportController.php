<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\EligibleAnnouncement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class EligibleExportController extends Controller
{
    public function __invoke(Request $request, string $format): BinaryFileResponse|Response
    {
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 404);
        $data = $request->validate(['cohort_id' => ['required', 'integer', 'exists:cohorts,id']]);
        $announcement = EligibleAnnouncement::with('cohort')->where('cohort_id', $data['cohort_id'])->firstOrFail();
        $results = $announcement->results()->with('student.schoolClass')
            ->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END')->orderBy('rank')->orderBy('id')->get();
        $filename = 'hasil-eligible-angkatan-'.$announcement->cohort_id.'.'.$format;

        if ($format === 'pdf') {
            return Pdf::loadView('bk.eligible.pdf', compact('announcement', 'results'))
                ->setPaper('A4', 'landscape')->download($filename);
        }

        $rows = $results->map(fn ($result) => [
            $result->rank ?? '-', (string) $result->student->nis, $result->student->name,
            $result->student->schoolClass?->name ?? '-', $result->total_score ?? '-',
            $result->average ?? '-', ucfirst($result->category),
            $result->response ? ucfirst($result->response) : 'Belum memilih',
        ]);

        return Excel::download(new TableExport(
            ['Peringkat', 'NIS', 'Nama', 'Kelas', 'Total Nilai', 'Nilai Rata-rata', 'Status', 'Respons'], $rows
        ), $filename);
    }
}

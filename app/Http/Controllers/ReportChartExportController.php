<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportChartExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'charts' => ['required', 'array', 'min:1', 'max:40'],
            'charts.*.title' => ['required', 'string', 'max:100'],
            'charts.*.image' => ['required', 'string', 'max:1000'],
        ]);

        $charts = [];
        foreach ($validated['charts'] as $chart) {
            $binary = $this->decodeImage($chart['image']);
            abort_unless($binary !== null, 422, 'Gambar grafik tidak valid.');
            $charts[] = [
                'title' => trim($chart['title']) !== '' ? trim($chart['title']) : 'Grafik',
                'image' => 'data:image/png;base64,'.base64_encode($binary),
            ];
        }

        $filename = count($charts) === 1
            ? 'grafik-'.Str::slug(mb_substr($charts[0]['title'], 0, 40)).'-'.now()->format('Ymd-His').'.pdf'
            : 'grafik-laporan-'.now()->format('Ymd-His').'.pdf';

        return Pdf::loadView('exports.report-charts', [
            'title' => count($charts) === 1 ? $charts[0]['title'] : 'Grafik Laporan',
            'charts' => $charts,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape')->download($filename);
    }

    private function decodeImage(string $value): ?string
    {
        if (str_starts_with($value, 'data:image/')) {
            $parts = explode(',', $value, 2);
            if (count($parts) !== 2) {
                return null;
            }
            $value = $parts[1];
        }

        $binary = base64_decode($value, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        if (! str_starts_with($binary, "\x89PNG") && ! str_starts_with($binary, "\xFF\xD8\xFF")) {
            return null;
        }

        return $binary;
    }
}

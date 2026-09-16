@props(['id', 'averages' => []])

@php
    $trendLabels = collect(range(1, 5))->map(fn (int $semester) => 'Semester '.$semester)->values();
    $trendValues = collect(range(1, 5))->map(fn (int $semester) => isset($averages[$semester]) && ! is_null($averages[$semester]) ? (float) $averages[$semester] : null)->values();
    $hasTrend = $trendValues->filter(fn ($value) => ! is_null($value))->isNotEmpty();
@endphp

<details class="-mx-4 border-y border-slate-200 bg-white sm:mx-0 sm:rounded-2xl sm:border" data-average-trend-details>
    <summary class="cursor-pointer px-4 py-4 sm:px-5">
        <span class="block text-base font-semibold text-slate-900">Tren Rata-rata Semester 1-5</span>
        <span class="mt-1 block text-sm text-slate-500">Buka untuk melihat perkembangan nilai.</span>
    </summary>
    <div class="border-t border-slate-100 p-4 sm:p-5">
        @if (! $hasTrend)
            <x-empty-state icon="fa-solid fa-chart-line" title="Belum ada rata-rata" description="Grafik tren muncul setelah ada nilai yang diisi." />
        @else
            <div class="h-64"><canvas id="{{ $id }}"></canvas></div>
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var canvas = document.getElementById(@json($id));
                    if (!canvas) return;
                    var render = function () {
                        if (canvas.dataset.rendered) return;
                        canvas.dataset.rendered = '1';
                        new window.Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: @json($trendLabels),
                            datasets: [{
                                label: 'Rata-rata',
                                data: @json($trendValues),
                                borderColor: '#1e3a8a',
                                backgroundColor: 'rgba(30, 58, 138, 0.08)',
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#1e3a8a',
                                pointRadius: 5,
                                spanGaps: true,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { min: 0, max: 100, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } },
                            },
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                            },
                        },
                        });
                    };
                    var details = canvas.closest('details');
                    if (details) {
                        details.addEventListener('toggle', function () {
                            if (details.open) render();
                        });
                    } else {
                        render();
                    }
                });
            </script>
        @endpush
        @endif
    </div>
</details>

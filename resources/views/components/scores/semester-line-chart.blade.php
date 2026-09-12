@props(['id', 'title' => 'Grafik Nilai', 'settings', 'scores'])

@php
    $chartLabels = $settings->map(fn ($setting) => $setting->subject?->name ?? '-')->values();
    $chartValues = $settings->map(fn ($setting) => filled($scores->get($setting->subject_id)?->score) ? (float) $scores->get($setting->subject_id)->score : null)->values();
    $chartAverage = $chartValues->filter(fn ($value) => ! is_null($value));
    $chartAverage = $chartAverage->isEmpty() ? null : round($chartAverage->avg(), 2);
@endphp

<x-card :title="$title" description="Tren nilai per mata pelajaran semester ini.">
    @if ($chartValues->filter(fn ($value) => ! is_null($value))->isEmpty())
        <x-empty-state icon="fa-solid fa-chart-line" title="Belum ada nilai" description="Grafik muncul setelah ada nilai yang diisi." />
    @else
        <div class="h-64"><canvas id="{{ $id }}"></canvas></div>
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var canvas = document.getElementById(@json($id));
                    if (!canvas || canvas.dataset.rendered) return;
                    canvas.dataset.rendered = '1';
                    new window.Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [
                                {
                                    label: 'Nilai',
                                    data: @json($chartValues),
                                    borderColor: '#1e3a8a',
                                    backgroundColor: 'rgba(30, 58, 138, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    pointBackgroundColor: '#1e3a8a',
                                    pointRadius: 4,
                                    spanGaps: true,
                                },
                                {
                                    label: 'Rata-rata ({{ $chartAverage }})',
                                    data: @json($chartLabels->map(fn () => $chartAverage)->values()),
                                    borderColor: '#059669',
                                    borderDash: [6, 4],
                                    pointRadius: 0,
                                    fill: false,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: false, min: 0, max: 100, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } },
                            },
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                            },
                        },
                    });
                });
            </script>
        @endpush
    @endif
</x-card>

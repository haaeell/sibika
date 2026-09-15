@component('layouts.app', ['title' => 'Laporan Nilai'])
    <x-page-header title="Laporan Nilai Siswa" description="Analisis rata-rata, ranking, ketuntasan, dan sebaran nilai seluruh siswa.">
        <x-slot:actions>
            <x-button id="score-report-export-btn" variant="secondary"><i class="fa-solid fa-file-excel"></i> Export Excel</x-button>
            <x-button type="button" data-export-all-charts variant="secondary" title="Unduh semua grafik (PDF)"><i class="fa-solid fa-file-pdf"></i> Grafik PDF</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('bk.student-scores.report') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-form.select name="academic_year_id" label="Tahun Ajaran" icon="fa-solid fa-calendar-days" class="select2" data-placeholder="Semua tahun ajaran">
                <option value="">Semua tahun ajaran</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(($filters['academic_year_id'] ?? null) == $academicYear->id)>{{ $academicYear->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="class_id" label="Kelas" icon="fa-solid fa-school" class="select2" data-placeholder="Semua kelas">
                <option value="">Semua kelas</option>
                @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}" @selected(($filters['class_id'] ?? null) == $schoolClass->id)>{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="major_id" label="Jurusan" icon="fa-solid fa-code-branch" class="select2" data-placeholder="Semua jurusan">
                <option value="">Semua jurusan</option>
                @foreach ($majors as $major)
                    <option value="{{ $major->id }}" @selected(($filters['major_id'] ?? null) == $major->id)>{{ $major->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="status" label="Status Siswa" icon="fa-solid fa-user-check">
                <option value="">Semua status</option>
                <option value="active" @selected(($filters['status'] ?? null) === 'active')>Aktif</option>
                <option value="graduated" @selected(($filters['status'] ?? null) === 'graduated')>Lulus</option>
                <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Nonaktif</option>
            </x-form.select>
            <x-form.select name="completeness" label="Kelengkapan Nilai" icon="fa-solid fa-list-check">
                <option value="">Semua kelengkapan</option>
                <option value="complete" @selected(($filters['completeness'] ?? null) === 'complete')>Ada rata-rata</option>
                <option value="incomplete" @selected(($filters['completeness'] ?? null) === 'incomplete')>Belum ada rata-rata</option>
            </x-form.select>
            <div class="flex gap-2 sm:col-span-2 xl:col-span-5 xl:justify-end">
                <x-button variant="secondary" :href="route('bk.student-scores.report')" class="flex-1 sm:flex-none"><i class="fa-solid fa-rotate-left"></i> Reset</x-button>
                <x-button type="submit" class="flex-1 sm:flex-none"><i class="fa-solid fa-filter"></i> Terapkan Filter</x-button>
            </div>
        </form>
    </x-card>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 2xl:grid-cols-7">
        @foreach ([
            ['label' => 'Total Siswa', 'value' => $summary['total'], 'icon' => 'fa-users', 'class' => 'bg-blue-50 text-blue-800'],
            ['label' => 'Rata-rata Keseluruhan', 'value' => is_null($summary['average']) ? '-' : number_format($summary['average'], 2), 'icon' => 'fa-chart-line', 'class' => 'bg-indigo-50 text-indigo-700'],
            ['label' => 'Nilai Tertinggi', 'value' => is_null($summary['highest']) ? '-' : number_format($summary['highest'], 2), 'icon' => 'fa-arrow-trend-up', 'class' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Nilai Terendah', 'value' => is_null($summary['lowest']) ? '-' : number_format($summary['lowest'], 2), 'icon' => 'fa-arrow-trend-down', 'class' => 'bg-rose-50 text-rose-700'],
            ['label' => 'Ada Rata-rata', 'value' => $summary['complete'], 'icon' => 'fa-circle-check', 'class' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Belum Ada Rata-rata', 'value' => $summary['incomplete'], 'icon' => 'fa-circle-exclamation', 'class' => 'bg-amber-50 text-amber-700'],
            ['label' => 'Tanpa Nilai Sama Sekali', 'value' => $summary['without_scores'], 'icon' => 'fa-file-circle-xmark', 'class' => 'bg-slate-100 text-slate-600'],
        ] as $stat)
            <x-card class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold leading-4 text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $stat['class'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                </div>
            </x-card>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-5">
        <x-card title="Ketuntasan Nilai" description="Perbandingan siswa yang sudah dan belum memiliki rata-rata." class="xl:col-span-2">
            <div class="mx-auto h-72 max-w-md"><canvas id="completion-chart"></canvas></div>
        </x-card>
        <x-card title="Rata-rata per Semester" description="Rata-rata nilai seluruh siswa hasil filter per semester." class="xl:col-span-3">
            <div class="h-72"><canvas id="semester-averages-chart"></canvas></div>
        </x-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card title="Rata-rata per Kelas" description="Perbandingan rata-rata nilai antar kelas.">
            <div class="h-72"><canvas id="class-averages-chart"></canvas></div>
        </x-card>
        <x-card title="Rata-rata per Jurusan" description="Perbandingan rata-rata nilai antar jurusan.">
            <div class="h-72"><canvas id="major-averages-chart"></canvas></div>
        </x-card>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <x-card title="Distribusi Nilai" description="Pengelompokan rata-rata siswa ke rentang nilai.">
            <div class="h-64"><canvas id="distribution-chart"></canvas></div>
        </x-card>
        <x-card title="8 Siswa Teratas" description="Siswa dengan rata-rata tertinggi.">
            <div class="h-64"><canvas id="top-students-chart"></canvas></div>
        </x-card>
        <x-card title="8 Siswa Terbawah" description="Siswa dengan rata-rata terendah — prioritas pembinaan.">
            <div class="h-64"><canvas id="bottom-students-chart"></canvas></div>
        </x-card>
    </div>

    <x-card title="Rata-rata per Mata Pelajaran" description="Sepuluh mapel dengan rata-rata tertinggi lintas siswa terfilter.">
        <div class="h-72"><canvas id="subjects-chart"></canvas></div>
    </x-card>

    <x-card title="Sorotan untuk BK" description="Ringkasan cepat yang perlu diperhatikan berdasarkan filter aktif.">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Kelas rata-rata tertinggi', 'value' => $insights['top_class'], 'icon' => 'fa-trophy'],
                ['label' => 'Kelas rata-rata terendah', 'value' => $insights['lowest_class'], 'icon' => 'fa-arrow-trend-down'],
                ['label' => 'Belum ada rata-rata', 'value' => $insights['incomplete'].' siswa', 'icon' => 'fa-circle-exclamation'],
                ['label' => 'Tanpa nilai sama sekali', 'value' => $insights['without_scores'].' siswa', 'icon' => 'fa-file-circle-xmark'],
                ['label' => 'Mapel rata-rata terendah', 'value' => $insights['lowest_subject'], 'icon' => 'fa-book-open'],
            ] as $insight)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <i class="fa-solid {{ $insight['icon'] }} text-blue-800"></i>
                    <p class="mt-3 text-xs font-semibold text-slate-500">{{ $insight['label'] }}</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $insight['value'] }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card title="Rekap Detail Siswa" description="Urutkan dan cari siswa untuk menentukan prioritas pembinaan.">
        <div class="overflow-x-auto">
            <table id="score-report-table" class="w-full min-w-[1400px] text-left text-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Smt 1</th>
                        <th>Smt 2</th>
                        <th>Smt 3</th>
                        <th>Smt 4</th>
                        <th>Smt 5</th>
                        <th>Rata-rata</th>
                        <th>Rank Kelas</th>
                        <th>Rank Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $row)
                        @php($student = $row['student'])
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><p class="font-bold text-slate-900">{{ $student->name }}</p><p class="text-xs text-slate-500">{{ $student->nis }}</p></td>
                            <td>{{ $student->schoolClass?->name ?? '-' }}</td>
                            <td>{{ $student->schoolClass?->major?->name ?? '-' }}</td>
                            @foreach (range(1, 5) as $semester)
                                <td>{{ is_null($row['semesters'][$semester]) ? '-' : number_format($row['semesters'][$semester], 2) }}</td>
                            @endforeach
                            <td data-order="{{ $row['average'] ?? -1 }}"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ is_null($row['average']) ? 'bg-slate-100 text-slate-600' : ($row['average'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($row['average'] >= 70 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')) }}">{{ is_null($row['average']) ? '-' : number_format($row['average'], 2) }}</span></td>
                            <td>{{ $row['class_rank'] ?? '-' }}</td>
                            <td>{{ $row['major_rank'] ?? '-' }}</td>
                            <td><a href="{{ route('bk.student-scores.show', $student) }}" class="btn-icon" aria-label="Lihat nilai {{ $student->name }}"><i class="fa-solid fa-eye"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const charts = @json($charts);
                const colors = ['#1e3a8a', '#059669', '#d97706', '#e11d48', '#0284c7', '#7c3aed', '#475569', '#ea580c'];
                const renderChart = function (id, data, type = 'doughnut', options = {}) {
                    const canvas = document.getElementById(id);
                    if (!canvas) return;
                    new window.Chart(canvas, {
                        type,
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.values,
                                backgroundColor: colors,
                                borderWidth: type === 'bar' ? 0 : 3,
                                borderColor: '#ffffff',
                                borderRadius: type === 'bar' ? 7 : 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: type !== 'bar', position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                                tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw}${options.percentage ? '%' : ''}` } },
                            },
                            scales: type === 'bar' ? {
                                x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                                y: { grid: { display: false } },
                            } : undefined,
                            indexAxis: options.horizontal ? 'y' : 'x',
                        },
                    });
                };

                renderChart('completion-chart', charts.completion);
                renderChart('semester-averages-chart', charts.semester_averages, 'bar');
                renderChart('class-averages-chart', charts.class_averages, 'bar', { horizontal: true });
                renderChart('major-averages-chart', charts.major_averages, 'bar');
                renderChart('distribution-chart', charts.distribution, 'bar');
                renderChart('top-students-chart', charts.top_students, 'bar', { horizontal: true });
                renderChart('bottom-students-chart', charts.bottom_students, 'bar', { horizontal: true });
                renderChart('subjects-chart', charts.subjects, 'bar', { horizontal: true });

                window.initDataTable('#score-report-table', {
                    pageLength: 25,
                    order: [[9, 'desc'], [1, 'asc']],
                    columnDefs: [{ orderable: false, targets: [12] }],
                });

                // Export mengikuti filter laporan aktif.
                window.$('#score-report-export-btn').on('click', function () {
                    var params = new URLSearchParams(window.location.search);
                    params.delete('completeness');
                    window.location = @json(route('bk.student-scores.export')) + (params.toString() ? '?' + params.toString() : '');
                });
            });
        </script>
    @endpush
    @include('bk.reports._chart-download')
@endcomponent

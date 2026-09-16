@component('layouts.app', ['title' => 'Laporan Biodata'])
    <x-page-header title="Laporan Biodata Siswa" description="Analisis kelengkapan, kesehatan, rencana kampus, aktivitas, dan prestasi seluruh siswa.">
        <x-slot:actions>
            <x-export-buttons resource="biodata" />
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('bk.biodata.report') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-form.select name="class_id" label="Kelas" icon="fa-solid fa-school" class="select2" data-placeholder="Semua kelas">
                <option value="">Semua kelas</option>
                @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}" @selected(($filters['class_id'] ?? null) == $schoolClass->id)>{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="cohort_id" label="Angkatan" icon="fa-solid fa-layer-group" class="select2" data-placeholder="Semua angkatan">
                <option value="">Semua angkatan</option>
                @foreach ($cohorts as $cohort)
                    <option value="{{ $cohort->id }}" @selected(($filters['cohort_id'] ?? null) == $cohort->id)>{{ $cohort->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="status" label="Status Siswa" class="select2" icon="fa-solid fa-user-check">
                <option value="">Semua status</option>
                <option value="active" @selected(($filters['status'] ?? null) === 'active')>Aktif</option>
                <option value="graduated" @selected(($filters['status'] ?? null) === 'graduated')>Lulus</option>
                <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Nonaktif</option>
            </x-form.select>
            <x-form.select name="completeness" label="Kelengkapan" class="select2" icon="fa-solid fa-list-check">
                <option value="">Semua kelengkapan</option>
                <option value="complete" @selected(($filters['completeness'] ?? null) === 'complete')>Lengkap 100%</option>
                <option value="incomplete" @selected(($filters['completeness'] ?? null) === 'incomplete')>Belum lengkap</option>
            </x-form.select>
            <x-form.select name="mcu_status" label="Status MCU" class="select2" icon="fa-solid fa-file-medical">
                <option value="">Semua status MCU</option>
                <option value="sudah" @selected(($filters['mcu_status'] ?? null) === 'sudah')>Sudah</option>
                <option value="proses" @selected(($filters['mcu_status'] ?? null) === 'proses')>Proses</option>
                <option value="belum" @selected(($filters['mcu_status'] ?? null) === 'belum')>Belum</option>
            </x-form.select>
            <div class="flex gap-2 sm:col-span-2 xl:col-span-5 xl:justify-end">
                <x-button variant="secondary" :href="route('bk.biodata.report')" class="flex-1 sm:flex-none"><i class="fa-solid fa-rotate-left"></i> Reset</x-button>
                <x-button type="submit" class="flex-1 sm:flex-none"><i class="fa-solid fa-filter"></i> Terapkan Filter</x-button>
            </div>
        </form>
    </x-card>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 2xl:grid-cols-7">
        @foreach ([
            ['label' => 'Total Siswa', 'value' => $summary['total'], 'icon' => 'fa-users', 'class' => 'bg-blue-50 text-blue-800'],
            ['label' => 'Biodata Lengkap', 'value' => $summary['complete'], 'icon' => 'fa-circle-check', 'class' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Belum Lengkap', 'value' => $summary['incomplete'], 'icon' => 'fa-circle-exclamation', 'class' => 'bg-rose-50 text-rose-700'],
            ['label' => 'Rata-rata Progress', 'value' => $summary['average_progress'].'%', 'icon' => 'fa-chart-line', 'class' => 'bg-indigo-50 text-indigo-700'],
            ['label' => 'Punya Sertifikat', 'value' => $summary['with_certificates'], 'icon' => 'fa-award', 'class' => 'bg-amber-50 text-amber-700'],
            ['label' => 'MCU Selesai', 'value' => $summary['mcu_complete'], 'icon' => 'fa-file-circle-check', 'class' => 'bg-sky-50 text-sky-700'],
            ['label' => 'Riwayat Kesehatan', 'value' => $summary['medical_attention'], 'icon' => 'fa-heart-pulse', 'class' => 'bg-orange-50 text-orange-700'],
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
        <x-card title="Status Kelengkapan" description="Perbandingan biodata lengkap dan belum lengkap." class="xl:col-span-2">
            <div class="mx-auto h-72 max-w-md"><canvas id="completion-chart"></canvas></div>
        </x-card>
        <x-card title="Rata-rata Kelengkapan per Kelas" description="Persentase rata-rata pengisian biodata pada setiap kelas." class="xl:col-span-3">
            <div class="h-72"><canvas id="class-progress-chart"></canvas></div>
        </x-card>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['id' => 'gender-chart', 'title' => 'Jenis Kelamin', 'description' => 'Distribusi siswa berdasarkan jenis kelamin.'],
            ['id' => 'mcu-chart', 'title' => 'Status MCU Mandiri', 'description' => 'Status pelaksanaan medical check-up siswa.'],
            ['id' => 'health-chart', 'title' => 'Riwayat Kesehatan', 'description' => 'Siswa yang memerlukan perhatian berdasarkan isian kesehatan.'],
            ['id' => 'achievement-chart', 'title' => 'Prestasi Sekolah', 'description' => 'Perbandingan siswa yang memiliki dan tidak memiliki prestasi.'],
            ['id' => 'achievement-type-chart', 'title' => 'Jenis Prestasi', 'description' => 'Akademik dan non akademik.'],
            ['id' => 'achievement-level-chart', 'title' => 'Tingkat Prestasi', 'description' => 'Kab/kota, provinsi, nasional, internasional.'],
            ['id' => 'organization-chart', 'title' => 'Keikutsertaan Organisasi', 'description' => 'Jawaban Ya / Tidak mengikuti organisasi.'],
            ['id' => 'organization-level-chart', 'title' => 'Tingkat Organisasi/Ekskul', 'description' => 'Tingkat organisasi atau ekskul siswa.'],
            ['id' => 'height-chart', 'title' => 'Distribusi Tinggi Badan', 'description' => 'Pengelompokan tinggi badan siswa.'],
            ['id' => 'weight-chart', 'title' => 'Distribusi Berat Badan', 'description' => 'Pengelompokan berat badan siswa.'],
        ] as $chart)
            <x-card :title="$chart['title']" :description="$chart['description']">
                <div class="h-64"><canvas id="{{ $chart['id'] }}"></canvas></div>
            </x-card>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card title="Sebaran Provinsi" description="Delapan provinsi dengan jumlah domisili siswa terbanyak.">
            <div class="h-72"><canvas id="province-chart"></canvas></div>
        </x-card>
        <x-card title="Sebaran Kota / Kabupaten" description="Delapan kota atau kabupaten dengan jumlah domisili siswa terbanyak.">
            <div class="h-72"><canvas id="city-chart"></canvas></div>
        </x-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Pilihan Kampus Pertama" description="Delapan kampus pilihan pertama yang paling banyak diminati.">
            <div class="h-72"><canvas id="campus-choice-1-chart"></canvas></div>
        </x-card>
        <x-card title="Pilihan Kampus Kedua" description="Delapan kampus pilihan kedua yang paling banyak diminati.">
            <div class="h-72"><canvas id="campus-choice-2-chart"></canvas></div>
        </x-card>
        <x-card title="Pilihan Kampus Ketiga" description="Delapan kampus pilihan ketiga yang paling banyak diminati.">
            <div class="h-72"><canvas id="campus-choice-3-chart"></canvas></div>
        </x-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Jurusan Pilihan Pertama" description="Delapan jurusan pilihan pertama yang paling banyak diminati.">
            <div class="h-72"><canvas id="major-choice-1-chart"></canvas></div>
        </x-card>
        <x-card title="Jurusan Pilihan Kedua" description="Delapan jurusan pilihan kedua yang paling banyak diminati.">
            <div class="h-72"><canvas id="major-choice-2-chart"></canvas></div>
        </x-card>
        <x-card title="Jurusan Pilihan Ketiga" description="Delapan jurusan pilihan ketiga yang paling banyak diminati.">
            <div class="h-72"><canvas id="major-choice-3-chart"></canvas></div>
        </x-card>
    </div>

    <x-card title="Sorotan untuk BK" description="Ringkasan cepat yang perlu diperhatikan berdasarkan filter aktif.">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Kelas progress terendah', 'value' => $insights['lowest_class'], 'icon' => 'fa-arrow-trend-down'],
                ['label' => 'Kampus terfavorit', 'value' => $insights['top_campus'], 'icon' => 'fa-building-columns'],
                ['label' => 'Perlu perhatian kesehatan', 'value' => $insights['medical_attention'].' siswa', 'icon' => 'fa-notes-medical'],
                ['label' => 'MCU belum selesai', 'value' => $insights['mcu_pending'].' siswa', 'icon' => 'fa-stethoscope'],
                ['label' => 'Belum punya sertifikat', 'value' => $insights['without_certificates'].' siswa', 'icon' => 'fa-medal'],
            ] as $insight)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <i class="fa-solid {{ $insight['icon'] }} text-blue-800"></i>
                    <p class="mt-3 text-xs font-semibold text-slate-500">{{ $insight['label'] }}</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $insight['value'] }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card title="Rekap Detail Siswa" description="Urutkan dan cari siswa untuk menentukan prioritas tindak lanjut.">
        <div class="overflow-x-auto">
            <table id="biodata-report-table" class="w-full min-w-[1200px] text-left text-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Angkatan</th>
                        <th>Progress</th>
                        <th>MCU</th>
                        <th>Biodata Orang Tua</th>
                        <th>Riwayat Kesehatan</th>
                        <th>Pilihan Kampus</th>
                        <th>TKA</th>
                        <th>Sertifikat</th>
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
                            <td>{{ $student->cohort?->name ?? '-' }}</td>
                            <td data-order="{{ $row['progress'] }}"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $row['progress'] === 100 ? 'bg-emerald-50 text-emerald-700' : ($row['progress'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">{{ $row['progress'] }}%</span></td>
                            <td>{{ ['sudah' => 'Sudah', 'proses' => 'Proses', 'belum' => 'Belum'][$student->profile?->mcu_status] ?? '-' }}{{ $student->profile?->mcu_status === 'sudah' ? ' - '.$student->profile?->mcu_count.' kali, terakhir '.$student->profile?->mcu_last_date?->format('d M Y') : '' }}</td>
                            <td><span class="block max-w-xs whitespace-normal text-xs leading-5">Ayah: {{ $student->profile?->parent_father_name ?? '-' }} ({{ $student->profile?->parent_father_occupation ?? '-' }})<br>Ibu: {{ $student->profile?->parent_mother_name ?? '-' }} ({{ $student->profile?->parent_mother_occupation ?? '-' }})<br>Telp: {{ $student->profile?->parent_phone ?? '-' }}</span></td>
                            <td><span class="block max-w-xs truncate" title="{{ $student->profile?->medical_history }}">{{ $student->profile?->medical_history ?? '-' }}</span></td>
                            <td><span class="block max-w-xs whitespace-normal text-xs leading-5">1. {{ ($student->profile?->universityChoice1?->name ?? '-').($student->profile?->university_major_choice_1 ? ' - '.$student->profile->university_major_choice_1 : '') }}<br>2. {{ ($student->profile?->universityChoice2?->name ?? '-').($student->profile?->university_major_choice_2 ? ' - '.$student->profile->university_major_choice_2 : '') }}<br>3. {{ ($student->profile?->universityChoice3?->name ?? '-').($student->profile?->university_major_choice_3 ? ' - '.$student->profile->university_major_choice_3 : '') }}</span></td>
                            <td><span class="block max-w-xs whitespace-normal text-xs leading-5">{{ $row['tka'] }}</span></td>
                            <td>{{ $row['certificate_count'] }}</td>
                            <td><a href="{{ route('bk.students.biodata.show', $student) }}" class="btn-icon" aria-label="Lihat biodata {{ $student->name }}"><i class="fa-solid fa-eye"></i></a></td>
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
                                tooltip: { callbacks: { label: (context) => {
                                    if (options.percentage) return `${context.label}: ${context.raw}%`;
                                    const total = context.dataset.data.reduce((sum, value) => sum + Number(value || 0), 0);
                                    const percent = total ? Math.round((Number(context.raw || 0) / total) * 100) : 0;
                                    return `${context.label}: ${context.raw} siswa (${percent}%)`;
                                } } },
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
                renderChart('class-progress-chart', charts.class_progress, 'bar', { percentage: true });
                renderChart('gender-chart', charts.gender);
                renderChart('mcu-chart', charts.mcu);
                renderChart('health-chart', charts.health);
                renderChart('achievement-chart', charts.achievement);
                renderChart('achievement-type-chart', charts.achievement_type, 'bar');
                renderChart('achievement-level-chart', charts.achievement_level, 'bar');
                renderChart('organization-chart', charts.organization);
                renderChart('organization-level-chart', charts.organization_level, 'bar');
                renderChart('height-chart', charts.height, 'bar');
                renderChart('weight-chart', charts.weight, 'bar');
                renderChart('province-chart', charts.province, 'bar', { horizontal: true });
                renderChart('city-chart', charts.city, 'bar', { horizontal: true });
                renderChart('campus-choice-1-chart', charts.campus_choice_1, 'bar', { horizontal: true });
                renderChart('campus-choice-2-chart', charts.campus_choice_2, 'bar', { horizontal: true });
                renderChart('campus-choice-3-chart', charts.campus_choice_3, 'bar', { horizontal: true });
                renderChart('major-choice-1-chart', charts.major_choice_1, 'bar', { horizontal: true });
                renderChart('major-choice-2-chart', charts.major_choice_2, 'bar', { horizontal: true });
                renderChart('major-choice-3-chart', charts.major_choice_3, 'bar', { horizontal: true });

                window.initDataTable('#biodata-report-table', {
                    pageLength: 25,
                    order: [[4, 'asc'], [1, 'asc']],
                    columnDefs: [{ orderable: false, targets: [6, 7, 8, 10] }],
                });
            });
        </script>
    @endpush
@endcomponent

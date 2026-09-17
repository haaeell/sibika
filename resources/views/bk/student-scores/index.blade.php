@component('layouts.app', ['title' => 'Data Nilai'])
    <x-page-header title="Data Nilai" description="Rekap nilai semester 1 sampai 5 per siswa.">
        <x-slot:actions>
            @unless ($isMonitoring ?? false)<x-button id="score-export-btn" variant="secondary"><i class="fa-solid fa-file-excel"></i> Export Excel</x-button>@endunless
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="text-sm font-bold text-slate-500">Filter:</span>
            <select id="score-year-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua tahun ajaran">
                <option value="">Semua tahun ajaran</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                @endforeach
            </select>
            <select id="score-class-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua kelas">
                <option value="">Semua kelas</option>
                @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
                @endforeach
            </select>
            <select id="score-major-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua jurusan">
                <option value="">Semua jurusan</option>
                @foreach ($majors as $major)
                    <option value="{{ $major->id }}">{{ $major->name }}</option>
                @endforeach
            </select>
            <select id="score-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status siswa">
                <option value="">Semua status siswa</option>
                <option value="active">Aktif</option>
                <option value="graduated">Lulus</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table id="student-score-table" class="w-full min-w-[1320px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Rata-rata</th>
                        <th>Ranking Kelas</th>
                        <th>Ranking Jurusan</th>
                        <th>Ranking Angkatan</th>
                        <th>Semester 1</th>
                        <th>Semester 2</th>
                        <th>Semester 3</th>
                        <th>Semester 4</th>
                        <th>Semester 5</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#student-score-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(($isMonitoring ?? false) ? route('wali-kelas.scores.data') : route('bk.student-scores.data')),
                        data: function (params) {
                            params.academic_year_id = window.$('#score-year-filter').val();
                            params.class_id = window.$('#score-class-filter').val();
                            params.major_id = window.$('#score-major-filter').val();
                            params.status = window.$('#score-status-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'student', name: 'name' },
                        { data: 'class_name', name: 'schoolClass.name', orderable: false, searchable: false },
                        { data: 'major_name', name: 'schoolClass.major.name', orderable: false, searchable: false },
                        { data: 'overall_average', name: 'overall_average', orderable: false, searchable: false },
                        { data: 'class_rank', name: 'class_rank', orderable: false, searchable: false },
                        { data: 'major_rank', name: 'major_rank', orderable: false, searchable: false },
                        { data: 'cohort_rank', name: 'cohort_rank', orderable: false, searchable: false },
                        { data: 'semester_1', name: 'semester_1', orderable: false, searchable: false },
                        { data: 'semester_2', name: 'semester_2', orderable: false, searchable: false },
                        { data: 'semester_3', name: 'semester_3', orderable: false, searchable: false },
                        { data: 'semester_4', name: 'semester_4', orderable: false, searchable: false },
                        { data: 'semester_5', name: 'semester_5', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#student-score-table').DataTable().ajax.reload();
                });

                // Export mengikuti filter aktif (jurusan, kelas, tahun ajaran, status).
                window.$('#score-export-btn').on('click', function () {
                    var $btn = window.$(this);
                    window.setButtonLoading($btn, true, 'Menyiapkan...');

                    var params = new URLSearchParams();
                    [
                        ['academic_year_id', '#score-year-filter'],
                        ['class_id', '#score-class-filter'],
                        ['major_id', '#score-major-filter'],
                        ['status', '#score-status-filter'],
                    ].forEach(function ([key, selector]) {
                        (window.$(selector).val() || []).forEach(function (val) {
                            if (val !== '' && val !== null) params.append(key + '[]', val);
                        });
                    });

                    window.location = @json(route('bk.student-scores.export')) + '?' + params.toString();
                    setTimeout(function () {
                        window.setButtonLoading($btn, false);
                    }, 3000);
                });
            });
        </script>
    @endpush
@endcomponent

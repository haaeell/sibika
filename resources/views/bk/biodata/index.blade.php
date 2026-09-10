@component('layouts.app', ['title' => 'Biodata Siswa'])
    <x-page-header title="Biodata Siswa" description="Monitor kelengkapan biodata seluruh siswa." />

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="biodata-class-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua kelas">
                    @foreach ($schoolClasses as $schoolClass)
                        <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
                    @endforeach
                </select>
                <select id="biodata-cohort-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua angkatan">
                    @foreach ($cohorts as $cohort)
                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-export-buttons resource="students" />
        </div>
        <div class="overflow-x-auto">
            <table id="biodata-table" class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">NIS</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Kelas</th>
                        <th class="px-4 py-3 font-bold">Angkatan</th>
                        <th class="px-4 py-3 font-bold">Kelengkapan</th>
                        <th class="px-4 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#biodata-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.biodata.data')),
                        data: function (params) {
                            params.class_id = window.$('#biodata-class-filter').val();
                            params.cohort_id = window.$('#biodata-cohort-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'nis', name: 'nis' },
                        { data: 'name', name: 'name' },
                        { data: 'class_name', name: 'schoolClass.name' },
                        { data: 'cohort_name', name: 'cohort.name' },
                        { data: 'progress', name: 'progress', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[2, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#biodata-table').DataTable().ajax.reload();
                });
            });
        </script>
    @endpush
@endcomponent

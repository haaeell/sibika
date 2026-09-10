@component('layouts.app', ['title' => 'Guru'])
    <x-page-header title="Guru" description="Kelola data guru dan pengajar.">
        <x-slot:actions>
            <x-button :href="route('bk.teachers.create')"><i class="fa-solid fa-plus"></i> Tambah Guru</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="teacher-subject-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua mata pelajaran">
                    <option value="">Semua mata pelajaran</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
                <select id="teacher-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="teachers" />
        </div>
        <div class="overflow-x-auto">
            <table id="teacher-table" class="w-full min-w-[980px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">NIP / Kode</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Kontak</th>
                        <th class="px-4 py-3 font-bold">Mapel</th>
                        <th class="px-4 py-3 font-bold">Status</th>
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
                window.initDataTable('#teacher-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.teachers.data')),
                        data: function (params) {
                            params.subject_id = window.$('#teacher-subject-filter').val();
                            params.status = window.$('#teacher-status-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'code', name: 'code' },
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email', render: (data, type, row) => `${data}<br><span class="text-xs text-slate-400">${row.phone || '-'}</span>` },
                        { data: 'subjects_list', name: 'subjects.name', defaultContent: '-' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[2, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#teacher-table').DataTable().ajax.reload();
                });
            });
        </script>
    @endpush
@endcomponent

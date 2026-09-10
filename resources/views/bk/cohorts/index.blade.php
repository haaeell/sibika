@component('layouts.app', ['title' => 'Angkatan'])
    <x-page-header title="Angkatan" description="Kelola data angkatan siswa.">
        <x-slot:actions>
            <x-button :href="route('bk.cohorts.create')">
                <i class="fa-solid fa-plus"></i>
                Tambah Angkatan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="cohort-status-filter" data-table-filter class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="graduated">Lulus</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="cohorts" />
        </div>
        <div class="overflow-x-auto">
            <table id="cohort-table" class="w-full min-w-[680px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Periode</th>
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
                window.initDataTable('#cohort-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.cohorts.data')),
                        data: function (params) {
                            params.status = window.$('#cohort-status-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'period', name: 'entry_year' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#cohort-table').DataTable().ajax.reload();
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();
                    const form = this;
                    window.confirmAction({
                        title: 'Hapus angkatan?',
                        text: 'Data yang dihapus tidak bisa dikembalikan.',
                        confirmText: 'Ya, hapus',
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        </script>
    @endpush
@endcomponent

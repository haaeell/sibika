@component('layouts.app', ['title' => 'Tahun Ajaran'])
    <x-page-header title="Tahun Ajaran" description="Kelola periode akademik dan semester aktif.">
        <x-slot:actions>
            <x-button :href="route('bk.academic-years.create')">
                <i class="fa-solid fa-plus"></i>
                Tambah Tahun Ajaran
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="academic-year-semester-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua semester">
                    <option value="">Semua semester</option>
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
                <select id="academic-year-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="academic-years" />
        </div>
        <div class="overflow-x-auto">
            <table id="academic-year-table" class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Periode</th>
                        <th class="px-4 py-3 font-bold">Semester</th>
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
                window.initDataTable('#academic-year-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.academic-years.data')),
                        data: function (params) {
                            params.semester = window.$('#academic-year-semester-filter').val();
                            params.is_active = window.$('#academic-year-status-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'period', name: 'start_year' },
                        { data: 'semester', name: 'semester' },
                        { data: 'is_active', name: 'is_active' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[4, 'desc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#academic-year-table').DataTable().ajax.reload();
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();

                    window.confirmAction({
                        title: 'Hapus tahun ajaran?',
                        text: 'Data yang dihapus tidak bisa dikembalikan.',
                        confirmText: 'Ya, hapus',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endcomponent

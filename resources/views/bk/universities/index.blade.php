@component('layouts.app', ['title' => 'Master Kampus'])
    <x-page-header title="Master Kampus" description="Kelola daftar perguruan tinggi untuk pilihan kampus siswa.">
        <x-slot:actions><x-button :href="route('bk.universities.create')"><i class="fa-solid fa-plus"></i> Tambah Kampus</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="university-type-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua jenis">
                    <option value="">Semua jenis</option>
                    <option value="negeri">Negeri</option>
                    <option value="swasta">Swasta</option>
                    <option value="kedinasan">Kedinasan</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <select id="university-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="universities" />
        </div>
        <div class="overflow-x-auto">
            <table id="university-table" class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr>
                    <th class="px-4 py-3 font-bold">No</th><th class="px-4 py-3 font-bold">Singkatan</th><th class="px-4 py-3 font-bold">Nama Kampus</th><th class="px-4 py-3 font-bold">Jenis</th><th class="px-4 py-3 font-bold">Dipilih Siswa</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-card>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#university-table', { serverSide: true, ajax: { url: @json(route('bk.universities.data')), data: function (params) { params.type = window.$('#university-type-filter').val(); params.is_active = window.$('#university-status-filter').val(); } }, columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'short_name', name: 'short_name', defaultContent: '-' }, { data: 'name', name: 'name' }, { data: 'type', name: 'type' }, { data: 'students', name: 'students', orderable: false, searchable: false }, { data: 'is_active', name: 'is_active' }, { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ], order: [[2, 'asc']] });
                window.$('[data-table-filter]').on('change', function () { window.$('#university-table').DataTable().ajax.reload(); });
            });
        </script>
    @endpush
@endcomponent

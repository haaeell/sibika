@component('layouts.app', ['title' => 'Mata Pelajaran'])
    <x-page-header title="Mata Pelajaran" description="Kelola master mata pelajaran dan kategorinya.">
        <x-slot:actions><x-button :href="route('bk.subjects.create')"><i class="fa-solid fa-plus"></i> Tambah Mata Pelajaran</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="subject-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="subjects" />
        </div>
        <div class="overflow-x-auto">
            <table id="subject-table" class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr>
                    <th class="px-4 py-3 font-bold">No</th><th class="px-4 py-3 font-bold">Kode</th><th class="px-4 py-3 font-bold">Nama Mata Pelajaran</th><th class="px-4 py-3 font-bold">Kategori</th><th class="px-4 py-3 font-bold">Guru</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-card>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#subject-table', { serverSide: true, ajax: { url: @json(route('bk.subjects.data')), data: function (params) { params.is_active = window.$('#subject-status-filter').val(); } }, columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'code', name: 'code' }, { data: 'name', name: 'name' }, { data: 'category', name: 'category' }, { data: 'teachers', name: 'teachers' }, { data: 'is_active', name: 'is_active' }, { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ], order: [[2, 'asc']] });
                window.$('[data-table-filter]').on('change', function () { window.$('#subject-table').DataTable().ajax.reload(); });
            });
        </script>
    @endpush
@endcomponent

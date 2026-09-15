@component('layouts.app', ['title' => 'Mapel TKA'])
    <x-page-header title="Mapel TKA" description="Kelola daftar mapel TKA yang dapat dipilih siswa di biodata.">
        <x-slot:actions><x-button :href="route('bk.tka-subjects.create')"><i class="fa-solid fa-plus"></i> Tambah Mapel TKA</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="tka-subject-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="tka-subject-table" class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr>
                    <th class="px-4 py-3 font-bold">No</th><th class="px-4 py-3 font-bold">Nama Mapel TKA</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-card>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#tka-subject-table', { serverSide: true, ajax: { url: @json(route('bk.tka-subjects.data')), data: function (params) { params.is_active = window.$('#tka-subject-status-filter').val(); } }, columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'name', name: 'name' }, { data: 'is_active', name: 'is_active' }, { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ], order: [[1, 'asc']] });
                window.$('[data-table-filter]').on('change', function () { window.$('#tka-subject-table').DataTable().ajax.reload(); });
            });
        </script>
    @endpush
@endcomponent

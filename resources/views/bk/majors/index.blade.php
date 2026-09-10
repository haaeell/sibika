@component('layouts.app', ['title' => 'Jurusan'])
    <x-page-header title="Jurusan" description="Kelola master jurusan untuk rombongan belajar.">
        <x-slot:actions><x-button :href="route('bk.majors.create')"><i class="fa-solid fa-plus"></i> Tambah Jurusan</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="major-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <x-export-buttons resource="majors" />
        </div>
        <div class="overflow-x-auto">
            <table id="major-table" class="w-full min-w-[700px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr>
                    <th class="px-4 py-3 font-bold">No</th><th class="px-4 py-3 font-bold">Kode</th><th class="px-4 py-3 font-bold">Nama Jurusan</th><th class="px-4 py-3 font-bold">Jumlah Kelas</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-card>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#major-table', { serverSide: true, ajax: { url: @json(route('bk.majors.data')), data: function (params) { params.is_active = window.$('#major-status-filter').val(); } }, columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, { data: 'code', name: 'code' }, { data: 'name', name: 'name' }, { data: 'classes', name: 'classes' }, { data: 'is_active', name: 'is_active' }, { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ], order: [[2, 'asc']] });
                window.$('[data-table-filter]').on('change', function () { window.$('#major-table').DataTable().ajax.reload(); });
                window.$(document).on('submit', '.js-delete-form', function (event) { event.preventDefault(); const form = this; window.confirmAction({ title: 'Hapus jurusan?', text: 'Jurusan yang masih dipakai kelas tidak dapat dihapus.', confirmText: 'Ya, hapus' }).then((result) => { if (result.isConfirmed) form.submit(); }); });
            });
        </script>
    @endpush
@endcomponent

@component('layouts.app', ['title' => 'Guru'])
    <x-page-header title="Guru" description="Kelola data guru dan pengajar.">
        <x-slot:actions>
            <x-button :href="route('bk.teachers.create')"><i class="fa-solid fa-plus"></i> Tambah Guru</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
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
                    ajax: @json(route('bk.teachers.data')),
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'code', name: 'code' },
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email', render: (data, type, row) => `${data}<br><span class="text-xs text-slate-400">${row.phone || '-'}</span>` },
                        { data: 'subjects', name: 'subjects', defaultContent: '-' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[2, 'asc']],
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();
                    const form = this;
                    window.confirmAction({ title: 'Hapus guru?', text: 'Data yang dihapus tidak bisa dikembalikan.', confirmText: 'Ya, hapus' }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        </script>
    @endpush
@endcomponent

@component('layouts.app', ['title' => 'Siswa'])
    <x-page-header title="Siswa" description="Kelola data siswa dan penempatan kelas.">
        <x-slot:actions>
            <x-button :href="route('bk.students.create')"><i class="fa-solid fa-plus"></i> Tambah Siswa</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-export-buttons resource="students" />
        </div>
        <div class="overflow-x-auto">
            <table id="student-table" class="w-full min-w-[980px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">NIS</th>
                        <th class="px-4 py-3 font-bold">NISN</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Kelas</th>
                        <th class="px-4 py-3 font-bold">Angkatan</th>
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
                window.initDataTable('#student-table', {
                    serverSide: true,
                    ajax: @json(route('bk.students.data')),
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'nis', name: 'nis' },
                        { data: 'nisn', name: 'nisn', defaultContent: '-' },
                        { data: 'name', name: 'name' },
                        { data: 'class_name', name: 'schoolClass.name' },
                        { data: 'cohort_name', name: 'cohort.name' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[3, 'asc']],
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();
                    const form = this;
                    window.confirmAction({ title: 'Hapus siswa?', text: 'Data yang dihapus tidak bisa dikembalikan.', confirmText: 'Ya, hapus' }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        </script>
    @endpush
@endcomponent

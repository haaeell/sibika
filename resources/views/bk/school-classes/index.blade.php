@component('layouts.app', ['title' => 'Kelas'])
    <x-page-header title="Kelas" description="Kelola rombongan belajar dan wali kelas.">
        <x-slot:actions>
            <x-button :href="route('bk.school-classes.create')"><i class="fa-solid fa-plus"></i> Tambah Kelas</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table id="school-class-table" class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama Kelas</th>
                        <th class="px-4 py-3 font-bold">Tingkat</th>
                        <th class="px-4 py-3 font-bold">Jurusan</th>
                        <th class="px-4 py-3 font-bold">Tahun Ajaran</th>
                        <th class="px-4 py-3 font-bold">Wali Kelas</th>
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
                window.initDataTable('#school-class-table', {
                    serverSide: true,
                    ajax: @json(route('bk.school-classes.data')),
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'grade_level', name: 'grade_level' },
                        { data: 'major', name: 'major' },
                        { data: 'academic_year', name: 'academicYear.name' },
                        { data: 'homeroom_teacher', name: 'homeroomTeacher.name' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'asc']],
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();
                    const form = this;
                    window.confirmAction({ title: 'Hapus kelas?', text: 'Data yang dihapus tidak bisa dikembalikan.', confirmText: 'Ya, hapus' }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        </script>
    @endpush
@endcomponent

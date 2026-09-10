@component('layouts.app', ['title' => 'Kelas'])
    <x-page-header title="Kelas" description="Kelola rombongan belajar dan wali kelas.">
        <x-slot:actions>
            <x-button :href="route('bk.school-classes.create')"><i class="fa-solid fa-plus"></i> Tambah Kelas</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="school-class-year-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua tahun ajaran">
                    <option value="">Semua tahun ajaran</option>
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                    @endforeach
                </select>
                <select id="school-class-major-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua jurusan">
                    <option value="">Semua jurusan</option>
                    @foreach ($majors as $major)
                        <option value="{{ $major->id }}">{{ $major->name }}</option>
                    @endforeach
                </select>
                <select id="school-class-grade-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua tingkat">
                    <option value="">Semua tingkat</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
            </div>
            <x-export-buttons resource="school-classes" />
        </div>
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
                    ajax: {
                        url: @json(route('bk.school-classes.data')),
                        data: function (params) {
                            params.academic_year_id = window.$('#school-class-year-filter').val();
                            params.major_id = window.$('#school-class-major-filter').val();
                            params.grade_level = window.$('#school-class-grade-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'grade_level', name: 'grade_level' },
                        { data: 'major_name', name: 'major.name' },
                        { data: 'academic_year', name: 'academicYear.name' },
                        { data: 'homeroom_teacher', name: 'homeroomTeacher.name' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#school-class-table').DataTable().ajax.reload();
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

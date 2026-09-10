@component('layouts.app', ['title' => 'Setting Nilai'])
    <x-page-header title="Setting Nilai" description="Atur mapel umum dan mapel jurusan per semester."><x-slot:actions><x-button :href="route('bk.score-subject-settings.create')"><i class="fa-solid fa-plus"></i> Tambah Setting</x-button></x-slot:actions></x-page-header>

    <x-card>
        <div class="mb-4 grid gap-3 rounded-2xl bg-slate-50 p-4 lg:grid-cols-4">
            <x-form.select name="semester_filter" label="Semester" icon="fa-solid fa-layer-group" id="score-semester-filter" class="select2" multiple data-table-filter data-placeholder="Semua semester">
                @for ($semester = 1; $semester <= 5; $semester++)
                    <option value="{{ $semester }}">Semester {{ $semester }}</option>
                @endfor
            </x-form.select>
            <x-form.select name="scope_filter" label="Tipe Mapel" icon="fa-solid fa-tags" id="score-scope-filter" class="select2" multiple data-table-filter data-placeholder="Semua tipe">
                <option value="general">Umum</option>
                <option value="major">Jurusan</option>
            </x-form.select>
            <x-form.select name="major_filter" label="Jurusan" icon="fa-solid fa-code-branch" id="score-major-filter" class="select2" multiple data-table-filter data-placeholder="Semua jurusan">
                @foreach ($majors as $major)
                    <option value="{{ $major->id }}">{{ $major->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="status_filter" label="Status" icon="fa-solid fa-toggle-on" id="score-status-filter" class="select2" multiple data-table-filter data-placeholder="Semua status">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
            </x-form.select>
        </div>

        <div class="overflow-x-auto">
            <table id="score-setting-table" class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th>No</th>
                        <th>Semester</th>
                        <th>Mata Pelajaran</th>
                        <th>Tipe/Jurusan</th>
                        <th>Wajib</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('#score-setting-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.score-subject-settings.data')),
                        data: function (params) {
                            params.semester_number = window.$('#score-semester-filter').val();
                            params.scope = window.$('#score-scope-filter').val();
                            params.major_id = window.$('#score-major-filter').val();
                            params.is_active = window.$('#score-status-filter').val();
                        },
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'semester_badge', name: 'semester_number' },
                        { data: 'subject_name', name: 'subject.name' },
                        { data: 'major_name', name: 'major.name' },
                        { data: 'is_required', name: 'is_required' },
                        { data: 'is_active', name: 'is_active' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'asc']],
                });

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#score-setting-table').DataTable().ajax.reload();
                });
            });
        </script>
    @endpush
@endcomponent

@component('layouts.app', ['title' => 'Siswa'])
    <x-page-header title="Siswa" description="Kelola data siswa dan penempatan kelas.">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('bk.students.template')"><i class="fa-solid fa-file-arrow-down"></i> Template</x-button>
            <x-button variant="secondary" :href="route('bk.students.import.create')"><i class="fa-solid fa-upload"></i> Import Excel</x-button>
            <x-button :href="route('bk.students.create')"><i class="fa-solid fa-plus"></i> Tambah Siswa</x-button>
            <form action="{{ route('bk.students.destroy-all') }}" method="POST" class="js-delete-form inline-flex" data-confirm-title="Hapus semua siswa?" data-confirm-text="Seluruh data siswa, akun login, dan data terkait akan dihapus permanen." data-confirm-button="Ya, hapus semua">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700"><i class="fa-solid fa-trash"></i> Hapus Semua</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    @if (session('import_failures'))
        <x-card class="border-rose-200">
            <p class="text-sm font-bold text-rose-700">
                {{ count(session('import_failures')) }} baris gagal saat import terakhir:
            </p>
            <ul class="mt-2 max-h-40 list-disc space-y-1 overflow-auto pl-5 text-sm text-slate-600">
                @foreach (session('import_failures') as $failure)
                    <li>Baris {{ $failure['row'] }} (NIS: {{ $failure['nis'] ?: '-' }}) — {{ $failure['message'] }}</li>
                @endforeach
            </ul>
            <p class="mt-2 text-sm text-slate-500">Lihat detail di <a href="{{ route('bk.students.import.create') }}" class="font-bold text-blue-800 underline">halaman import</a>.</p>
        </x-card>
    @endif

    <x-card>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-500">Filter:</span>
                <select id="student-class-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua kelas">
                    <option value="">Semua kelas</option>
                    @foreach ($schoolClasses as $schoolClass)
                        <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
                    @endforeach
                </select>
                <select id="student-cohort-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua angkatan">
                    <option value="">Semua angkatan</option>
                    @foreach ($cohorts as $cohort)
                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                    @endforeach
                </select>
                <select id="student-status-filter" data-table-filter class="table-filter-select select2" multiple data-placeholder="Semua status">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="graduated">Lulus</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
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
                    ajax: {
                        url: @json(route('bk.students.data')),
                        data: function (params) {
                            params.class_id = window.$('#student-class-filter').val();
                            params.cohort_id = window.$('#student-cohort-filter').val();
                            params.status = window.$('#student-status-filter').val();
                        },
                    },
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

                window.$('[data-table-filter]').on('change', function () {
                    window.$('#student-table').DataTable().ajax.reload();
                });
            });
        </script>
    @endpush
@endcomponent

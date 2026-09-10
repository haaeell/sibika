@component('layouts.app', ['title' => 'Tahun Ajaran'])
    <x-page-header title="Tahun Ajaran" description="Kelola periode akademik dan semester aktif.">
        <x-slot:actions>
            <x-button :href="route('bk.academic-years.create')">
                <i class="fa-solid fa-plus"></i>
                Tambah Tahun Ajaran
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex justify-end">
            <x-export-buttons resource="academic-years" />
        </div>
        <div class="overflow-x-auto">
            <table id="academic-year-table" class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Periode</th>
                        <th class="px-4 py-3 font-bold">Semester</th>
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
                window.initDataTable('#academic-year-table', {
                    serverSide: true,
                    ajax: @json(route('bk.academic-years.data')),
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'period', name: 'start_year' },
                        { data: 'semester', name: 'semester' },
                        { data: 'is_active', name: 'is_active' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[4, 'desc']],
                });

                window.$(document).on('submit', '.js-delete-form', function (event) {
                    event.preventDefault();

                    window.confirmAction({
                        title: 'Hapus tahun ajaran?',
                        text: 'Data yang dihapus tidak bisa dikembalikan.',
                        confirmText: 'Ya, hapus',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endcomponent

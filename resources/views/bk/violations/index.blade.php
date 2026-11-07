@component('layouts.app', ['title' => 'Pelanggaran'])
    <x-page-header title="Pelanggaran" description="Kelola riwayat pelanggaran, sanksi, dan surat peringatan siswa.">
        <x-slot:actions>
            <button type="button" id="violation-import-open" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700 hover:bg-emerald-100"><i class="fa-solid fa-file-excel" aria-hidden="true"></i> Import Excel</button>
            <x-button :href="route('bk.violations.create')"><i class="fa-solid fa-plus" aria-hidden="true"></i> Catat pelanggaran</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="violation-top-title">
            <div class="flex flex-wrap items-center justify-between gap-2"><div><h2 id="violation-top-title" class="font-bold text-slate-900">Siswa paling sering tercatat</h2><p class="mt-1 text-xs text-slate-500">Lima teratas sesuai filter kelas kejadian dan tanggal; klik nama untuk riwayat lengkap.</p></div></div>
            <div id="violation-top-students" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @include('bk.violations._top-students')
            </div>
        </section>
        @error('warning_letter')<p role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">{{ $message }} Pilih ulang surat melalui tombol upload pada catatan terkait.</p>@enderror
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4"><h2 class="font-bold text-slate-900">Filter riwayat</h2><p class="mt-1 text-xs text-slate-500">Cari terlapor, NIS, pasal, kelas saat kejadian, atau tanggal.</p></div>
            <form id="violation-filters" method="GET" action="{{ route('bk.violations.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <label class="text-xs font-bold text-slate-600">Kelas kejadian<select name="class_at_incident" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100"><option value="">Semua kelas</option>@foreach ($classes as $class)<option value="{{ $class }}" @selected(($filters['class_at_incident'] ?? null) === $class)>{{ $class }}</option>@endforeach</select></label>
                <label class="text-xs font-bold text-slate-600">Dari tanggal<input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100"></label>
                <label class="text-xs font-bold text-slate-600">Sampai tanggal<input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-100"></label>
                <div class="flex items-end gap-2"><x-button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Terapkan</x-button>@if (array_filter($filters))<x-button variant="secondary" :href="route('bk.violations.index')">Reset</x-button>@endif</div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4"><div><h2 class="font-bold text-slate-900">Daftar pelanggaran</h2><p class="mt-1 text-xs text-slate-500">Cari dan urutkan catatan; terbaru ditampilkan lebih dulu.</p></div></div>
            <div class="overflow-x-auto p-4 sm:p-5"><table id="violation-table" class="w-full min-w-[950px] text-left text-sm">
                <thead><tr><th>No</th><th>Kejadian</th><th>Terlapor</th><th>Total</th><th>Pasal</th><th>Sanksi</th><th>Surat</th><th>Pencatat</th><th class="text-right">Aksi</th></tr></thead><tbody></tbody>
            </table></div>
        </section>

    </div>

    <dialog id="violation-import-modal" aria-labelledby="violation-import-title" class="warning-letter-dialog" style="width:min(36rem,calc(100vw - 2rem))">
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4"><div><h2 id="violation-import-title" class="text-lg font-bold text-slate-900">Import Excel</h2><p class="mt-1 text-xs text-slate-500">Gunakan template yang disediakan sebelum mengunggah.</p></div><button type="button" class="btn-icon" data-import-close aria-label="Tutup modal import"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>
            <div class="px-5 py-5"><p class="text-sm text-slate-600">Isi sheet Data Pelanggaran. Nama siswa dan kelas saat ini digunakan untuk mencocokkan terlapor.</p>
                <p class="mt-2 text-xs text-slate-500">Kolom: nama_siswa · kelas_saat_ini · kelas_saat_kejadian · tanggal_kejadian (DD-MM-YYYY) · pasal_perdupsis · sanksi.</p>
                <div class="mt-4"><x-button variant="secondary" :href="route('bk.violations.template')"><i class="fa-solid fa-download" aria-hidden="true"></i> Unduh template Excel</x-button></div>
                <form method="POST" action="{{ route('bk.violations.import') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3 rounded-xl bg-slate-50 p-4">@csrf
                    <label class="min-w-64 flex-1 text-sm font-semibold text-slate-700">File Excel <span class="text-rose-500">*</span><input type="file" name="file" accept=".xlsx,.xls" required class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm"></label>
                    <x-button type="submit"><i class="fa-solid fa-upload" aria-hidden="true"></i> Impor data</x-button>
                </form>
                @error('file')<p role="alert" class="mt-3 rounded-xl bg-rose-50 p-3 text-sm font-semibold text-rose-800">{{ $message }}</p>@enderror
                @if (session('import_errors'))<ul class="mt-3 max-h-40 list-inside list-disc space-y-1 overflow-auto rounded-xl bg-rose-50 p-4 text-sm text-rose-800">@foreach (session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
            </div>
    </dialog>

    <dialog id="warning-letter-modal" aria-labelledby="warning-letter-title" class="warning-letter-dialog">
        <form id="warning-letter-form" method="POST" enctype="multipart/form-data">@csrf
            <div class="border-b border-slate-100 px-6 py-5"><div class="flex items-start justify-between gap-4"><div><h2 id="warning-letter-title" class="text-lg font-bold text-slate-900">Upload Surat Peringatan</h2><p id="warning-letter-student" class="mt-1 text-sm text-slate-500"></p></div><button type="button" class="btn-icon" data-warning-letter-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div></div>
            <div class="space-y-4 px-6 py-5"><label for="modal-letter" class="block text-sm font-semibold text-slate-700">File PDF <span class="text-rose-500">*</span><span class="mt-1 block text-xs font-normal text-slate-500">Ukuran maksimal 2 MB.</span></label><input id="modal-letter" type="file" name="warning_letter" accept="application/pdf,.pdf" required class="block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm"></div>
            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4"><button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100" data-warning-letter-close>Batal</button><x-button type="submit"><i class="fa-solid fa-upload" aria-hidden="true"></i> Simpan surat</x-button></div>
        </form>
    </dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('violation-filters');
                const table = window.initDataTable('#violation-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.violations.data')),
                        data: params => new FormData(form).forEach((value, key) => { if (value) params[key] = value; }),
                    },
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'incident', name: 'occurred_at' },
                        { data: 'reportee', orderable: false },
                        { data: 'total_violations', orderable: false, searchable: false },
                        { data: 'pasal_perdupsis', name: 'pasal_perdupsis' },
                        { data: 'sanction_preview', name: 'sanction', orderable: false },
                        { data: 'letter', orderable: false, searchable: false },
                        { data: 'creator_name', orderable: false },
                        { data: 'action', orderable: false, searchable: false, className: 'text-right' },
                    ],
                    order: [[1, 'desc']],
                });

                const modal = document.getElementById('warning-letter-modal');
                document.body.appendChild(modal);
                const importModal = document.getElementById('violation-import-modal');
                document.body.appendChild(importModal);
                document.getElementById('violation-import-open').addEventListener('click', () => importModal.showModal());
                importModal.querySelector('[data-import-close]').addEventListener('click', () => importModal.close());
                importModal.addEventListener('click', event => { if (event.target === importModal) importModal.close(); });
                @if ($errors->has('file') || session('import_errors')) importModal.showModal(); @endif
                const upload = document.getElementById('warning-letter-form');
                document.getElementById('violation-table').addEventListener('click', event => {
                    const button = event.target.closest('[data-warning-letter-url]');
                    if (!button) return;
                    upload.reset();
                    upload.action = button.dataset.warningLetterUrl;
                    document.getElementById('warning-letter-title').textContent = button.dataset.warningLetterReplace === '1' ? 'Ganti Surat Peringatan' : 'Upload Surat Peringatan';
                    document.getElementById('warning-letter-student').textContent = `${button.dataset.warningLetterName} · ${button.dataset.warningLetterNis}`;
                    modal.showModal();
                    document.getElementById('modal-letter').focus();
                });
                modal.querySelectorAll('[data-warning-letter-close]').forEach(button => button.addEventListener('click', () => modal.close()));
                modal.addEventListener('click', event => { if (event.target === modal) modal.close(); });
            });
        </script>
    @endpush
@endcomponent

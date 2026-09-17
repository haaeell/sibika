@component('layouts.app', ['title' => 'Import Siswa'])
    <x-page-header title="Import Siswa" description="Upload file Excel untuk menambah / meng-update banyak siswa sekaligus.">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('bk.students.template')">
                <i class="fa-solid fa-file-arrow-down"></i> Download Template
            </x-button>
            <x-button variant="secondary" :href="route('bk.students.index')">Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-card>
            <form action="{{ route('bk.students.import.prepare') }}" method="POST" enctype="multipart/form-data" class="space-y-4" data-student-import-form>
                @csrf
                <div>
                    <label for="file" class="mb-1.5 block text-sm font-semibold text-slate-700">File Excel <span class="text-rose-500">*</span></label>
                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept=".xlsx,.xls"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-blue-800 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                    >
                    <p class="mt-1.5 text-xs text-slate-500">Format .xlsx / .xls, maksimal 5 MB. Gunakan template resmi agar kolom terbaca.</p>
                    <x-form.error name="file" />
                </div>
                <x-button type="submit" data-student-import-submit><i class="fa-solid fa-upload"></i> Upload & Proses</x-button>
            </form>

            <div class="mt-6 hidden rounded-xl border border-blue-200 bg-blue-50 p-4" data-student-import-progress aria-live="polite">
                <div class="flex items-center justify-between gap-3 text-sm font-bold text-blue-900">
                    <span data-student-import-message>Menyiapkan import...</span>
                    <span data-student-import-count>0 / 0</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-blue-100"><div class="h-full w-0 rounded-full bg-blue-800 transition-all" data-student-import-bar></div></div>
            </div>

            <div class="mt-6 hidden overflow-hidden rounded-xl border border-rose-200" data-student-import-failures>
                    <p class="bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700">
                        <span data-student-import-failure-count>0</span> baris gagal. Perbaiki lalu upload ulang baris tersebut.
                    </p>
                    <div class="max-h-72 overflow-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-500">
                                <tr><th class="px-4 py-2">Baris</th><th class="px-4 py-2">NIS</th><th class="px-4 py-2">Kesalahan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" data-student-import-failure-rows></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-card>

        <aside class="space-y-4">
            <x-card>
                <h3 class="font-bold text-slate-900"><i class="fa-solid fa-circle-info mr-1.5 text-blue-800"></i>Format Kolom</h3>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-slate-600">
                    <li><b>NIS</b> — wajib, unik. NIS yang sudah ada akan <b>di-update</b>.</li>
                    <li><b>NISN</b> — opsional, boleh kosong.</li>
                    <li><b>Nama</b> — wajib.</li>
                    <li><b>Kelas</b> — nama persis seperti di aplikasi.</li>
                    <li><b>Angkatan</b> — nama persis seperti di aplikasi.</li>
                    <li><b>Status</b> — <code>active</code> / <code>graduated</code> / <code>inactive</code>.</li>
                </ol>
            </x-card>
            <x-card>
                <h3 class="font-bold text-slate-900"><i class="fa-solid fa-key mr-1.5 text-emerald-700"></i>Akun Otomatis</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Email: <code class="rounded bg-slate-100 px-1.5 py-0.5">{NISN}@smaplusasthahannas.id</code> (atau NIS bila NISN kosong)<br>
                    Password awal: <b>NIS</b>. Siswa wajib menggantinya saat login pertama via popup.
                </p>
            </x-card>
            <x-card>
                <h3 class="mb-2 font-bold text-slate-900">Referensi Nama</h3>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Kelas tersedia</p>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    @forelse ($schoolClasses as $schoolClass)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $schoolClass->name }}</span>
                    @empty
                        <span class="text-sm text-slate-400">Belum ada kelas.</span>
                    @endforelse
                </div>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-400">Angkatan tersedia</p>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    @forelse ($cohorts as $cohort)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $cohort->name }}</span>
                    @empty
                        <span class="text-sm text-slate-400">Belum ada angkatan.</span>
                    @endforelse
                </div>
            </x-card>
        </aside>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('[data-student-import-form]');
                if (!form) return;

                const submit = form.querySelector('[data-student-import-submit]');
                const progress = document.querySelector('[data-student-import-progress]');
                const message = document.querySelector('[data-student-import-message]');
                const count = document.querySelector('[data-student-import-count]');
                const bar = document.querySelector('[data-student-import-bar]');
                const failures = document.querySelector('[data-student-import-failures]');
                const failureCount = document.querySelector('[data-student-import-failure-count]');
                const failureRows = document.querySelector('[data-student-import-failure-rows]');
                const batchUrl = @json(route('bk.students.import.batch'));

                const request = async function (url, body) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body,
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'Import gagal diproses.');
                    return payload;
                };

                const showFailures = function (items) {
                    if (!items.length) return;
                    failures.classList.remove('hidden');
                    failureCount.textContent = items.length;
                    failureRows.replaceChildren(...items.map(function (item) {
                        const row = document.createElement('tr');
                        [item.row, item.nis || '-', item.message].forEach(function (value, index) {
                            const cell = document.createElement('td');
                            cell.className = index === 0 ? 'px-4 py-2 font-bold' : index === 2 ? 'px-4 py-2 text-rose-600' : 'px-4 py-2';
                            cell.textContent = value;
                            row.appendChild(cell);
                        });
                        return row;
                    }));
                };

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    submit.disabled = true;
                    progress.classList.remove('hidden');
                    failures.classList.add('hidden');

                    try {
                        const prepared = await request(form.action, new FormData(form));
                        let offset = 0;
                        let result;

                        do {
                            result = await request(batchUrl, new URLSearchParams({ token: prepared.token, offset }));
                            offset = result.next_offset;
                            count.textContent = `${result.processed} / ${result.total}`;
                            bar.style.width = `${Math.round((result.processed / result.total) * 100)}%`;
                            message.textContent = `Memproses akun siswa... ${result.success} baru, ${result.updated} diperbarui.`;
                        } while (!result.done);

                        showFailures(result.failures);
                        message.textContent = `Import selesai: ${result.success} baru, ${result.updated} diperbarui${result.failures.length ? `, ${result.failures.length} gagal.` : '.'}`;
                    } catch (error) {
                        message.textContent = error.message;
                        progress.classList.replace('border-blue-200', 'border-rose-200');
                        progress.classList.replace('bg-blue-50', 'bg-rose-50');
                    } finally {
                        submit.disabled = false;
                    }
                });
            });
        </script>
    @endpush
@endcomponent

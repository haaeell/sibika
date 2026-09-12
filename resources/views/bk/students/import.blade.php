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
            <form action="{{ route('bk.students.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                <x-button type="submit"><i class="fa-solid fa-upload"></i> Upload & Proses</x-button>
            </form>

            @if (session('import_failures'))
                <div class="mt-6 overflow-hidden rounded-xl border border-rose-200">
                    <p class="bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700">
                        {{ count(session('import_failures')) }} baris gagal — perbaiki lalu upload ulang baris tersebut.
                    </p>
                    <div class="max-h-72 overflow-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-500">
                                <tr><th class="px-4 py-2">Baris</th><th class="px-4 py-2">NIS</th><th class="px-4 py-2">Kesalahan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach (session('import_failures') as $failure)
                                    <tr>
                                        <td class="px-4 py-2 font-bold">{{ $failure['row'] }}</td>
                                        <td class="px-4 py-2">{{ $failure['nis'] ?: '-' }}</td>
                                        <td class="px-4 py-2 text-rose-600">{{ $failure['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
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
@endcomponent

@component('layouts.app', ['title' => 'Absensi TH'])
    <x-page-header title="Absensi Tanpa Keterangan" description="">
        <x-slot:actions>@unless (auth()->user()->hasRole('kepala_sekolah'))<button type="button" id="attendance-import-open" class="inline-flex items-center gap-2 rounded-xl bg-blue-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900"><i class="fa-solid fa-file-excel"></i> Import Excel</button>@endunless</x-slot:actions>
    </x-page-header>

    <div class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="attendance-top-title">
            <div><h2 id="attendance-top-title" class="font-bold text-slate-900">5 Siswa Tanpa Keterangan Terbanyak</h2><p class="mt-1 text-xs text-slate-500">Akumulasi seluruh data{{ $hasFilters ? ' yang sesuai filter aktif' : '' }}.</p></div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @forelse ($topStudents as $row)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3"><span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-900">{{ $row['name'] }}</span><span class="block text-xs text-slate-500">{{ $row['nis'] }} · {{ $row['class'] }}</span></span><span class="shrink-0 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-extrabold text-rose-800">{{ $row['absences'] }} TH</span></div>
                @empty
                    <p class="text-sm text-slate-500 sm:col-span-2 xl:col-span-5">Belum ada data sesuai filter.</p>
                @endforelse
            </div>
        </section>
        <x-card title="Laporan absensi" description="">
            <form action="{{ route('bk.attendance.index') }}" method="GET" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <label class="text-sm font-semibold">Minggu<select name="week_id" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"><option value="all" @selected(($filters['week_id'] ?? null) === 'all')>Semua minggu</option>@foreach ($weeks as $week)<option value="{{ $week->id }}" @selected(($filters['week_id'] ?? null) == $week->id)>Minggu {{ $week->week_number }} · {{ $week->starts_on->format('d-m-Y') }}</option>@endforeach</select></label>
                <x-form.input name="from" type="date" label="Dari tanggal" :value="$filters['from'] ?? ''" />
                <x-form.input name="to" type="date" label="Sampai tanggal" :value="$filters['to'] ?? ''" />
                <x-form.select name="student_id" label="Siswa" class="select2" data-placeholder="Semua siswa"><option value="">Semua siswa</option>@foreach ($students as $student)<option value="{{ $student->id }}" @selected(($filters['student_id'] ?? null) == $student->id)>{{ $student->name }} · {{ $student->nis }}</option>@endforeach</x-form.select>
                <label class="text-sm font-semibold">Kelas<select name="class_id" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"><option value="">Semua kelas</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected(($filters['class_id'] ?? null) == $class->id)>{{ $class->name }}</option>@endforeach</select></label>
                <div class="flex items-end gap-2"><x-button type="submit">Terapkan</x-button><x-button variant="secondary" :href="route('bk.attendance.index')">Reset</x-button></div>
            </form>
            @unless (auth()->user()->hasRole('kepala_sekolah'))<div class="mt-4 flex gap-2"><a class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-800" href="{{ route('bk.attendance.export', ['format' => 'xlsx'] + $filters) }}">Excel</a><a class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-800" href="{{ route('bk.attendance.export', ['format' => 'pdf'] + $filters) }}">PDF</a></div>@endunless
        </x-card>

        <x-card title="Data mingguan" description="{{ ! $hasFilters && ! $allWeeks && $weeks->isNotEmpty() ? 'Menampilkan minggu terbaru. Pilih Semua minggu atau gunakan filter untuk data lain.' : 'TH ÷ jumlah pertemuan masing-masing siswa × 100.' }}">
            @unless (auth()->user()->hasRole('kepala_sekolah'))
                @if ($weeks->isNotEmpty())
                    <div class="mb-4 flex flex-wrap items-end gap-3">
                        <label for="delete-attendance-week" class="text-sm font-semibold text-slate-700">Hapus data satu minggu
                            <select id="delete-attendance-week" class="select2 mt-1 block w-full min-w-56 rounded-xl border border-slate-300 bg-white p-2 text-sm" data-placeholder="Cari minggu">
                                <option value="">Pilih minggu</option>
                                @foreach ($weeks as $week)
                                    <option value="{{ route('bk.attendance.weeks.destroy', $week) }}" data-label="Minggu {{ $week->week_number }} · {{ $week->starts_on->format('d-m-Y') }}–{{ $week->ends_on->format('d-m-Y') }}">Minggu {{ $week->week_number }} · {{ $week->starts_on->format('d-m-Y') }}–{{ $week->ends_on->format('d-m-Y') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <form id="delete-attendance-week-form" method="POST" action="{{ route('bk.attendance.weeks.destroy', $weeks->first()) }}" class="js-delete-form" data-confirm-title="Hapus seluruh absensi minggu ini?" data-confirm-text="Seluruh catatan absensi minggu terpilih akan dihapus permanen.">@csrf @method('DELETE')<x-button variant="danger" type="submit" disabled><i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus minggu</x-button></form>
                    </div>
                @endif
            @endunless
            <div class="overflow-x-auto"><table class="w-full min-w-[780px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">Minggu</th><th class="p-3">Siswa</th><th class="p-3">Kelas</th><th class="p-3 text-right">Pertemuan</th><th class="p-3 text-right">TH</th><th class="p-3 text-right">TH %</th>@unless (auth()->user()->hasRole('kepala_sekolah'))<th class="p-3">Aksi</th>@endunless</tr></thead><tbody>@forelse ($records as $row)<tr class="border-t border-slate-100"><td class="p-3">Ke-{{ $row->week->week_number }}<span class="block text-xs text-slate-500">{{ $row->week->starts_on->translatedFormat('d M') }}–{{ $row->week->ends_on->translatedFormat('d M Y') }}</span></td><td class="p-3 font-bold">{{ $row->student->name }}<span class="block text-xs font-normal text-slate-500">{{ $row->student->nis }}</span></td><td class="p-3">{{ $row->student->schoolClass?->name ?? '—' }}</td><td class="p-3 text-right">{{ $row->meetings }}</td><td class="p-3 text-right">{{ $row->absences }}</td><td class="p-3 text-right"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">{{ number_format($row->percentage, 2, ',', '.') }}%</span></td>@unless (auth()->user()->hasRole('kepala_sekolah'))<td class="p-3"><form method="POST" action="{{ route('bk.attendance.destroy', $row) }}" class="js-delete-form" data-confirm-title="Hapus absensi {{ $row->student->name }}?" data-confirm-text="Catatan siswa pada minggu ini akan dihapus permanen.">@csrf @method('DELETE')<x-button variant="danger" type="submit"><i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus</x-button></form></td>@endunless</tr>@empty<tr><td colspan="7" class="p-6 text-center text-slate-500">Belum ada data TH sesuai filter.</td></tr>@endforelse</tbody></table></div>
            <div class="mt-4">{{ $records->links() }}</div>
        </x-card>

        <details class="group rounded-2xl border border-slate-200 bg-white shadow-sm"><summary class="cursor-pointer p-5 font-bold text-slate-900">Akumulasi periode filter <i class="fa-solid fa-chevron-down ml-2 text-xs transition group-open:rotate-180"></i></summary>
            <div class="border-t border-slate-100 p-5"><p class="mb-3 text-xs text-slate-500">Total TH ÷ total pertemuan dari semua catatan yang masuk rentang tanggal dan filter aktif.</p><div class="overflow-x-auto"><table class="w-full min-w-[650px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">Siswa</th><th class="p-3">Kelas</th><th class="p-3 text-right">Total pertemuan</th><th class="p-3 text-right">Total TH</th><th class="p-3 text-right">TH %</th></tr></thead><tbody>@forelse ($summary as $row)<tr class="border-t border-slate-100"><td class="p-3 font-semibold">{{ $row['name'] }} <span class="text-xs font-normal text-slate-500">{{ $row['nis'] }}</span></td><td class="p-3">{{ $row['class'] }}</td><td class="p-3 text-right">{{ $row['meetings'] }}</td><td class="p-3 text-right">{{ $row['absences'] }}</td><td class="p-3 text-right">{{ number_format($row['percentage'], 2, ',', '.') }}%</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada rekap.</td></tr>@endforelse</tbody></table></div></div>
        </details>
    </div>

    <dialog id="attendance-import-modal" aria-labelledby="attendance-import-title" class="warning-letter-dialog" style="width:min(38rem,calc(100vw - 2rem));max-height:calc(100vh - 2rem);overflow-y:auto">
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4"><div><h2 id="attendance-import-title" class="text-lg font-bold text-slate-900">Import Absensi Mingguan</h2><p class="mt-1 text-xs text-slate-500">Atur periode sekali; file Excel hanya berisi siswa dengan TH.</p></div><button type="button" data-attendance-import-close class="btn-icon" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="space-y-4 p-5">
            <p class="text-sm text-slate-600">Isi minggu ke-, tanggal awal dan tanggal akhir di sini. Template berisi nama siswa, jumlah pertemuan siswa, dan jumlah TH; kelas diambil otomatis dari data siswa saat ini.</p>
            <x-button variant="secondary" :href="route('bk.attendance.template')"><i class="fa-solid fa-download"></i> Unduh template Excel</x-button>
            <form action="{{ route('bk.attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">@csrf
                <div class="grid gap-3 sm:grid-cols-3">
                    <label class="text-sm font-semibold text-slate-700">Minggu ke-<input type="number" name="week_number" min="1" max="53" value="{{ old('week_number') }}" required class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"></label>
                    <label class="text-sm font-semibold text-slate-700">Tanggal awal<input type="date" name="starts_on" value="{{ old('starts_on') }}" required class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"></label>
                    <label class="text-sm font-semibold text-slate-700">Tanggal akhir<input type="date" name="ends_on" value="{{ old('ends_on') }}" required class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"></label>
                </div>
                <label class="block text-sm font-semibold text-slate-700">File XLSX/XLS<input name="file" type="file" accept=".xlsx,.xls" required class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm"></label>
                @if ($errors->any())<p role="alert" class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</p>@endif
                @if (session('import_errors'))<ul class="max-h-40 list-inside list-disc overflow-auto rounded-xl bg-rose-50 p-3 text-sm text-rose-700">@foreach (session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" data-attendance-import-close class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Batal</button><x-button type="submit"><i class="fa-solid fa-upload"></i> Import TH</x-button></div>
            </form>
        </div>
    </dialog>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('attendance-import-modal');
                const weekSelect = document.getElementById('delete-attendance-week');
                const weekForm = document.getElementById('delete-attendance-week-form');
                if (weekSelect && weekForm) {
                    weekForm.querySelector('[type="submit"]').disabled = true;
                    weekSelect.addEventListener('change', () => {
                        weekForm.action = weekSelect.value || weekForm.action;
                        weekForm.querySelector('[type="submit"]').disabled = !weekSelect.value;
                        weekForm.dataset.confirmText = weekSelect.value ? `${weekSelect.selectedOptions[0].dataset.label}: seluruh catatan akan dihapus permanen.` : '';
                    });
                }
                document.body.appendChild(modal);
                document.getElementById('attendance-import-open')?.addEventListener('click', () => modal.showModal());
                modal.querySelectorAll('[data-attendance-import-close]').forEach(button => button.addEventListener('click', () => modal.close()));
                modal.addEventListener('click', event => { if (event.target === modal) modal.close(); });
                @if ($errors->any() || session('import_errors')) modal.showModal(); @endif
            });
        </script>
    @endpush
@endcomponent

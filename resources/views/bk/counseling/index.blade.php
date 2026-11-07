@component('layouts.app', ['title' => 'Konseling'])
    <x-page-header title="Konseling" description="Catat pertemuan dan pantau tindak lanjut siswa dalam satu tempat.">
        <x-slot:actions>
            <x-button :href="route('bk.counseling.create')"><i class="fa-solid fa-plus" aria-hidden="true"></i> Catat sesi</x-button>
        </x-slot:actions>
    </x-page-header>

    @php
        $activeFilters = collect($filters)->filter(fn ($value) => filled($value))->count();
        $advancedOpen = filled($filters['cohort_id'] ?? null) || filled($filters['class_id'] ?? null)
            || filled($filters['from'] ?? null) || filled($filters['to'] ?? null);
    @endphp

    <div class="space-y-5">
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['label' => 'Total sesi', 'value' => $totalCount, 'icon' => 'fa-comments', 'color' => 'bg-blue-50 text-blue-800'],
                ['label' => 'Perlu tindak lanjut', 'value' => $pendingCount, 'icon' => 'fa-list-check', 'color' => 'bg-amber-50 text-amber-700'],
                ['label' => 'Lewat tenggat', 'value' => $overdueCount, 'icon' => 'fa-clock', 'color' => 'bg-rose-50 text-rose-700'],
            ] as $stat)
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</p><p class="mt-1 text-2xl font-extrabold tabular-nums text-slate-900">{{ $stat['value'] }}</p></div>
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $stat['color'] }}"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                </div>
            @endforeach
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="counseling-filter-title">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div><h2 id="counseling-filter-title" class="font-bold text-slate-900">Cari catatan</h2><p class="mt-0.5 text-xs text-slate-500">Gunakan nama atau NIS untuk menemukan sesi siswa.</p></div>
                @if ($activeFilters) <x-badge variant="info">{{ $activeFilters }} filter aktif</x-badge> @endif
            </div>
            <form id="counseling-list-filters" method="GET" action="{{ route('bk.counseling.index') }}" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-xs font-bold text-slate-600">Status tindak lanjut
                        <select name="status" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua status</option>
                            <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>Perlu tindak lanjut</option>
                            <option value="overdue" @selected(($filters['status'] ?? null) === 'overdue')>Lewat tenggat</option>
                            <option value="done" @selected(($filters['status'] ?? null) === 'done')>Selesai</option>
                            <option value="none" @selected(($filters['status'] ?? null) === 'none')>Tanpa tindak lanjut</option>
                        </select>
                    </label>
                    <label class="block text-xs font-bold text-slate-600">Jenis konseling
                        <select name="type" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua jenis</option>
                            @foreach ($types as $type) <option value="{{ $type }}" @selected(($filters['type'] ?? null) === $type)>{{ ucfirst($type) }}</option> @endforeach
                        </select>
                    </label>
                </div>
                <details class="group border-t border-slate-100 pt-3" @if ($advancedOpen) open @endif>
                    <summary class="flex cursor-pointer list-none items-center gap-2 text-xs font-bold text-blue-800 marker:hidden"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filter lanjutan <i class="fa-solid fa-chevron-down text-[10px] transition group-open:rotate-180" aria-hidden="true"></i></summary>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="block text-xs font-bold text-slate-600">Angkatan
                            <select name="cohort_id" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><option value="">Semua angkatan</option>@foreach ($cohorts as $cohort)<option value="{{ $cohort->id }}" @selected(($filters['cohort_id'] ?? null) == $cohort->id)>{{ $cohort->name }}</option>@endforeach</select>
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Kelas
                            <select name="class_id" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><option value="">Semua kelas</option>@foreach ($classes as $class)<option value="{{ $class->id }}" @selected(($filters['class_id'] ?? null) == $class->id)>{{ $class->name }}</option>@endforeach</select>
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Dari tanggal
                            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Sampai tanggal
                            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </label>
                    </div>
                </details>
                <div class="flex flex-wrap items-center gap-2">
                    <x-button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Tampilkan hasil</x-button>
                    @if ($activeFilters) <x-button variant="secondary" :href="route('bk.counseling.index')">Hapus filter</x-button> @endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="counseling-list-title">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-4 sm:px-5">
                <div><h2 id="counseling-list-title" class="font-bold text-slate-900">Daftar sesi</h2><p class="mt-0.5 text-xs text-slate-500">Cari, urutkan, dan telusuri seluruh catatan.</p></div>
                 <div class="flex flex-wrap items-center gap-2">
                     <a data-counseling-export="xlsx" href="{{ route('bk.counseling.export', 'xlsx') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100"><i class="fa-solid fa-file-excel" aria-hidden="true"></i> Excel</a>
                     <a data-counseling-export="pdf" href="{{ route('bk.counseling.export', 'pdf') }}" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF</a>
                 </div>
            </div>
            <div class="overflow-x-auto p-4 sm:p-5">
                <table id="counseling-table" class="w-full min-w-[850px] text-left text-sm">
                    <thead><tr><th>Tanggal</th><th>Siswa</th><th>Jenis / Topik</th><th>Tindak lanjut</th><th>Pencatat</th><th>Aksi</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const table = window.initDataTable('#counseling-table', {
                    serverSide: true,
                    ajax: {
                        url: @json(route('bk.counseling.data')),
                        data: function (params) {
                            const form = document.getElementById('counseling-list-filters');
                            new FormData(form).forEach((value, key) => { if (value) params[key] = value; });
                        },
                    },
                    columns: [
                        { data: 'session_date', name: 'held_at', orderable: true },
                        { data: 'student_name', name: 'student_name', orderable: false },
                        { data: 'session_topic', name: 'topic' },
                        { data: 'follow_up_status', name: 'follow_up_status', orderable: false },
                        { data: 'creator_name', name: 'creator_name', orderable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                    order: [[0, 'desc']],
                });
                document.getElementById('counseling-list-filters').addEventListener('submit', event => {
                    event.preventDefault();
                    table.ajax.reload();
                });
                document.querySelectorAll('[data-counseling-export]').forEach(link => link.addEventListener('click', () => {
                    const url = new URL(link.href);
                    url.search = '';
                    new FormData(document.getElementById('counseling-list-filters')).forEach((value, key) => {
                        if (value) url.searchParams.set(key, value);
                    });
                    const search = table.search().trim();
                    if (search) url.searchParams.set('search', search);
                    link.href = url.toString();
                }));
            });
        </script>
    @endpush
@endcomponent

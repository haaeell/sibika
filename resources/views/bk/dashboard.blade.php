@component('layouts.app', ['title' => 'Dashboard BK'])
    <x-page-header title="Dashboard BK" description="Monitoring biodata, kesehatan, dan kesiapan siswa." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <x-card class="p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stat['value'] }}</p></div><span class="flex size-11 items-center justify-center rounded-xl {{ $stat['class'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></span></div></x-card>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-card title="Progress Biodata per Kelas" description="Rata-rata kelengkapan biodata siswa.">
            <div class="space-y-3">
                @foreach ($classProgress as $class)
                    <div><div class="mb-1 flex justify-between text-sm font-bold"><span>{{ $class['name'] }}</span><span>{{ $class['progress'] }}%</span></div><div class="h-2 rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $class['progress'] }}%"></div></div><p class="mt-1 text-xs text-slate-400">{{ $class['students'] }} siswa</p></div>
                @endforeach
            </div>
        </x-card>

        <div class="space-y-6">
            <x-card title="Ringkasan BK" description="Kondisi yang perlu dipantau.">
                <div class="grid gap-3"><div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-400">Rata-rata progress</p><p class="text-2xl font-extrabold text-blue-900">{{ $averageProgress }}%</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-400">Riwayat kesehatan</p><p class="text-2xl font-extrabold text-orange-700">{{ $healthAttention }} siswa</p></div><x-button :href="route('bk.biodata.report')"><i class="fa-solid fa-chart-pie"></i> Laporan Biodata</x-button></div>
            </x-card>
            <x-card title="Prioritas Tindak Lanjut" description="Siswa dengan biodata belum lengkap.">
                <div class="space-y-2">@forelse ($incompleteStudents as $student)<a href="{{ route('bk.students.biodata.show', $student) }}" class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold hover:bg-blue-50"><span>{{ $student->name }}</span><span class="text-blue-800">{{ $student->progress['percentage'] }}%</span></a>@empty<p class="text-sm font-semibold text-emerald-600">Semua biodata lengkap.</p>@endforelse</div>
            </x-card>
        </div>
    </div>
@endcomponent

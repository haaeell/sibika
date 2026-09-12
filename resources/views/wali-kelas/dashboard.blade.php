@component('layouts.app', ['title' => 'Dashboard Wali Kelas'])
    <x-page-header title="Dashboard Wali Kelas" :description="$teacher ? 'Monitoring kelas wali '.$teacher->name : 'Akun belum terhubung ke data guru wali kelas.'" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <x-card class="p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stat['value'] }}</p></div><span class="flex size-11 items-center justify-center rounded-xl {{ $stat['class'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></span></div></x-card>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-card title="Kelas Wali" description="Kelas yang menjadi tanggung jawab Anda.">
            <div class="space-y-3">
                @forelse ($classes as $class)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="font-bold text-slate-900">{{ $class->name }}</p><p class="text-sm font-semibold text-slate-500">{{ $class->academicYear?->name ?? '-' }}</p><p class="mt-2 text-xs font-bold text-blue-800">{{ $class->students->count() }} siswa</p></div>
                @empty
                    <x-empty-state icon="fa-solid fa-school" title="Belum ada kelas wali" description="Hubungi BK untuk mengatur wali kelas." />
                @endforelse
            </div>
        </x-card>

        <div class="space-y-6">
            <x-card title="Progress Kelas" description="Rata-rata kelengkapan biodata."><p class="text-5xl font-extrabold text-blue-900">{{ $averageProgress }}%</p><div class="mt-4 h-3 rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $averageProgress }}%"></div></div></x-card>
            <x-card title="Siswa Belum Lengkap" description="Prioritas tindak lanjut wali kelas.">
                <div class="space-y-2">@forelse ($incompleteStudents as $student)<div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold"><span>{{ $student->name }}</span><span class="text-blue-800">{{ $student->progress['percentage'] }}%</span></div>@empty<p class="text-sm font-semibold text-emerald-600">Semua biodata lengkap.</p>@endforelse</div>
            </x-card>
        </div>
    </div>
@endcomponent

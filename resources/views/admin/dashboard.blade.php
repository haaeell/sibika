@component('layouts.app', ['title' => 'Dashboard Admin'])
    <x-page-header title="Dashboard Admin" description="Ringkasan sistem dan master data utama." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <x-card class="p-4"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stat['value'] }}</p></div><span class="flex size-11 items-center justify-center rounded-xl {{ $stat['class'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></span></div></x-card>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-card title="Kelas Terbaru" description="Kelas dan wali kelas pada tahun ajaran aktif.">
            <div class="space-y-3">
                @forelse ($recentClasses as $class)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3"><div><p class="font-bold text-slate-900">{{ $class->name }}</p><p class="text-xs font-semibold text-slate-500">{{ $class->academicYear?->name ?? '-' }} · {{ $class->homeroomTeacher?->name ?? 'Tanpa wali' }}</p></div><span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-blue-800">{{ $class->students_count }} siswa</span></div>
                @empty
                    <x-empty-state icon="fa-solid fa-school" title="Belum ada kelas" description="Tambahkan kelas pada Master Data." />
                @endforelse
            </div>
        </x-card>

        <x-card title="Akses Master" description="Kelola data pokok sistem.">
            <div class="space-y-2">
                @foreach ($masters as $item)
                    <a href="{{ $item['route'] }}" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-900"><i class="fa-solid {{ $item['icon'] }} w-5 text-center text-blue-800"></i><span class="flex-1">{{ $item['label'] }}</span><span class="rounded-full bg-white px-2 py-0.5 text-xs text-slate-500">{{ $item['value'] }}</span></a>
                @endforeach
            </div>
        </x-card>
    </div>
@endcomponent

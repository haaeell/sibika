@forelse ($topStudents as $top)
    <a href="{{ route('bk.violations.student', $top->id) }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
        <span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-900">{{ $top->name }}</span><span class="block text-xs text-slate-500">{{ $top->nis }}</span></span>
        <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-extrabold text-blue-800">{{ $top->total }}×</span>
    </a>
@empty
    <p class="text-sm text-slate-500 sm:col-span-2 xl:col-span-5">Belum ada pelanggaran sesuai filter.</p>
@endforelse

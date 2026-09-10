@props([
    'title' => 'Dashboard',
])

<header {{ $attributes->merge(['class' => 'flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6']) }}>
    <div class="flex items-center gap-3">
        <button type="button" class="js-sidebar-open inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" aria-label="Buka menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div>
            <p class="text-xs font-medium text-slate-500">E-Learning BK</p>
            <h1 class="text-base font-bold text-slate-900">{{ $title }}</h1>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50" aria-label="Notifikasi">
            <i class="fa-solid fa-bell"></i>
        </button>

        <div class="hidden items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 sm:flex">
            <div class="flex size-8 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-700">
                {{ strtoupper(substr(auth()->user()->name ?? 'Guest', 0, 1)) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Guest' }}</p>
                <p class="text-xs text-slate-500">Template setup</p>
            </div>
        </div>
    </div>
</header>

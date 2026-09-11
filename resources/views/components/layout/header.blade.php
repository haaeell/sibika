@props([
    'title' => 'Dashboard',
])

<header {{ $attributes->merge(['class' => 'flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6']) }}>
    <div class="flex items-center gap-3">
        <button type="button" class="js-sidebar-layout-toggle inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2" aria-label="Buka atau tutup menu" aria-expanded="true">
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

        <div class="relative">
            <button type="button" class="js-user-menu-toggle flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-2 py-2 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 sm:px-3" aria-expanded="false" aria-controls="user-menu">
                <span class="flex size-8 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-900">
                    {{ strtoupper(substr(auth()->user()->name ?? 'Guest', 0, 1)) }}
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <span class="block text-xs text-slate-500">{{ auth()->user()->email ?? 'guest@example.test' }}</span>
                </span>
                <i class="hidden fa-solid fa-chevron-down text-xs text-slate-400 sm:inline-block"></i>
            </button>

            <div id="user-menu" class="absolute right-0 z-50 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-xl" data-user-menu>
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="truncate text-sm font-bold text-slate-900">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->email ?? 'guest@example.test' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

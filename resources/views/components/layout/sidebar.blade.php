@props([
    'navigation' => [],
    'idPrefix' => 'sidebar',
])

<aside {{ $attributes->merge(['class' => 'flex h-full w-64 flex-col border-r border-slate-200 bg-white']) }}>
    <div class="flex h-16 items-center justify-between gap-3 border-b border-slate-200 px-5">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="size-10 object-contain">
             <span>
                <span class="block text-sm font-bold text-slate-900">SIBIKA</span>
                <span class="block text-xs text-slate-500">SMA Plus Astha Hannas</span>
            </span>
        </a>
        <button type="button" class="js-sidebar-close inline-flex size-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden" aria-label="Tutup menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-3 overflow-y-auto px-3 py-5">
        @foreach ($navigation as $groupIndex => $group)
            @php
                $items = collect($group['items'])->reject(fn ($item) => ($item['disabled'] ?? false) || ! empty($item['badge']));
                $hasLabel = ! empty($group['label']);
                $panelId = $idPrefix.'-menu-'.$groupIndex;
                $isOpen = true;
            @endphp

            @continue($items->isEmpty())

            <div class="sidebar-group">
                @if ($hasLabel)
                    <button
                        type="button"
                        class="js-sidebar-toggle flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-xs font-bold uppercase text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                        aria-controls="{{ $panelId }}"
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    >
                        <span>{{ $group['label'] }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition {{ $isOpen ? '' : '-rotate-90' }}" data-sidebar-chevron></i>
                    </button>
                @endif

                <div id="{{ $panelId }}" class="{{ $hasLabel ? 'mt-1' : '' }} space-y-1 {{ $isOpen ? '' : 'hidden' }}" data-sidebar-panel>
                    @foreach ($items as $item)
                        @php
                            $isActive = $item['active'] ?? false;
                            $itemClass = $isActive
                                ? 'bg-blue-50 text-blue-900'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
                        @endphp

                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $itemClass }}">
                            <i class="{{ $item['icon'] }} w-5 text-center"></i>
                            <span class="min-w-0 flex-1">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-slate-100 px-3 py-4">
        <div class="relative">
            <button
                type="button"
                class="js-academic-year-toggle flex w-full items-center gap-3 rounded-2xl bg-slate-50 px-3 py-3 text-left transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2"
                aria-expanded="false"
                aria-controls="{{ $idPrefix }}-academic-year-menu"
            >
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-semibold text-slate-500">Tahun Ajaran</span>
                    <span class="js-academic-year-label block truncate text-base font-bold text-slate-900" data-initial-year="{{ $activeAcademicYear ?? '2026 / 2027' }}">{{ $activeAcademicYear ?? '2026 / 2027' }}</span>
                </span>
                <span class="flex flex-col text-slate-400">
                    <i class="fa-solid fa-chevron-up text-[10px] leading-none"></i>
                    <i class="fa-solid fa-chevron-down text-[10px] leading-none"></i>
                </span>
            </button>

            <div
                id="{{ $idPrefix }}-academic-year-menu"
                class="absolute bottom-full left-0 right-0 z-50 mb-2 hidden max-h-60 overflow-y-auto rounded-2xl border border-slate-200 bg-white py-2 shadow-xl"
                data-academic-year-menu
            >
                @forelse ($academicYears ?? [] as $academicYear)
                    <button
                        type="button"
                        class="js-academic-year-option flex w-full items-center justify-between px-4 py-2.5 text-left text-sm font-semibold transition hover:bg-blue-50 hover:text-blue-900 {{ ($activeAcademicYear ?? null) === $academicYear->name ? 'bg-blue-50 text-blue-900' : 'text-slate-600' }}"
                        data-academic-year="{{ $academicYear->name }}"
                    >
                        <span class="flex flex-col text-left">
                            <span>{{ $academicYear->name }}</span>
                            <span class="text-xs font-medium {{ $academicYear->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $academicYear->is_active ? 'Aktif' : '' }} {{ $academicYear->semester ? '· '.ucfirst($academicYear->semester) : '' }}</span>
                        </span>
                        <i class="fa-solid fa-check text-xs text-blue-700 {{ ($activeAcademicYear ?? null) === $academicYear->name ? '' : 'hidden' }}" data-academic-year-check></i>
                    </button>
                @empty
                    <div class="px-4 py-3 text-sm text-slate-500">Belum ada tahun ajaran</div>
                @endforelse
                <a href="{{ route('bk.academic-years.index') }}" class="mt-1 flex items-center gap-2 border-t border-slate-100 px-4 py-2.5 text-xs font-bold text-blue-700 hover:bg-slate-50">
                    <i class="fa-solid fa-gear"></i> Kelola Tahun Ajaran
                </a>
            </div>
        </div>
    </div>
</aside>

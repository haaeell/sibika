@props([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum ada data',
    'description' => 'Data yang tersedia akan muncul di halaman ini.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <div class="flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
        <i class="{{ $icon }}"></i>
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
</div>

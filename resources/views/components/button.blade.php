@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $baseClass = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    $variantClass = match ($variant) {
        'secondary' => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-300',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500',
        default => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    };
@endphp

@if ($href)
    <a {{ $attributes->merge(['href' => $href, 'class' => $baseClass.' '.$variantClass]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => $baseClass.' '.$variantClass]) }}>
        {{ $slot }}
    </button>
@endif

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10']) }}
    >

    @if ($help)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif

    <x-form.error :name="$name" />
</div>

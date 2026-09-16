@props([
    'name',
    'label' => null,
    'type' => 'text',
    'help' => null,
    'icon' => null,
    'required' => false,
])

    <div class="min-w-0">
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400">
                <i class="{{ $icon }}"></i>
            </span>
        @endif

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'min-w-0 w-full rounded-xl border border-slate-300 bg-white '.($icon ? 'pl-12 pr-3.5' : 'px-3.5').' py-2.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10 read-only:cursor-not-allowed read-only:border-slate-300 read-only:bg-slate-200 read-only:text-slate-700 read-only:focus:border-slate-300 read-only:focus:ring-0 sm:text-sm']) }}
        >
    </div>

    @if ($help)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif

    <x-form.error :name="$name" />
</div>

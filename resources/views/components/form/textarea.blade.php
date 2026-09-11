@props([
    'name',
    'label' => null,
    'help' => null,
    'icon' => null,
    'value' => '',
    'required' => false,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700">
            {{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute left-0 top-0 flex h-11 w-12 items-center justify-center text-slate-400">
                <i class="{{ $icon }}"></i>
            </span>
        @endif

        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="4"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-300 bg-white '.($icon ? 'pl-12 pr-3.5' : 'px-3.5').' py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10']) }}
        >{{ $value ?: $slot }}</textarea>
    </div>

    @if ($help)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif

    <x-form.error :name="$name" />
</div>

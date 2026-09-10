@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm']) }}>
    @if ($title || $description)
        <div class="mb-5">
            @if ($title)
                <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>

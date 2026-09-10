@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-xs font-medium text-rose-600']) }}>
        {{ $message }}
    </p>
@enderror

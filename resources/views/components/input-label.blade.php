@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-slate-800 dark:text-slate-100 mb-1']) }}>
    {{ $value ?? $slot }}
</label>

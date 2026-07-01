@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-800']) }}>
    {{ $value ?? $slot }}
</label>

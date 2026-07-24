@props(['label', 'type' => 'text', 'name'])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-800">{{ $label }}</label>
    <input 
        type="{{ $type }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="{{ old($name, $attributes->get('value')) }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30 sm:text-sm']) }}
    />
    @error($name)
        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
    @enderror
</div>

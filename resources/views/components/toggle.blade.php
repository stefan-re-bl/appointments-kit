@props(['label', 'name'])

<div class="mb-4 flex items-center">
    <input 
        type="checkbox" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="1"
        {{ old($name) ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-indigo-300 text-slate-600 focus:ring-indigo-500/30']) }}
    />
    <label for="{{ $name }}" class="ml-2 block text-sm text-slate-950">{{ $label }}</label>
    @error($name)
        <p class="ml-2 text-sm text-rose-700">{{ $message }}</p>
    @enderror
</div>

@props(['label', 'name'])

<div class="mb-4 flex items-center">
    <input 
        type="checkbox" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="1"
        {{ old($name) ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500']) }}
    />
    <label for="{{ $name }}" class="ml-2 block text-sm text-gray-900">{{ $label }}</label>
    @error($name)
        <p class="ml-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
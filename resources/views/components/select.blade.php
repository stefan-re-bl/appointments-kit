@props(['label', 'name', 'options' => [], 'selected' => null])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-800">{{ $label }}</label>
    <select 
        id="{{ $name }}" 
        name="{{ $name }}" 
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30 sm:text-sm']) }}
    >
        @foreach($options as $value => $text)
            <option value="{{ $value }}" @selected((string) old($name, $selected) === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
    @enderror
</div>

@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-brand-accent bg-slate-50 py-2 pe-4 ps-3 text-start text-base font-medium text-slate-700 transition duration-150 ease-in-out focus:border-brand-accent focus:bg-brand-accent-soft focus:text-slate-800 focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 focus:border-brand-accent focus:bg-slate-50 focus:text-slate-800 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

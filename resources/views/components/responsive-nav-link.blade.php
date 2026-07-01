@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-indigo-500 bg-slate-50 py-2 pe-4 ps-3 text-start text-base font-medium text-slate-700 transition duration-150 ease-in-out focus:border-indigo-700 focus:bg-indigo-50 focus:text-slate-800 focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 focus:border-indigo-300 focus:bg-slate-50 focus:text-slate-800 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

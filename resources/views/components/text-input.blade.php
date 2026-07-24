@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-slate-200 bg-white text-slate-950 shadow-sm placeholder:text-slate-300 focus:border-umbralia-accent focus:ring-umbralia-accent/30 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400']) }}>

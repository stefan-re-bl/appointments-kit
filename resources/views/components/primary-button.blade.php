<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg border border-transparent bg-brand-title px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition duration-150 ease-in-out hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2 active:bg-brand-title/90 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>

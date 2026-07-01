<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg border border-transparent bg-rose-800 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition duration-150 ease-in-out hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 active:bg-rose-900 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>

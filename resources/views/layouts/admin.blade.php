<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('app.admin.layout.title') }} - {{ config('app.name', 'Umbralia') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-slate-950 text-slate-100 antialiased">
    <div
        x-data="{ sidebarOpen: false }"
        class="flex h-full overflow-hidden"
    >
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-white/10 bg-slate-950/95 backdrop-blur transition-transform duration-200 lg:static lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex h-16 items-center justify-between border-b border-white/10 px-6">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500 text-sm font-bold text-white">
                        U
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-white">{{ __('app.admin.layout.brand') }}</span>
                        <span class="block text-xs text-slate-400">{{ __('app.admin.layout.subtitle') }}</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
                    @click="sidebarOpen = false"
                >
                    <span class="sr-only">{{ __('app.admin.layout.close_menu') }}</span>
                    ✕
                </button>
            </div>

            <nav class="space-y-1 px-4 py-5">
                <a
                    href="{{ route('admin.appointments.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-500/15 text-indigo-200 ring-1 ring-indigo-400/20' => request()->routeIs('admin.appointments.*'),
                        'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.appointments.*'),
                    ])
                >
                    <span class="mr-3">📅</span>
                    {{ __('app.admin.nav.appointments') }}
                </a>

                <a
                    href="{{ route('admin.therapists.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-500/15 text-indigo-200 ring-1 ring-indigo-400/20' => request()->routeIs('admin.therapists.*'),
                        'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.therapists.*'),
                    ])
                >
                    <span class="mr-3">🧑‍⚕️</span>
                    {{ __('app.admin.nav.therapists') }}
                </a>

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
                >
                    <span class="mr-3">↩</span>
                    {{ __('app.admin.nav.main_dashboard') }}
                </a>
            </nav>
        </aside>

        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-30 bg-black/60 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <div class="min-w-0 flex flex-1 flex-col overflow-hidden">
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 bg-slate-950/80 px-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <span class="sr-only">{{ __('app.admin.layout.open_menu') }}</span>
                        ☰
                    </button>

                    <div>
                        <h1 class="text-base font-semibold text-white sm:text-lg">
                            {{ $header ?? __('app.admin.layout.title') }}
                        </h1>
                        <p class="hidden text-xs text-slate-400 sm:block">
                            {{ __('app.admin.layout.operation_visibility') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-200 sm:inline-flex">
                        {{ __('app.admin.layout.admin_role') }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl border border-white/10 px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"
                        >
                            {{ __('app.admin.layout.logout') }}
                        </button>
                    </form>
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-slate-100 text-slate-950">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
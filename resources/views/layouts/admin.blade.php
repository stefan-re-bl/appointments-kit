<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-indigo-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('app.admin.layout.title') }} - {{ config('app.name', 'Umbralia') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-indigo-950 text-slate-100 antialiased">
    <div
        x-data="{ sidebarOpen: false }"
        class="flex h-full overflow-hidden"
    >
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-white/10 bg-indigo-950/95 backdrop-blur transition-transform duration-200 lg:static lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex h-16 items-center justify-between border-b border-white/10 px-6">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-700 text-sm font-bold text-white ring-1 ring-amber-200/40">
                        U
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-white">{{ __('app.admin.layout.brand') }}</span>
                        <span class="block text-xs text-slate-300">{{ __('app.admin.layout.subtitle') }}</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-300 hover:bg-white/10 hover:text-white lg:hidden"
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
                        'bg-indigo-700/30 text-white ring-1 ring-amber-200/20' => request()->routeIs('admin.appointments.*'),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.appointments.*'),
                    ])
                >
                    <span class="mr-3">📅</span>
                    {{ __('app.admin.nav.appointments') }}
                </a>

                <a
                    href="{{ route('admin.therapists.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-700/30 text-white ring-1 ring-amber-200/20' => request()->routeIs('admin.therapists.*'),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.therapists.*'),
                    ])
                >
                    <span class="mr-3">🧑‍⚕️</span>
                    {{ __('app.admin.nav.therapists') }}
                </a>

                <a
                    href="{{ route('admin.activity-logs.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-700/30 text-white ring-1 ring-amber-200/20' => request()->routeIs('admin.activity-logs.*'),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.activity-logs.*'),
                    ])
                >
                    <span class="mr-3">◎</span>
                    {{ __('app.admin.nav.activity_logs') }}
                </a>

                <a
                    href="{{ route('admin.contact-messages.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-700/30 text-white ring-1 ring-amber-200/20' => request()->routeIs('admin.contact-messages.*'),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.contact-messages.*'),
                    ])
                >
                    <span class="mr-3">✉</span>
                    {{ __('app.admin.nav.contact_messages') }}
                </a>

                <a
                    href="{{ route('admin.reports.appointments.index') }}"
                    @class([
                        'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                        'bg-indigo-700/30 text-white ring-1 ring-amber-200/20' => request()->routeIs('admin.reports.*'),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.reports.*'),
                    ])
                >
                    <span class="mr-3">📊</span>
                    {{ __('reports.nav.appointment_reports') }}
                </a>

            </nav>
        </aside>

        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-30 bg-indigo-950/70 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <div class="min-w-0 flex flex-1 flex-col overflow-hidden">
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 bg-indigo-950/85 px-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-300 hover:bg-white/10 hover:text-white lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <span class="sr-only">{{ __('app.admin.layout.open_menu') }}</span>
                        ☰
                    </button>

                    <div>
                        <h1 class="text-base font-semibold text-white sm:text-lg">
                            {{ $header ?? __('app.admin.layout.title') }}
                        </h1>
                        <p class="hidden text-xs text-slate-300 sm:block">
                            {{ __('app.admin.layout.operation_visibility') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full border border-amber-200/30 bg-amber-200/10 px-3 py-1 text-xs font-medium text-amber-100 sm:inline-flex">
                        {{ __('app.admin.layout.admin_role') }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl border border-white/10 px-3 py-2 text-sm font-medium text-slate-100 hover:bg-white/10 hover:text-white"
                        >
                            {{ __('app.admin.layout.logout') }}
                        </button>
                    </form>
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-[#fbf9fc] text-slate-950">
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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-[#fbf9fc] font-sans text-slate-950 antialiased">
    <!-- Script de Zona Horaria -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.cookie = `user_timezone=${timezone}; path=/; samesite=lax`;
        });
    </script>

    <div x-data="{ sidebarOpen: false }" class="flex h-full overflow-hidden">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-64 flex-shrink-0 transform overflow-y-auto bg-indigo-950 transition duration-300 ease-in-out lg:static lg:inset-0 lg:translate-x-0"
        >
            <!-- Logo Area -->
            <div class="flex h-16 items-center bg-indigo-950 px-6 shadow-md ring-1 ring-white/10">
                <svg class="h-6 w-6 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    ></path>
                </svg>

                <span class="ml-3 text-xl font-bold tracking-wide text-white">
                    {{ __('app.app_name') }}
                </span>
            </div>

            <!-- Navegación -->
            <nav class="mt-6">
                <a
                    href="{{ url('/dashboard') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white ring-1 ring-white/10' : 'text-slate-200 hover:bg-indigo-900 hover:text-white' }} transition-colors duration-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        ></path>
                    </svg>

                    <span class="mx-3">{{ __('app.dashboard') }}</span>
                </a>

                <a
                    href="{{ route('session-types.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('session-types.*') ? 'bg-indigo-800 text-white ring-1 ring-white/10' : 'text-slate-200 hover:bg-indigo-900 hover:text-white' }} transition-colors duration-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>

                    <span class="mx-3">{{ __('app.session_types') }}</span>
                </a>

                <a
                    href="{{ route('availabilities.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('availabilities.*') ? 'bg-indigo-800 text-white ring-1 ring-white/10' : 'text-slate-200 hover:bg-indigo-900 hover:text-white' }} transition-colors duration-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>

                    <span class="mx-3">{{ __('app.availabilities') }}</span>
                </a>

                <a
                    href="{{ route('therapist.appointments.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('therapist.appointments.*') ? 'bg-indigo-800 text-white ring-1 ring-white/10' : 'text-slate-200 hover:bg-indigo-900 hover:text-white' }} transition-colors duration-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>

                    <span class="mx-3">{{ __('app.appointments.management.title') }}</span>
                </a>

                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('profile.*') ? 'bg-indigo-800 text-white ring-1 ring-white/10' : 'text-slate-200 hover:bg-indigo-900 hover:text-white' }} transition-colors duration-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>

                    <span class="mx-3">{{ __('app.dashboard_onboarding.quick_links.profile') }}</span>
                </a>
            </nav>
        </aside>

        <!-- Contenedor Principal -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="z-20 flex h-16 flex-shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
                <div class="flex items-center">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="rounded-lg p-2 text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 lg:hidden"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Switcher de Idioma -->
                    <div x-data="{ langOpen: false }" class="relative">
                        <button
                            @click="langOpen = !langOpen"
                            class="flex items-center text-sm text-slate-600 hover:text-slate-950 focus:outline-none"
                        >
                            <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                                ></path>
                            </svg>

                            <span class="hidden sm:inline">{{ strtoupper(app()->getLocale()) }}</span>
                        </button>

                        <div
                            x-show="langOpen"
                            @click.away="langOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 mt-2 w-36 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                        >
                            <a
                                href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}"
                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 {{ app()->getLocale() === 'es' ? 'font-bold text-slate-600' : '' }}"
                            >
                                🇪🇸 {{ __('app.spanish') }}
                            </a>

                            <a
                                href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                                class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 {{ app()->getLocale() === 'en' ? 'font-bold text-slate-600' : '' }}"
                            >
                                🇺🇸 {{ __('app.english') }}
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown de Usuario -->
                    <div class="flex items-center" x-data="{ userMenuOpen: false }">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="relative flex min-w-0 items-center space-x-3 focus:outline-none"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-700 text-xs font-bold text-white shadow-sm">
                                {{ strtoupper(Auth::user()->name[0]) }}
                            </div>

                            <span class="hidden max-w-44 truncate text-sm font-medium text-slate-700 md:inline">
                                {{ Auth::user()->name }}
                            </span>

                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </button>

                        <div
                            x-show="userMenuOpen"
                            @click.away="userMenuOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-4 top-14 z-50 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg sm:right-6"
                        >
                            <div class="border-b border-slate-200 px-4 py-2">
                                <p class="text-xs text-slate-500">
                                    {{ __('app.role') }}:
                                    <span class="font-semibold text-slate-600">
                                        {{ Auth::user()->role->value }}
                                    </span>
                                </p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="flex w-full items-center px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-50"
                                >
                                    <svg class="mr-2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                        ></path>
                                    </svg>

                                    {{ __('app.log_out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-[#fbf9fc]">
                @isset($header)
                    <div class="border-b border-slate-200 bg-white shadow-sm">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <div class="min-w-0 max-w-full p-4 sm:p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Umbralia'))</title>
    <meta name="description" content="@yield('meta_description', __('app.home.meta_description'))">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fbf9fc] font-sans text-slate-950 antialiased">
    <header
        x-data="{ mobileOpen: false }"
        class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:h-[4.5rem] lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-slate-950">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-700 text-sm font-bold text-white ring-1 ring-indigo-300/60">U</span>
                <span>Umbralia</span>
            </a>

            <nav class="hidden items-center gap-7 lg:flex" aria-label="{{ __('app.home.nav.aria') }}">
                <a href="{{ route('information.how-it-works') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">
                    {{ __('app.home.nav.how_it_works') }}
                </a>
                <a href="{{ route('information.patients') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">
                    {{ __('information.nav.patients') }}
                </a>
                <a href="{{ route('information.payment-and-cancellation') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">
                    {{ __('information.nav.payment_and_cancellation') }}
                </a>
                <a href="{{ route('information.faq') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">
                    {{ __('app.home.nav.faq') }}
                </a>
                <a href="{{ route('contact.create') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">
                    {{ __('app.home.nav.contact') }}
                </a>
            </nav>

            <div class="hidden items-center gap-3 lg:flex">
                <a
                    href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'es' ? 'en' : 'es']) }}"
                    class="text-sm font-semibold text-indigo-700 hover:text-indigo-800"
                >
                    {{ app()->getLocale() === 'es' ? 'EN' : 'ES' }}
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">
                        {{ __('app.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">
                        {{ __('app.home.nav.login') }}
                    </a>
                @endauth

                <a
                    href="{{ route('book.index') }}"
                    class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2"
                >
                    {{ __('app.home.nav.book') }}
                </a>
            </div>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-50 lg:hidden"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen"
                aria-controls="public-mobile-menu"
                title="{{ __('app.home.nav.open_menu') }}"
            >
                <svg x-show="!mobileOpen" aria-hidden="true" viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg x-cloak x-show="mobileOpen" aria-hidden="true" viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div
            id="public-mobile-menu"
            x-cloak
            x-show="mobileOpen"
            x-transition
            class="border-t border-slate-200 bg-white px-4 py-5 shadow-lg lg:hidden"
        >
            <nav class="flex flex-col gap-4" aria-label="{{ __('app.home.nav.mobile_aria') }}">
                <a href="{{ route('information.how-it-works') }}" @click="mobileOpen = false" class="font-medium text-slate-700">{{ __('app.home.nav.how_it_works') }}</a>
                <a href="{{ route('information.patients') }}" @click="mobileOpen = false" class="font-medium text-slate-700">{{ __('information.nav.patients') }}</a>
                <a href="{{ route('information.payment-and-cancellation') }}" @click="mobileOpen = false" class="font-medium text-slate-700">{{ __('information.nav.payment_and_cancellation') }}</a>
                <a href="{{ route('information.faq') }}" @click="mobileOpen = false" class="font-medium text-slate-700">{{ __('app.home.nav.faq') }}</a>
                <a href="{{ route('contact.create') }}" @click="mobileOpen = false" class="font-medium text-slate-700">{{ __('app.home.nav.contact') }}</a>
                <div class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-4">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'es' ? 'en' : 'es']) }}" class="font-semibold text-slate-700">
                        {{ app()->getLocale() === 'es' ? 'English' : 'Español' }}
                    </a>
                    <a href="{{ route('book.index') }}" class="rounded-lg bg-indigo-700 px-4 py-2.5 font-semibold text-white">
                        {{ __('app.home.nav.book') }}
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer id="contact" class="border-t border-slate-200 bg-indigo-950 text-slate-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
            <div>
                <p class="text-xl font-bold text-white">Umbralia</p>
                <p class="mt-3 max-w-sm text-sm leading-6 text-slate-200">
                    {{ __('app.home.footer.description') }}
                </p>
            </div>

            <div>
                <p class="text-sm font-semibold text-white">{{ __('app.home.footer.explore') }}</p>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <a href="{{ route('information.how-it-works') }}" class="hover:text-white">{{ __('app.home.nav.how_it_works') }}</a>
                    <a href="{{ route('information.patients') }}" class="hover:text-white">{{ __('information.nav.patients') }}</a>
                    <a href="{{ route('information.payment-and-cancellation') }}" class="hover:text-white">{{ __('information.nav.payment_and_cancellation') }}</a>
                    <a href="{{ route('information.faq') }}" class="hover:text-white">{{ __('app.home.nav.faq') }}</a>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-white">{{ __('app.home.footer.access') }}</p>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <a href="{{ route('book.index') }}" class="hover:text-white">{{ __('app.home.nav.book') }}</a>
                    <a href="{{ route('login') }}" class="hover:text-white">{{ __('app.home.nav.login') }}</a>
                    <a href="{{ route('contact.create') }}" class="hover:text-white">{{ __('app.home.footer.contact') }}</a>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-white">{{ __('legal.footer.title') }}</p>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <a href="{{ route('legal.terms') }}" class="hover:text-white">{{ __('legal.nav.terms') }}</a>
                    <a href="{{ route('legal.privacy') }}" class="hover:text-white">{{ __('legal.nav.privacy') }}</a>
                    <a href="{{ route('legal.emergency-notice') }}" class="hover:text-white">{{ __('legal.nav.emergency') }}</a>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-slate-300 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <p>&copy; {{ date('Y') }} Umbralia. {{ __('app.all_rights_reserved') }}</p>
                <p>{{ __('legal.footer.disclaimer') }}</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';

            if (! document.cookie.includes('user_timezone=')) {
                document.cookie = `user_timezone=${timezone}; path=/; max-age=31536000; SameSite=Lax`;
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }} - @yield('title', __('app.booking_title'))
    </title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-slate-950 antialiased">
    <!-- Navbar Pública -->
    <nav class="border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="flex flex-shrink-0 items-center">
                        <a href="/" class="text-2xl font-bold tracking-tight text-slate-700">
                            {{ config('app.name', 'Appointments Kit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="flex-1">
        @isset($slot)
            <div class="mx-auto flex min-h-[calc(100vh-13rem)] w-full max-w-md flex-col justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white px-6 py-8 shadow-sm ring-1 ring-slate-200 sm:px-8">
                    {{ $slot }}
                </div>
            </div>
        @else
            @yield('content')
        @endisset
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between px-4 sm:px-6 md:flex-row lg:px-8">
            <p class="text-sm text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('app.all_rights_reserved') }}
            </p>

            <div class="mt-4 flex space-x-6 md:mt-0">
                <a href="{{ route('legal.privacy') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    {{ __('legal.nav.privacy') }}
                </a>

                <a href="{{ route('legal.terms') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    {{ __('legal.nav.terms') }}
                </a>

                <a href="{{ route('legal.emergency-notice') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    {{ __('legal.nav.emergency') }}
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            if (!document.cookie.includes('user_timezone=')) {
                document.cookie = `user_timezone=${timezone}; path=/; max-age=31536000; SameSite=Lax`;
                location.reload();
            }
        });
    </script>
</body>
</html>

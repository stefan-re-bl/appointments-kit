<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }} - @yield('title', __('app.booking_title'))
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-900 antialiased">
    <!-- Navbar Pública -->
    <nav class="border-b border-gray-200 bg-white shadow">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="flex flex-shrink-0 items-center">
                        <a href="/" class="text-2xl font-bold tracking-tight text-indigo-600">
                            {{ config('app.name', 'Umbralia') }}
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
                <div class="rounded-2xl bg-white px-6 py-8 shadow-sm ring-1 ring-gray-200 sm:px-8">
                    {{ $slot }}
                </div>
            </div>
        @else
            @yield('content')
        @endisset
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 bg-white py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between px-4 sm:px-6 md:flex-row lg:px-8">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('app.all_rights_reserved') }}
            </p>

            <div class="mt-4 flex space-x-6 md:mt-0">
                <a href="#" class="text-sm text-gray-400 hover:text-gray-500">
                    Privacy
                </a>

                <a href="#" class="text-sm text-gray-400 hover:text-gray-500">
                    Terms
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
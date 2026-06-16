<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ __('app.booking_title') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50">
    
    <!-- Navbar Pública -->
    <nav class="bg-white shadow border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-2xl font-bold text-indigo-600 tracking-tight">
                            Umbralia
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="py-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('app.all_rights_reserved') }}
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-gray-400 hover:text-gray-500 text-sm">
                    Privacy
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500 text-sm">
                    Terms
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            
            // Verificamos si la cookie ya existe para no recargar infinitamente
            if (!document.cookie.includes('user_timezone=')) {
                document.cookie = `user_timezone=${timezone}; path=/; max-age=31536000`; // 1 año
                // Recargamos la página para aplicar el timezone inmediatamente
                location.reload(); 
            }
        });
    </script>
</body>
</html>
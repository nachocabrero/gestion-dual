<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} — Portfolio</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <!-- Header público -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="IES Hermenegildo Lanz" class="h-12 w-auto">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">IES Hermenegildo Lanz</h1>
                        <p class="text-sm text-gray-500">Portfolio de Proyectos — Informática</p>
                    </div>
                </div>
                <nav class="flex gap-4">
                    <a href="{{ route('proyectos.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        @auth
                            Mis Proyectos
                        @else
                            Login
                        @endauth
                    </a>
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} IES Hermenegildo Lanz — Granada</p>
            </div>
        </footer>
    </body>
</html>
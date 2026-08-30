<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gestión Académica IES') }}</title>

    <link rel="icon" href="{{ asset('images/logo-ieshlanz.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-slate-100 to-gray-200 min-h-screen flex flex-col">

    <div class="flex-1 flex flex-col lg:flex-row">
        <!-- Left side — branding -->
        <div class="lg:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 flex flex-col items-center justify-center p-8 lg:p-16 text-white relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-500/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full translate-x-1/3 translate-y-1/3"></div>

            <div class="relative z-10 text-center">
                <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Hermenegildo Lanz" class="h-24 w-auto bg-white rounded-xl px-4 py-3 mx-auto mb-8">
                <h1 class="text-3xl lg:text-4xl font-bold mb-4">IES Hermenegildo Lanz</h1>
                <p class="text-lg text-slate-300 max-w-md mx-auto">Sistema de Gestión Académica</p>
                <p class="text-sm text-slate-400 mt-4">Granada, España</p>

                <div class="mt-12 grid grid-cols-3 gap-6 max-w-sm mx-auto">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-400">1000+</div>
                        <div class="text-xs text-slate-400 mt-1">Alumnos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-400">100+</div>
                        <div class="text-xs text-slate-400 mt-1">Empresas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-400">50+</div>
                        <div class="text-xs text-slate-400 mt-1">Profesores</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side — form -->
        <div class="lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8">
                    {{ $slot }}
                </div>

                <!-- Footer links -->
                <div class="mt-6 text-center text-xs text-gray-500">
                    <p>© {{ date('Y') }} IES Hermenegildo Lanz</p>
                    <div class="flex items-center justify-center gap-3 mt-2">
                        <a href="{{ route('privacy') }}" class="hover:text-gray-700 transition-colors">Aviso Legal</a>
                        <span>·</span>
                        <a href="{{ route('privacy') }}" class="hover:text-gray-700 transition-colors">Privacidad</a>
                        <span>·</span>
                        <a href="{{ route('cookies') }}" class="hover:text-gray-700 transition-colors">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <x-cookie-banner />
</body>
</html>
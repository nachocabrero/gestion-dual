<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portfolio de Proyectos — IES Hermenegildo Lanz</title>

    <link rel="icon" type="image/webp" href="{{ asset('images/informaticahlanz_icon.webp') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen flex flex-col selection:bg-[#0048FE] selection:text-white">

    <!-- Glassmorphic Navigation Header -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <a href="{{ route('portfolio') }}" class="flex items-center gap-3.5 group">
                <div class="p-2 bg-slate-900 rounded-2xl shadow-md group-hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Hermenegildo Lanz" class="h-9 w-auto">
                </div>
                <div>
                    <h1 class="text-lg font-bold font-display text-slate-900 group-hover:text-[#0048FE] transition-colors leading-tight">IES Hermenegildo Lanz</h1>
                    <p class="text-xs text-slate-500 font-medium">Portfolio Público de Proyectos</p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('portfolio') }}#empresas" class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    🏢 Empresas
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="hlanz-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Ir a Mi Panel</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="hlanz-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Acceso Usuarios</span>
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Public Slot -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-[#0F172A] text-slate-400 py-12 border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-sm">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 bg-white rounded-xl border border-slate-800 shadow-sm flex items-center justify-center">
                        <img src="{{ asset('images/icon avatar hlanz.webp') }}" alt="Informática Lanz" class="h-8 w-auto">
                    </div>
                    <div>
                        <p class="font-bold text-white font-display">IES Politécnico Hermenegildo Lanz</p>
                        <p class="text-xs text-slate-400">Desarrollado por Departamento de Informática 2026</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 text-xs font-medium">
                    <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Aviso Legal</a>
                    <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Política de Privacidad</a>
                    <a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <x-cookie-banner />
</body>
</html>
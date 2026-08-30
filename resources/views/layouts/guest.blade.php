<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IES Hermenegildo Lanz') }} — Acceso</title>

    <link rel="icon" type="image/webp" href="{{ asset('images/informaticahlanz_icon.webp') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-100 min-h-screen flex flex-col selection:bg-[#0048FE] selection:text-white">

    <div class="flex-1 flex flex-col lg:flex-row min-h-screen">
        <!-- Left Hero Branding -->
        <div class="lg:w-1/2 bg-gradient-to-br from-[#0F172A] via-slate-900 to-[#0048FE] flex flex-col justify-between p-8 lg:p-16 text-white relative overflow-hidden">
            <!-- Decorative Subtle Shapes -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#0048FE]/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500/10 rounded-full blur-2xl translate-y-1/3 -translate-x-1/3"></div>

            <!-- Top Header Logo -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="p-2 bg-white rounded-xl shadow-lg">
                    <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Hermenegildo Lanz" class="h-10 w-auto">
                </div>
                <div>
                    <h2 class="text-lg font-bold font-display tracking-tight leading-none text-white">IES Politécnico</h2>
                    <p class="text-xs text-blue-300 font-medium">Hermenegildo Lanz</p>
                </div>
            </div>

            <!-- Hero Message Center -->
            <div class="relative z-10 my-auto py-12">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-400/30 mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#0048FE] animate-ping"></span>
                    Plataforma Oficial de Gestión Académica
                </span>
                <h1 class="text-3xl lg:text-5xl font-extrabold font-display leading-tight tracking-tight text-white mb-6">
                    Innovación, Formación y Desarrollo Profesional
                </h1>
                <p class="text-base text-slate-300 max-w-lg leading-relaxed">
                    Portal integral para el alumnado, profesorado y empresas colaboradoras. Gestión de calificaciones, tutorías, asignaciones de prácticas y portfolio tecnológico.
                </p>

                <!-- Quick Stats Grid -->
                <div class="mt-10 grid grid-cols-3 gap-4 border-t border-slate-800/80 pt-8 max-w-md">
                    <div>
                        <p class="text-2xl font-bold font-display text-white">+2.500</p>
                        <p class="text-xs text-slate-400 mt-1">Alumnos matriculados</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold font-display text-blue-400">+150</p>
                        <p class="text-xs text-slate-400 mt-1">Empresas en convenio</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold font-display text-cyan-400">100%</p>
                        <p class="text-xs text-slate-400 mt-1">Gestión digital FCT</p>
                    </div>
                </div>
            </div>

            <!-- Left Footer -->
            <div class="relative z-10 text-xs text-slate-400 flex items-center justify-between">
                <p>© {{ date('Y') }} IES Hermenegildo Lanz • Granada</p>
                <a href="{{ route('portfolio') }}" class="text-blue-300 hover:text-white transition-colors font-medium flex items-center gap-1">
                    Ver Portfolio Público →
                </a>
            </div>
        </div>

        <!-- Right Side Form Slot -->
        <div class="lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-slate-50">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 p-8 sm:p-10 relative overflow-hidden">
                    <!-- Accent top border line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#0048FE] via-cyan-400 to-emerald-400"></div>

                    {{ $slot }}
                </div>

                <!-- Footer Legal Links -->
                <div class="mt-8 text-center text-xs text-slate-500 space-y-2">
                    <div class="flex items-center justify-center gap-2 text-slate-600">
                        <img src="{{ asset('images/informaticahlanz_icon.webp') }}" alt="Informática Lanz" class="h-4 w-auto">
                        <span>Desarrollado por Departamento de Informática 2026</span>
                    </div>
                    <div class="flex items-center justify-center gap-4 font-medium">
                        <a href="{{ route('privacy') }}" class="hover:text-[#0048FE] transition-colors">Aviso Legal</a>
                        <span>•</span>
                        <a href="{{ route('privacy') }}" class="hover:text-[#0048FE] transition-colors">Política de Privacidad</a>
                        <span>•</span>
                        <a href="{{ route('cookies') }}" class="hover:text-[#0048FE] transition-colors">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <x-cookie-banner />
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $oferta->especialidad_requerida }} — Oferta de Prácticas | IES Hermenegildo Lanz</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 min-h-full flex flex-col">

    <!-- Top Navigation / Header -->
    <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Hermenegildo Lanz" class="h-10 w-auto object-contain">
                <div>
                    <h1 class="text-base font-bold text-slate-900 dark:text-white leading-tight">IES Hermenegildo Lanz</h1>
                    <p class="text-xs font-semibold text-[#0048FE] dark:text-blue-400">Portal de Empresas — Formación Dual</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0048FE] border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Enlace Seguro con Token</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Informative Notice -->
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800 border border-blue-100 dark:border-slate-700 flex items-start gap-3.5">
                <div class="p-2 rounded-xl bg-[#0048FE]/10 text-[#0048FE] dark:bg-blue-400/10 dark:text-blue-400 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Bienvenido al espacio de la oferta de prácticas</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                        Este enlace personalizado le permite consultar los datos de la oferta asignada a su empresa, revisar los alumnos candidatos y descargar sus currículums vitae (CV) en formato digital sin necesidad de disponer de usuario ni contraseña.
                    </p>
                </div>
            </div>

            <!-- Offer Details Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    Oferta {{ ucfirst($oferta->estado) }}
                                </span>
                                @if($oferta->empresa)
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        Empresa: <strong class="text-slate-900 dark:text-white">{{ $oferta->empresa->nombre }}</strong>
                                    </span>
                                @endif
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">
                                {{ $oferta->especialidad_requerida }}
                            </h2>
                            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-4xl">
                                {{ $oferta->descripcion ?: 'Sin descripción detallada de actividades.' }}
                            </p>

                            @if($oferta->grupos->count() > 0)
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Grupos formativos:</span>
                                @foreach($oferta->grupos as $grupo)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                                        {{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}
                                    </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="shrink-0 bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 text-center min-w-[160px]">
                            <span class="block text-3xl font-black text-[#0048FE] dark:text-blue-400">{{ $oferta->num_alumnos }}</span>
                            <span class="block text-xs font-bold text-slate-600 dark:text-slate-300 mt-1 uppercase tracking-wider">
                                {{ $oferta->num_alumnos == 1 ? 'Plaza Solicitada' : 'Plazas Solicitadas' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-700/60 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">CIF Empresa:</span> {{ $oferta->empresa?->cif ?: '—' }}
                    </div>
                    <div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Contacto Centro:</span> IES Hermenegildo Lanz
                    </div>
                    <div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Fecha de Registro:</span> {{ $oferta->created_at ? $oferta->created_at->format('d/m/Y') : '—' }}
                    </div>
                </div>
            </div>

            <!-- Candidates and CVs Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Alumnado Candidato y Asignado</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                            {{ $oferta->solicitudes->count() }}
                        </span>
                        @if($totalCvsDisponibles > 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                <svg class="w-3 h-3 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $totalCvsDisponibles }} {{ $totalCvsDisponibles === 1 ? 'CV disponible' : 'CVs disponibles' }}
                            </span>
                        @endif
                    </div>

                    <div>
                        @if($totalCvsDisponibles > 0)
                            <a href="{{ route('ofertas.compartida.descargar-cvs', $oferta->token) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar todos los CVs (ZIP)</span>
                            </a>
                        @else
                            <button type="button" disabled
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-400 bg-slate-100 dark:bg-slate-700/50 dark:text-slate-500 cursor-not-allowed"
                                    title="Ningún alumno candidato ha subido su currículum todavía">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar todos los CVs (ZIP)</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($oferta->solicitudes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidato y Datos de Contacto</th>
                                <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Currículum Vitae (CV)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                            @foreach($oferta->solicitudes as $solicitud)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                        <!-- Avatar -->
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-600 dark:text-slate-300 shrink-0 shadow-sm">
                                            @if($solicitud->alumno?->user?->avatar_url)
                                                <img src="{{ asset('storage/' . $solicitud->alumno->user->avatar_url) }}" alt="{{ $solicitud->alumno->user->name }}" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($solicitud->alumno?->user?->name ?? 'A', 0, 1)) }}
                                            @endif
                                        </div>

                                        <!-- Info / Ficha -->
                                        <div class="space-y-1.5 flex-1 min-w-0">
                                            <div>
                                                <span class="font-bold text-slate-900 dark:text-white block text-base leading-tight">
                                                    {{ $solicitud->alumno?->user?->name ?? '—' }}
                                                </span>
                                                @if($solicitud->alumno?->user?->email)
                                                    <a href="mailto:{{ $solicitud->alumno->user->email }}" class="text-xs text-slate-500 hover:text-[#0048FE] transition-colors inline-flex items-center gap-1 mt-0.5">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                        {{ $solicitud->alumno->user->email }}
                                                    </a>
                                                @endif
                                            </div>

                                            @php
                                                $telefono = $solicitud->alumno?->telefono ?? $solicitud->alumno?->user?->telefono;
                                                $fechaNacimiento = $solicitud->alumno?->fecha_nacimiento;
                                                $github = $solicitud->alumno?->user?->github_url;
                                                $linkedin = $solicitud->alumno?->linkedin_url ?? $solicitud->alumno?->user?->linkedin_url;
                                                $portfolio = $solicitud->alumno?->portfolio_url ?? $solicitud->alumno?->user?->portfolio_url;
                                            @endphp

                                            @if($telefono || $fechaNacimiento)
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-slate-400 pt-0.5">
                                                    @if($telefono)
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                            <a href="tel:{{ $telefono }}" class="hover:underline font-medium text-slate-700 dark:text-slate-300">{{ $telefono }}</a>
                                                        </span>
                                                    @endif

                                                    @if($fechaNacimiento)
                                                        <span class="inline-flex items-center gap-1.5" title="Fecha de nacimiento">
                                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            <span>{{ \Carbon\Carbon::parse($fechaNacimiento)->format('d/m/Y') }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($github || $linkedin || $portfolio)
                                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                                    @if($github)
                                                        <a href="{{ $github }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200 transition-colors"
                                                           title="Ver perfil de GitHub">
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                                                            <span>GitHub</span>
                                                        </a>
                                                    @endif

                                                    @if($linkedin)
                                                        <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 transition-colors"
                                                           title="Ver perfil de LinkedIn">
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                                            <span>LinkedIn</span>
                                                        </a>
                                                    @endif

                                                    @if($portfolio)
                                                        <a href="{{ $portfolio }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 transition-colors"
                                                           title="Ver Portfolio Web">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                                            <span>Portfolio</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $hasCv = $solicitud->alumno?->user?->cv_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($solicitud->alumno->user->cv_url);
                                    @endphp
                                    @if($hasCv)
                                        <div class="inline-flex items-center justify-center gap-2">
                                            <a href="{{ route('ofertas.compartida.descargar-cv', ['token' => $oferta->token, 'alumno' => $solicitud->alumno->id]) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 dark:text-rose-300 transition-colors"
                                               title="Descargar currículum de {{ $solicitud->alumno->user->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Descargar CV</span>
                                            </a>
                                            <a href="{{ asset('storage/' . $solicitud->alumno->user->cv_url) }}" target="_blank"
                                               class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                               title="Ver en pestaña nueva">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500 italic">
                                            <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Sin CV subido
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-16 text-slate-500 dark:text-slate-400">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <p class="text-base font-semibold text-slate-800 dark:text-slate-200">Actualmente no hay alumnos candidatos asignados a esta oferta.</p>
                    <p class="text-xs text-slate-400 mt-1">El equipo docente del centro gestionará las asignaciones próximamente.</p>
                </div>
                @endif
            </div>

            <!-- RGPD & Data Protection Compliance Note -->
            <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs text-slate-500 dark:text-slate-400 flex items-start gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>
                    <strong>Aviso de Confidencialidad y Protección de Datos (RGPD):</strong> La documentación y datos personales contenidos en este portal se facilitan exclusivamente para la selección de alumnos en el marco de la Formación Profesional DUAL y FCT del IES Hermenegildo Lanz. Queda prohibida su cesión o uso para fines distintos.
                </span>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-2">
                <span>© {{ date('Y') }} IES Hermenegildo Lanz — Granada</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('privacy') }}" class="hover:underline">Política de Privacidad</a>
                <a href="{{ route('cookies') }}" class="hover:underline">Cookies</a>
            </div>
        </div>
    </footer>

    <!-- Cookie Banner Component -->
    @include('components.cookie-banner')

</body>
</html>

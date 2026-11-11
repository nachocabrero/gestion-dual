<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">
            Panel Principal
        </h2>
    </x-slot>

    <div class="space-y-6">

        <!-- Welcome Banner Container -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0F172A] via-slate-900 to-[#0048FE] text-white p-6 sm:p-8 shadow-xl">
            <!-- Background Decorative Orbs -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#0048FE]/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-cyan-500/20 rounded-full blur-2xl translate-y-1/2 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-start sm:items-center gap-4 max-w-2xl">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ asset('storage/' . auth()->user()->avatar_url) }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover ring-4 ring-white/20 shadow-xl shrink-0">
                    @endif
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold backdrop-blur-md border border-white/10">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Curso Académico 2026/2027
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-extrabold font-display tracking-tight text-white">
                            ¡Hola de nuevo, {{ auth()->user()->name }}! 👋
                        </h1>
                        <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                            Bienvenido a la plataforma de Gestión Académica del IES Hermenegildo Lanz. Revisa tus accesos rápidos y notificaciones recientes a continuación.
                        </p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO) && auth()->user()->alumno)
                    <a href="{{ route('alumnos.show', auth()->user()->alumno) }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-semibold text-sm hover:bg-slate-100 transition-all shadow-md inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
                        <span>Mis Profesores</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_PROFESOR) && auth()->user()->profesor)
                    <a href="{{ route('profesores.show', auth()->user()->profesor) }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-semibold text-sm hover:bg-slate-100 transition-all shadow-md inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Mi Perfil Docente</span>
                    </a>
                    @endif
                    <a href="{{ route('portfolio') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm border border-white/20 backdrop-blur-md transition-all">
                        Ver Portfolio
                    </a>
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-xl {{ auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO) && auth()->user()->alumno ? 'bg-white/10 hover:bg-white/20 text-white font-medium border border-white/20 backdrop-blur-md' : 'bg-white text-slate-900 font-semibold shadow-md hover:bg-slate-100' }} text-sm transition-all">
                        Mi Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Role Quick Metrics Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Notificaciones No Leídas Card -->
            @php $notifUnread = \App\Models\Notificacion::contarNoLeidas(auth()->id()); @endphp
            <a href="{{ route('notificaciones.index') }}" class="hlanz-card hlanz-card-hover p-5 flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0048FE] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Notificaciones</p>
                    <p class="text-2xl font-bold font-display text-slate-900">{{ $notifUnread }} <span class="text-xs font-normal text-slate-500">sin leer</span></p>
                </div>
            </a>

            <!-- Ofertas de Prácticas Card -->
            <a href="{{ route('ofertas.index') }}" class="hlanz-card hlanz-card-hover p-5 flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ofertas Prácticas</p>
                    <p class="text-2xl font-bold font-display text-slate-900">FCT / Dual</p>
                </div>
            </a>

            <!-- Proyectos de Alumnos Card -->
            <a href="{{ route('proyectos.index') }}" class="hlanz-card hlanz-card-hover p-5 flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.458 14.96 14.96 0 01.06-.312m7.44 5.29a9 9 0 01-2.448-2.458"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Proyectos 2º</p>
                    <p class="text-2xl font-bold font-display text-slate-900">Repositorio</p>
                </div>
            </a>

            <!-- Portfolio Público Card -->
            <a href="{{ route('portfolio') }}" class="hlanz-card hlanz-card-hover p-5 flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Portfolio</p>
                    <p class="text-2xl font-bold font-display text-slate-900">Público</p>
                </div>
            </a>
        </div>

        <!-- Middle Grid Section: Quick Links & Recent Notifications -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Columns: Fast Access Modules -->
            <div class="lg:col-span-2 space-y-6">
                <div class="hlanz-card p-6">
                    <h3 class="text-lg font-bold font-display text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Accesos Directos y Operativa
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]))
                        <a href="{{ route('calificaciones.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-[#0048FE] hover:bg-blue-50/50 transition-all flex items-start gap-3 group">
                            <div class="p-2.5 bg-blue-100 text-[#0048FE] rounded-xl group-hover:bg-[#0048FE] group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Gestión de Calificaciones</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Consultar y evaluar actas por grupo.</p>
                            </div>
                        </a>
                        <a href="{{ route('anotaciones.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-[#0048FE] hover:bg-blue-50/50 transition-all flex items-start gap-3 group">
                            <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Anotaciones de Tutoría</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Seguimiento pedagógico y laboral.</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('ofertas.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-[#0048FE] hover:bg-blue-50/50 transition-all flex items-start gap-3 group">
                            <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Ofertas de FCT</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Postulaciones y solicitudes de empresas.</p>
                            </div>
                        </a>

                        <a href="{{ route('proyectos.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-[#0048FE] hover:bg-blue-50/50 transition-all flex items-start gap-3 group">
                            <div class="p-2.5 bg-purple-100 text-purple-700 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.458 14.96 14.96 0 01.06-.312m7.44 5.29a9 9 0 01-2.448-2.458"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Repositorio de Proyectos</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Subida de memorias y capturas.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Resumen de Prácticas por Grupo (Solo Profesorado y Coordinación Dual) -->
                @if(isset($isProfesor, $isAdminOrCoord) && ($isProfesor || $isAdminOrCoord))
                <div class="hlanz-card p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-blue-50 text-[#0048FE]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold font-display text-slate-900 leading-tight">
                                    Seguimiento de Prácticas por Grupo
                                </h3>
                                <p class="text-xs text-slate-500 font-medium">
                                    {{ $isAdminOrCoord ? 'Todos los grupos activos del centro' : 'Grupos asignados que impartes o tutorizas' }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('practicas.grupos') }}" class="text-xs font-semibold text-[#0048FE] hover:underline flex items-center gap-1 self-start sm:self-auto">
                            <span>Gestión de grupos</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    @if($gruposPracticasStats->isEmpty())
                    <div class="py-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p class="text-sm font-medium">No hay grupos asignados para mostrar en este curso.</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($gruposPracticasStats as $stat)
                        @php
                            $grupo = $stat['grupo'];
                            $total = $stat['total_alumnos'];
                            $conOferta = $stat['con_oferta'];
                            $conPractica = $stat['con_practica'];
                        @endphp
                        <div class="p-4 rounded-2xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-[#0048FE] hover:shadow-sm transition-all space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-sm text-slate-900 font-display">
                                            {{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}
                                        </h4>
                                        @if($stat['es_tutor'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Tutoría
                                        </span>
                                        @endif
                                        @if($grupo->linea?->ciclo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600">
                                            {{ $grupo->linea->ciclo->codigo }} • {{ ucfirst($grupo->linea->turno ?? '') }}
                                        </span>
                                        @endif
                                    </div>
                                    @if($grupo->tutor && !$stat['es_tutor'])
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Tutor/a: {{ $grupo->tutor->name }}
                                    </p>
                                    @endif
                                </div>
                                <a href="{{ route('practicas.grupos.show', $grupo) }}"
                                   class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-[#0048FE] hover:border-[#0048FE] transition-colors shrink-0 shadow-2xs">
                                    Ver grupo →
                                </a>
                            </div>

                            <!-- Métricas del grupo -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                                <!-- 1. Número de alumnos -->
                                <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block leading-tight">Alumnos</span>
                                        <span class="text-base font-extrabold text-slate-900 font-display leading-tight">{{ $total }}</span>
                                    </div>
                                </div>

                                <!-- 2. Con oferta de prácticas asignada -->
                                <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block leading-tight">Con oferta asignada</span>
                                        <span class="text-base font-extrabold text-indigo-700 font-display leading-tight">
                                            {{ $conOferta }}
                                            <span class="text-[11px] font-medium text-slate-400">/ {{ $total }}</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. Con prácticas creadas -->
                                <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block leading-tight">Con prácticas creadas</span>
                                        <span class="text-base font-extrabold text-emerald-700 font-display leading-tight">
                                            {{ $conPractica }}
                                            <span class="text-[11px] font-medium text-slate-400">/ {{ $total }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Right 1 Column: Recent Notifications Feed -->
            <div class="hlanz-card p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            Últimos Avisos
                        </h3>
                        <a href="{{ route('notificaciones.index') }}" class="text-xs font-semibold text-[#0048FE] hover:underline">Ver todas</a>
                    </div>

                    @php
                        $notificacionesRecientes = \App\Models\Notificacion::where('usuario_id', auth()->id())
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($notificacionesRecientes->isEmpty())
                    <div class="py-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-xs">No tienes avisos pendientes.</p>
                    </div>
                    @else
                        @foreach($notificacionesRecientes as $notif)
                        <div class="p-3 rounded-xl {{ $notif->es_leida ? 'bg-slate-50 border border-slate-100 opacity-75' : 'bg-emerald-50/40 border border-emerald-100 shadow-sm' }} flex items-start gap-3 transition-all">
                            <div class="w-2 h-2 rounded-full mt-2 shrink-0 {{ $notif->es_leida ? 'bg-slate-300' : 'bg-emerald-500 ring-2 ring-emerald-200 animate-pulse' }}"></div>
                            <div class="flex-1 min-w-0">
                                @if($notif->enlace)
                                    <a href="{{ route('notificaciones.abrir', $notif) }}" class="text-xs text-slate-800 font-semibold hover:text-[#0048FE] hover:underline leading-snug block">
                                        {{ $notif->mensaje }}
                                    </a>
                                @else
                                    <p class="text-xs text-slate-800 font-medium leading-snug">{{ $notif->mensaje }}</p>
                                @endif
                                <p class="text-[10px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Check para marcar como leído -->
                            <div class="shrink-0 pt-0.5">
                                @if(!$notif->es_leida)
                                <form action="{{ route('notificaciones.marcarLeida', $notif) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="group flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 bg-white hover:border-emerald-500 hover:bg-emerald-50 text-slate-400 hover:text-emerald-600 transition-all shadow-sm"
                                            title="Marcar aviso como leído">
                                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-emerald-600 bg-emerald-50/60" title="Aviso leído">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 text-center">IES Hermenegildo Lanz • Sistema Seguro</p>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>

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
                <div class="space-y-2 max-w-2xl">
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

                <!-- Action Button -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('portfolio') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm border border-white/20 backdrop-blur-md transition-all">
                        Ver Portfolio
                    </a>
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-semibold text-sm hover:bg-slate-100 transition-all shadow-md">
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.022.547l-1.08 1.08a2 2 0 00-.547 1.022l-.477 2.387a6 6 0 00.517 3.86l.158.318a6 6 0 003.86.517l2.387-.477a2 2 0 001.022-.547l1.08-1.08a2 2 0 00.547-1.022l.477-2.387a6 6 0 00-.517-3.86l-.158-.318a6 6 0 01-.517-3.86l.477-2.387a2 2 0 00.547-1.022l1.08-1.08z"/></svg>
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.022.547l-1.08 1.08a2 2 0 00-.547 1.022l-.477 2.387a6 6 0 00.517 3.86l.158.318a6 6 0 003.86.517l2.387-.477a2 2 0 001.022-.547l1.08-1.08a2 2 0 00.547-1.022l.477-2.387a6 6 0 00-.517-3.86l-.158-.318a6 6 0 01-.517-3.86l.477-2.387a2 2 0 00.547-1.022l1.08-1.08z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">Repositorio de Proyectos</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Subida de memorias y capturas.</p>
                            </div>
                        </a>
                    </div>
                </div>
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
                    <div class="space-y-3">
                        @foreach($notificacionesRecientes as $notif)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 {{ $notif->es_leida ? 'bg-slate-300' : 'bg-[#0048FE]' }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-slate-800 font-medium leading-snug">{{ $notif->mensaje }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 text-center">IES Hermenegildo Lanz • Sistema Seguro</p>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>

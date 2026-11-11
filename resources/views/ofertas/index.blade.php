<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Ofertas de Prácticas
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Convocatorias para FCT y formación en empresas colaboradoras</p>
            </div>
            @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
            <a href="{{ route('ofertas.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Crear Oferta</span>
            </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="stitch-toolbar">
            <form method="GET" action="{{ route('ofertas.index') }}" class="w-full flex flex-col xl:flex-row items-stretch xl:items-center gap-3">
                <div class="relative w-full xl:flex-1 min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título, empresa o descripción..." class="stitch-input !pl-10 w-full">
                </div>

                <div class="flex flex-wrap items-center justify-between sm:justify-start gap-2.5 w-full xl:w-auto xl:shrink-0">
                    <div class="w-full sm:w-56">
                        <select name="especialidad" class="stitch-input text-xs w-full">
                            <option value="">Especialidades (Todas)</option>
                            @foreach($especialidades as $esp)
                            <option value="{{ $esp }}" {{ request('especialidad') == $esp ? 'selected' : '' }}>{{ $esp }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($gruposFiltro) && $gruposFiltro->isNotEmpty())
                    <div class="w-full sm:w-56">
                        <select name="grupo_id" class="stitch-input text-xs w-full">
                            <option value="">{{ auth()->user()->hasRole(\App\Models\User::ROLE_PROFESOR) && !auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]) ? 'Mis grupos (Todos)' : 'Grupos (Todos)' }}</option>
                            @foreach($gruposFiltro as $g)
                            <option value="{{ $g->id }}" {{ request('grupo_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 whitespace-nowrap">
                            Filtrar
                        </button>
                        @if(request()->anyFilled(['search', 'especialidad', 'grupo_id']))
                        <a href="{{ route('ofertas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3 whitespace-nowrap">
                            Limpiar
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Offers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($ofertas as $oferta)
            <div class="stitch-card stitch-card-hover p-6 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <span class="stitch-badge-info">{{ $oferta->especialidad_requerida }}</span>
                        @if(in_array($oferta->estado, ['abierta', 'activa']))
                        <span class="stitch-badge-success">Abierta</span>
                        @elseif($oferta->estado === 'pendiente')
                        <span class="stitch-badge-warning">Pendiente</span>
                        @elseif($oferta->estado === 'solicitada')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Solicitada</span>
                        @elseif($oferta->estado === 'cerrada')
                        <span class="stitch-badge-neutral">Cerrada</span>
                        @else
                        <span class="stitch-badge-neutral">{{ ucfirst($oferta->estado) }}</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-slate-900 text-lg font-display leading-snug">
                        {{ $oferta->empresa->nombre ?? 'Empresa no especificada' }}
                    </h3>

                    <p class="text-xs text-slate-500 mt-1 line-clamp-3 leading-relaxed">
                        {{ $oferta->descripcion ?? 'Sin descripción detallada' }}
                    </p>

                    @if($oferta->grupos && $oferta->grupos->count() > 0)
                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-1">
                        @foreach($oferta->grupos as $grupo)
                        <span class="stitch-badge-neutral text-[10px]">{{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-semibold text-slate-500" title="Plazas ofertadas">
                            👥 {{ $oferta->num_alumnos }} {{ $oferta->num_alumnos == 1 ? 'plaza' : 'plazas' }}
                        </span>
                        <span class="text-slate-300 font-normal">·</span>
                        @php
                            $candidatosInscritos = $oferta->candidatos_inscritos_count ?? $oferta->candidatos_count ?? 0;
                        @endphp
                        @if($candidatosInscritos > 0)
                        <span class="inline-flex items-center gap-1 font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200/80 px-2 py-0.5 rounded-full" title="Candidatos inscritos en esta oferta">
                            <span>👤 {{ $candidatosInscritos }} {{ $candidatosInscritos == 1 ? 'candidato inscrito' : 'candidatos inscritos' }}</span>
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-full" title="Sin candidatos inscritos actualmente">
                            <span>👤 0 candidatos inscritos</span>
                        </span>
                        @endif
                    </div>
                    <a href="{{ route('ofertas.show', $oferta) }}" class="stitch-btn-primary text-xs py-1.5 px-3">
                        <span>Ver Detalle</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full stitch-card p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="text-sm font-medium">No hay ofertas de prácticas disponibles.</p>
            </div>
            @endforelse
        </div>

        @if(method_exists($ofertas, 'links'))
        <div class="pt-2">
            {{ $ofertas->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
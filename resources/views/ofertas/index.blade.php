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
            <form method="GET" action="{{ route('ofertas.index') }}" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título, empresa o descripción..." class="stitch-input pl-10">
                </div>

                <div class="w-full md:w-56">
                    <select name="especialidad" class="stitch-input text-xs">
                        <option value="">Especialidades (Todas)</option>
                        @foreach($especialidades as $esp)
                        <option value="{{ $esp }}" {{ request('especialidad') == $esp ? 'selected' : '' }}>{{ $esp }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'especialidad']))
                    <a href="{{ route('ofertas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
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
                        @if($oferta->estado === 'activa')
                        <span class="stitch-badge-success">Activa</span>
                        @elseif($oferta->estado === 'pendiente')
                        <span class="stitch-badge-warning">Pendiente</span>
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

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">
                        👥 {{ $oferta->num_alumnos }} {{ $oferta->num_alumnos == 1 ? 'plaza' : 'plazas' }}
                    </span>
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
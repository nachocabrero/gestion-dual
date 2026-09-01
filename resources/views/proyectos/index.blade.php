<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Módulo de Proyectos (2º)
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Repositorio institucional de memorias, despliegues y proyectos destacados</p>
            </div>
            @if(auth()->user()->hasRole('alumno'))
            <a href="{{ route('proyectos.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Proyecto</span>
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

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(auth()->user()->hasAnyRole(['admin', 'profesor']))
        <!-- Search & Filter Toolbar -->
        <div class="stitch-toolbar">
            <form method="GET" action="{{ route('proyectos.index') }}" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título o alumno..." class="stitch-input pl-10">
                </div>

                <div class="w-full md:w-56">
                    <select name="ciclo_id" class="stitch-input text-xs">
                        <option value="">Ciclos (Todos)</option>
                        @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}" {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'ciclo_id']))
                    <a href="{{ route('proyectos.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>
        @endif

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($proyectos as $proyecto)
            <div class="stitch-card stitch-card-hover flex flex-col justify-between overflow-hidden">
                @if($proyecto->imagenes && $proyecto->imagenes->count() > 0)
                <div class="h-44 w-full overflow-hidden bg-slate-100 relative">
                    <img src="{{ Storage::url($proyecto->imagenes->first()->url) }}" alt="{{ $proyecto->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @if($proyecto->es_destacado)
                    <span class="absolute top-3 right-3 stitch-badge-warning shadow-md backdrop-blur-md">
                        ⭐ Destacado
                    </span>
                    @endif
                </div>
                @endif

                <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="stitch-badge-neutral text-[10px]">{{ $proyecto->ciclo->codigo ?? 'Proyecto' }}</span>
                            @if(!$proyecto->imagenes->first() && $proyecto->es_destacado)
                            <span class="stitch-badge-warning">⭐ Destacado</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-slate-900 text-lg font-display leading-snug">
                            {{ $proyecto->titulo }}
                        </h3>

                        <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                            {{ Str::limit($proyecto->descripcion, 150) }}
                        </p>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            @if($proyecto->enlace_repositorio)
                            <a href="{{ $proyecto->enlace_repositorio }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1">
                                📦 Code Repo
                            </a>
                            @endif
                            @if($proyecto->enlace_despliegue)
                            <a href="{{ $proyecto->enlace_despliegue }}" target="_blank" class="text-emerald-600 hover:underline inline-flex items-center gap-1">
                                🚀 Demo Online
                            </a>
                            @endif
                        </div>

                        @if(auth()->user()->hasAnyRole(['admin', 'profesor']))
                        <div class="text-xs text-slate-500 pt-1">
                            <p><strong>Alumno:</strong> {{ $proyecto->alumno->user->name ?? '—' }}</p>
                            <p><strong>Ciclo:</strong> {{ $proyecto->ciclo->nombre ?? '—' }}</p>
                        </div>
                        @endif

                        <div class="flex items-center justify-between pt-2">
                            <a href="{{ route('proyectos.show', $proyecto) }}" class="stitch-btn-primary text-xs py-1.5 px-3">
                                <span>Ver Proyecto</span>
                            </a>
                            @if(auth()->user()->hasRole('alumno') && !$proyecto->estaCalificado())
                            <a href="{{ route('proyectos.edit', $proyecto) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">
                                Editar
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full stitch-card p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.022.547l-1.08 1.08a2 2 0 00-.547 1.022l-.477 2.387a6 6 0 00.517 3.86l.158.318a6 6 0 003.86.517l2.387-.477a2 2 0 001.022-.547l1.08-1.08a2 2 0 00.547-1.022l.477-2.387a6 6 0 00-.517-3.86l-.158-.318a6 6 0 01-.517-3.86l.477-2.387a2 2 0 00.547-1.022l1.08-1.08z"/></svg>
                <p class="text-sm font-medium">No se han registrado proyectos aún.</p>
                @if(auth()->user()->hasRole('alumno'))
                <p class="text-xs text-slate-400 mt-1">¡Sube tu proyecto integrador de 2º curso!</p>
                @endif
            </div>
            @endforelse
        </div>

        @if(method_exists($proyectos, 'links'))
        <div class="pt-2">
            {{ $proyectos->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
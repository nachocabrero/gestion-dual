<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold font-display text-slate-900 tracking-tight">Observaciones evaluación</h2>
                <p class="text-sm text-slate-500 mt-1">Registro de tutorías, incidencias y evolución</p>
            </div>
            @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
            <a href="{{ route('anotaciones.create') }}" class="stitch-btn-primary whitespace-nowrap">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Observación
            </a>
            @endif
        </div>
    </x-slot>

    <div class="stitch-card">
        <!-- Barra superior -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-4 justify-between bg-slate-50/50">
            <form method="GET" action="{{ route('anotaciones.index') }}" class="flex-1 w-full flex items-center gap-2 max-w-lg relative">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="alumno" value="{{ request('alumno') }}" placeholder="Buscar por alumno..." class="stitch-input pl-10 w-full bg-white shadow-sm">
                </div>
                <button type="submit" class="stitch-btn-secondary shrink-0">Buscar</button>
            </form>
        </div>

        <!-- Lista de Anotaciones -->
        <div class="divide-y divide-slate-100">
            @forelse($anotaciones as $a)
            <div class="p-5 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between group">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-1">
                        <h4 class="text-sm font-bold text-slate-900 truncate">{{ $a->titulo }}</h4>
                        @if($a->puesto)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                            Puesto: {{ $a->puesto }}
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 line-clamp-2 mb-2">{{ $a->contenido }}</p>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $a->alumno->user->name }} ({{ $a->alumno->grupos->pluck("nombre")->join(", ") ?? '—' }})
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $a->profesor?->user?->name ?? '—' }}
                        </span>
                        <span class="text-slate-400">•</span>
                        <span>{{ $a->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                    <a href="{{ route('anotaciones.show', $a->alumno_id) }}" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Ver alumno">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <a href="{{ route('anotaciones.edit', $a) }}" class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                    <form action="{{ route('anotaciones.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta observación?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-12 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">Sin observaciones</h3>
                <p class="text-sm text-slate-500 max-w-sm">No se han encontrado registros. Puedes crear una nueva observación para realizar el seguimiento.</p>
            </div>
            @endforelse
        </div>

        @if($anotaciones->hasPages())
        <div class="p-5 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
            {{ $anotaciones->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Gestión de Profesorado
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Equipo docente, tutores de grupo y coordinadores Dual</p>
            </div>
            @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <a href="{{ route('profesores.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Profesor</span>
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
            <form method="GET" action="{{ route('profesores.index') }}" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, email o especialidad..." class="stitch-input pl-10">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <select name="es_tutor" class="stitch-input text-xs">
                        <option value="">Filtrar Tutorías</option>
                        <option value="1" {{ request('es_tutor') == '1' ? 'selected' : '' }}>Es Tutor</option>
                        <option value="0" {{ request('es_tutor') == '0' ? 'selected' : '' }}>No es Tutor</option>
                    </select>
                    <select name="es_coordinador" class="stitch-input text-xs">
                        <option value="">Filtrar Coordinador</option>
                        <option value="1" {{ request('es_coordinador') == '1' ? 'selected' : '' }}>Coordinador Dual</option>
                        <option value="0" {{ request('es_coordinador') == '0' ? 'selected' : '' }}>No Coordinador</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'es_tutor', 'es_coordinador']))
                    <a href="{{ route('profesores.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Container for Desktop -->
        <div class="stitch-table-container hidden sm:block">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Email Institucional</th>
                        <th>Especialidad</th>
                        <th>Cargos / Roles</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profesores as $profesor)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($profesor->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">{{ $profesor->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $profesor->familia->nombre ?? 'Sin familia asignada' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-800 font-medium text-sm">{{ $profesor->user->email }}</span>
                        </td>
                        <td>
                            <span class="text-slate-700 text-sm">{{ $profesor->especialidad ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @if($profesor->es_tutor)
                                <span class="stitch-badge-warning">Tutor de Grupo</span>
                                @endif
                                @if($profesor->es_coordinador_dual)
                                <span class="stitch-badge-info">Coordinador Dual</span>
                                @endif
                                @if(!$profesor->es_tutor && !$profesor->es_coordinador_dual)
                                <span class="stitch-badge-neutral">Profesor</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($profesor->user->is_active)
                            <span class="stitch-badge-success">Activo</span>
                            @else
                            <span class="stitch-badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('profesores.show', $profesor) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0048FE] hover:bg-slate-100 transition-colors inline-flex" title="Ver Perfil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('profesores.edit', $profesor) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors inline-flex" title="Editar Profesor">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                            @if($profesor->user->is_active)
                            <form action="{{ route('profesores.deactivate', $profesor) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors inline-flex" title="Desactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('profesores.reactivate', $profesor) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 transition-colors inline-flex" title="Reactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('profesores.destroy', $profesor) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente este profesor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-700 hover:bg-slate-100 transition-colors inline-flex" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <p class="text-sm font-medium">No se encontraron profesores registrados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:hidden">
            @forelse($profesores as $profesor)
            <div class="stitch-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            {{ strtoupper(substr($profesor->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $profesor->user->name }}</h4>
                            <p class="text-xs text-slate-500">{{ $profesor->user->email }}</p>
                        </div>
                    </div>
                    @if($profesor->user->is_active)
                    <span class="stitch-badge-success">Activo</span>
                    @else
                    <span class="stitch-badge-danger">Inactivo</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100 space-y-1">
                    <p><strong>Especialidad:</strong> {{ $profesor->especialidad ?? '—' }}</p>
                    <div class="flex flex-wrap gap-1 pt-1">
                        @if($profesor->es_tutor)<span class="stitch-badge-warning text-[10px]">Tutor</span>@endif
                        @if($profesor->es_coordinador_dual)<span class="stitch-badge-info text-[10px]">Coordinador</span>@endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('profesores.show', $profesor) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Ver</a>
                    <a href="{{ route('profesores.edit', $profesor) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Editar</a>
                </div>
            </div>
            @empty
            <div class="stitch-card p-8 text-center text-slate-400">
                <p class="text-sm">No hay profesores.</p>
            </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $profesores->links() }}
        </div>
    </div>
</x-app-layout>
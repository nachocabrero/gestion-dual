<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Gestión del Alumnado
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Listado oficial de alumnos matriculados y expediente</p>
            </div>
            @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <a href="{{ route('alumnos.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Alumno</span>
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
            <form method="GET" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email..." class="stitch-input pl-10">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 flex-wrap">
                    <select name="familia" class="stitch-input text-xs">
                        <option value="">Familias (Todas)</option>
                        @foreach($familias as $f)
                        <option value="{{ $f->codigo }}" {{ request('familia') == $f->codigo ? 'selected' : '' }}>{{ $f->nombre }}</option>
                        @endforeach
                    </select>

                    <select name="ciclo" class="stitch-input text-xs">
                        <option value="">Ciclos (Todos)</option>
                        @foreach($ciclos as $c)
                        <option value="{{ $c->codigo }}" {{ request('ciclo') == $c->codigo ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>

                    <select name="linea" class="stitch-input text-xs">
                        <option value="">Turno (Todos)</option>
                        <option value="manana" {{ request('linea') == 'manana' ? 'selected' : '' }}>Mañana</option>
                        <option value="tarde" {{ request('linea') == 'tarde' ? 'selected' : '' }}>Tarde</option>
                    </select>

                    <select name="curso_academico_id" class="stitch-input text-xs">
                        <option value="">Curso académico</option>
                        @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_academico_id') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }}{{ $curso->is_active ? ' (actual)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        <span>Filtrar</span>
                    </button>
                    @if(request()->anyFilled(['search', 'familia', 'ciclo', 'linea', 'curso_academico_id']))
                    <a href="{{ route('alumnos.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
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
                        <th>Alumno</th>
                        <th>Email / Teléfono</th>
                        <th>Ciclos</th>
                        <th>Grupos</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($alumno->user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-sm truncate">{{ $alumno->user->name }}</p>
                                    @if($alumno->linkedin_url)
                                    <a href="{{ $alumno->linkedin_url }}" target="_blank" class="text-xs text-blue-600 hover:underline inline-flex items-center gap-1">
                                        <span>LinkedIn</span>
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-slate-800 text-sm font-medium">{{ $alumno->user->email }}</p>
                            <p class="text-xs text-slate-400">{{ $alumno->telefono ?? 'Sin teléfono' }}</p>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $ciclos = $alumno->grupos->map(fn($g) => $g->linea?->ciclo)->filter()->unique('id');
                                @endphp
                                @foreach($ciclos as $ciclo)
                                <span class="stitch-badge-info">{{ $ciclo->codigo }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($alumno->grupos->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($alumno->grupos as $grp)
                                <span class="stitch-badge-neutral">{{ $grp->nombre }}</span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-xs text-slate-400 italic">Sin grupo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($alumno->user->is_active)
                            <span class="stitch-badge-success">Activo</span>
                            @else
                            <span class="stitch-badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('alumnos.show', $alumno) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0048FE] hover:bg-slate-100 transition-colors inline-flex" title="Ver Expediente">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('alumnos.edit', $alumno) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors inline-flex" title="Editar Alumno">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                            @if($alumno->user->is_active)
                            <form action="{{ route('alumnos.deactivate', $alumno) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors inline-flex" title="Desactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('alumnos.reactivate', $alumno) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 transition-colors inline-flex" title="Reactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente este alumno?')">
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
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-sm font-medium">No se encontraron alumnos con los criterios seleccionados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Responsive Grid (sm:hidden) -->
        <div class="grid grid-cols-1 gap-4 sm:hidden">
            @forelse($alumnos as $alumno)
            <div class="stitch-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            {{ strtoupper(substr($alumno->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $alumno->user->name }}</h4>
                            <p class="text-xs text-slate-500">{{ $alumno->user->email }}</p>
                        </div>
                    </div>
                    @if($alumno->user->is_active)
                    <span class="stitch-badge-success">Activo</span>
                    @else
                    <span class="stitch-badge-danger">Inactivo</span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-1 pt-2 border-t border-slate-100">
                    @php
                        $ciclos = $alumno->grupos->map(fn($g) => $g->linea?->ciclo)->filter()->unique('id');
                    @endphp
                    @foreach($ciclos as $ciclo)
                    <span class="stitch-badge-info text-[10px]">{{ $ciclo->codigo }}</span>
                    @endforeach
                    @foreach($alumno->grupos as $grp)
                    <span class="stitch-badge-neutral text-[10px]">{{ $grp->nombre }}</span>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('alumnos.show', $alumno) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Ver</a>
                    <a href="{{ route('alumnos.edit', $alumno) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Editar</a>
                </div>
            </div>
            @empty
            <div class="stitch-card p-8 text-center text-slate-400">
                <p class="text-sm">No hay alumnos registrados.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $alumnos->links() }}
        </div>
    </div>
</x-app-layout>
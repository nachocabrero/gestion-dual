<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Gestión de Prácticas
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Seguimiento de FCT, tutorización y registro de horas acumuladas</p>
            </div>
            <a href="{{ route('practicas.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Práctica</span>
            </a>
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
            <form method="GET" action="{{ route('practicas.index') }}" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por alumno o empresa..." class="stitch-input pl-10">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <select name="estado" class="stitch-input text-xs">
                        <option value="">Estado (Todos)</option>
                        <option value="en_curso" {{ request('estado') == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                        <option value="finalizadas" {{ request('estado') == 'finalizadas' ? 'selected' : '' }}>Finalizadas</option>
                        <option value="pendientes" {{ request('estado') == 'pendientes' ? 'selected' : '' }}>Pendientes</option>
                    </select>

                    <select name="convenio" class="stitch-input text-xs">
                        <option value="">Convenio</option>
                        <option value="si" {{ request('convenio') == 'si' ? 'selected' : '' }}>Firmado</option>
                        <option value="no" {{ request('convenio') == 'no' ? 'selected' : '' }}>No firmado</option>
                    </select>

                    <select name="curso_academico_id" class="stitch-input text-xs">
                        <option value="">Curso</option>
                        @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_academico_id') == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'estado', 'convenio', 'curso_academico_id']))
                    <a href="{{ route('practicas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="stitch-table-container hidden sm:block">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Empresa</th>
                        <th>Curso Académico</th>
                        <th>Periodo</th>
                        <th class="text-center">Horas</th>
                        <th class="text-center">Convenio</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($practicas as $practica)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($practica->alumno->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900 text-sm">{{ $practica->alumno->user->name ?? 'Alumno eliminado' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-800 font-medium text-sm">{{ $practica->empresa->nombre ?? 'Empresa' }}</span>
                        </td>
                        <td>
                            <span class="stitch-badge-neutral">{{ $practica->cursoAcademico->nombre ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-slate-600 font-medium">
                                {{ $practica->fecha_inicio->format('d/m/Y') }} → {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : 'En curso' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="font-bold text-slate-900 text-sm">{{ $practica->horas_acumuladas }}h</span>
                        </td>
                        <td class="text-center">
                            @if($practica->convenio_firmado)
                            <span class="stitch-badge-success">Firmado</span>
                            @else
                            <span class="stitch-badge-danger">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($practica->estaEnCurso())
                            <span class="stitch-badge-info">En curso</span>
                            @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                            <span class="stitch-badge-neutral">Finalizada</span>
                            @else
                            <span class="stitch-badge-warning">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('practicas.show', $practica) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0048FE] hover:bg-slate-100 transition-colors inline-flex" title="Ver Práctica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('practicas.edit', $practica) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors inline-flex" title="Editar Práctica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('practicas.destroy', $practica) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente esta práctica?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-700 hover:bg-slate-100 transition-colors inline-flex" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">No hay registros de prácticas disponibles.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:hidden">
            @forelse($practicas as $practica)
            <div class="stitch-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 text-sm">{{ $practica->alumno->user->name ?? 'Alumno' }}</h4>
                    @if($practica->estaEnCurso())
                    <span class="stitch-badge-info">En curso</span>
                    @else
                    <span class="stitch-badge-neutral">Finalizada</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100 space-y-1">
                    <p><strong>Empresa:</strong> {{ $practica->empresa->nombre ?? 'Empresa' }}</p>
                    <p><strong>Horas:</strong> {{ $practica->horas_acumuladas }}h acumuladas</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('practicas.show', $practica) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Ver</a>
                    <a href="{{ route('practicas.edit', $practica) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Editar</a>
                </div>
            </div>
            @empty
            <div class="stitch-card p-8 text-center text-slate-400">
                <p class="text-sm">No hay prácticas.</p>
            </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $practicas->links() }}
        </div>
    </div>
</x-app-layout>
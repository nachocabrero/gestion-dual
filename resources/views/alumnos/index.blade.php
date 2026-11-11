<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Gestión del Alumnado
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Listado oficial de alumnos matriculados y expediente</p>
            </div>
            @can('create-alumno')
            <a href="{{ route('alumnos.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Alumno</span>
            </a>
            @endcan
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
        @php
            $hasAdvancedActive = request()->filled('familia') || (request()->has('curso_academico_id') && request('curso_academico_id') != ($cursoActual?->id ?? ''));
            $isFiltered = request()->filled('search') || request()->filled('grupo_id') || request()->filled('familia') || request()->filled('ciclo') || request()->filled('linea') || (request()->has('curso_academico_id') && request('curso_academico_id') != ($cursoActual?->id ?? ''));
        @endphp
        <div class="stitch-toolbar" x-data="{ showAdvanced: {{ $hasAdvancedActive ? 'true' : 'false' }} }">
            <form method="GET" action="{{ route('alumnos.index') }}" class="w-full space-y-3">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                    <!-- Campo de búsqueda amplio y prioritario -->
                    <div class="relative flex-1 min-w-[260px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email de alumno..." class="stitch-input !pl-10 w-full">
                    </div>

                    <!-- Filtros más frecuentes -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 shrink-0">
                        <select name="grupo_id" class="stitch-input text-xs w-full sm:w-44">
                            <option value="">Grupo (Todos)</option>
                            @foreach($grupos as $g)
                            <option value="{{ $g->id }}" {{ request('grupo_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nombre }}
                            </option>
                            @endforeach
                        </select>

                        <select name="ciclo" class="stitch-input text-xs w-full sm:w-36">
                            <option value="">Ciclos (Todos)</option>
                            @foreach($ciclos as $c)
                            <option value="{{ $c->codigo }}" {{ request('ciclo') == $c->codigo ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>

                        <select name="linea" class="stitch-input text-xs w-full sm:w-32">
                            <option value="">Turno (Todos)</option>
                            <option value="manana" {{ request('linea') == 'manana' ? 'selected' : '' }}>Mañana</option>
                            <option value="tarde" {{ request('linea') == 'tarde' ? 'selected' : '' }}>Tarde</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="stitch-btn-primary text-xs py-2 px-4">
                            <span>Filtrar</span>
                        </button>

                        <button type="button" @click="showAdvanced = !showAdvanced"
                            class="stitch-btn-secondary text-xs py-2 px-3 inline-flex items-center gap-1.5 transition-colors"
                            :class="{ '!border-[#0048FE] !text-[#0048FE] !bg-blue-50/70 font-semibold': showAdvanced }"
                            title="Ampliar opciones de filtro">
                            <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ '!text-[#0048FE] rotate-180': showAdvanced }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <span>Ampliar opciones de filtro</span>
                            @if($hasAdvancedActive)
                            <span class="w-2 h-2 rounded-full bg-[#0048FE]" title="Filtro avanzado activo"></span>
                            @endif
                        </button>

                        @if($isFiltered)
                        <a href="{{ route('alumnos.index') }}" class="stitch-btn-secondary text-xs py-2 px-3" title="Restablecer filtros al curso actual">
                            Limpiar
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Panel Desplegable: Curso académico y Familias (oculto normalmente) -->
                <div x-show="showAdvanced" x-cloak class="pt-3 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1 uppercase tracking-wider">Curso Académico</label>
                        <select name="curso_academico_id" class="stitch-input text-xs w-full bg-white">
                            <option value="">Curso académico (Todos)</option>
                            @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ (string)($selectedCursoId ?? '') === (string)$curso->id ? 'selected' : '' }}>
                                {{ $curso->nombre }}{{ $curso->is_active ? ' (actual)' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1 uppercase tracking-wider">Familia Profesional</label>
                        <select name="familia" class="stitch-input text-xs w-full bg-white">
                            <option value="">Familias (Todas)</option>
                            @foreach($familias as $f)
                            <option value="{{ $f->codigo }}" {{ request('familia') == $f->codigo ? 'selected' : '' }}>{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-end">
                        <p class="text-xs text-slate-500 italic pb-1">
                            ℹ️ Filtro predeterminado activo en el <strong>curso actual ({{ $cursoActual?->nombre ?? 'en vigor' }})</strong>.
                        </p>
                    </div>
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
                        <th>Ciclos y Grupos</th>
                        <th>Prácticas 2025-2026</th>
                        <th>Prácticas 2026-2027</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($alumno->user->avatar_url)
                                    <img src="{{ asset('storage/' . $alumno->user->avatar_url) }}" alt="{{ $alumno->user->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                        {{ strtoupper(substr($alumno->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-sm truncate">{{ $alumno->user->name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($alumno->linkedin_url ?? $alumno->user->linkedin_url)
                                        <a href="{{ $alumno->linkedin_url ?? $alumno->user->linkedin_url }}" target="_blank" class="text-xs text-blue-600 hover:underline inline-flex items-center gap-1">
                                            <span>LinkedIn</span>
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                        </a>
                                        @endif
                                        @php $pUrl = trim($alumno->portfolio_url ?? $alumno->user->portfolio_url ?? ''); @endphp
                                        @if($pUrl && $pUrl !== 'https://' && $pUrl !== 'http://')
                                        <a href="{{ $pUrl }}" target="_blank" class="text-xs text-emerald-600 hover:underline inline-flex items-center gap-1 font-bold">
                                            <span>🌐 Portfolio</span>
                                        </a>
                                        @endif
                                        @if($alumno->user->cv_url)
                                        <a href="{{ route('alumnos.cv', $alumno) }}" data-cv="{{ $alumno->user->cv_url }}" download="{{ $alumno->cv_download_filename }}" class="text-xs text-rose-600 hover:underline inline-flex items-center gap-1 font-bold" title="Descargar Currículum ({{ $alumno->user->name }})">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>CV</span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-slate-800 text-sm font-medium">{{ $alumno->user->email }}</p>
                            <p class="text-xs text-slate-400">{{ $alumno->telefono ?? 'Sin teléfono' }}</p>
                        </td>
                        <td>
                            <div class="space-y-1">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $ciclos = $alumno->grupos->map(fn($g) => $g->linea?->ciclo)->filter()->unique('id');
                                    @endphp
                                    @forelse($ciclos as $ciclo)
                                        <span class="stitch-badge-info text-xs">{{ $ciclo->codigo }}</span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Sin ciclo</span>
                                    @endforelse
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($alumno->grupos as $grp)
                                        <span class="stitch-badge-neutral text-xs">{{ $grp->nombre }}</span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Sin grupo</span>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td>
                            @if(!$alumno->soloEnGruposPrimero())
                                @if($alumno->empresa_practicas_primero)
                                    <p class="text-slate-800 text-sm font-semibold">{{ $alumno->empresa_practicas_primero }}</p>
                                    @if($alumno->horas_practicas_primero)
                                        <p class="text-xs text-slate-500 font-medium">{{ $alumno->horas_practicas_primero }} horas</p>
                                    @endif
                                @elseif($alumno->sin_practicas_primero)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="1er curso convalidado o sin prácticas anteriores (0h)">
                                        Sin prácticas 1º (0h)
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sin empresa</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($alumno->repitiendo_modulos_primero)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-900 border border-orange-300 dark:bg-orange-950/60 dark:text-orange-200 dark:border-orange-800" title="Repitiendo módulos de 1º (según normativa no realiza prácticas este año)">
                                    No hace prácticas (Repite 1º)
                                </span>
                            @elseif($alumno->practica_confirmada && $alumno->practica_confirmada->empresa)
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-200 dark:border-emerald-800" title="Práctica formal creada y confirmada">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $alumno->practica_confirmada->empresa->nombre }}</span>
                                    </span>
                                    <p class="text-[11px] text-emerald-700 font-semibold pl-1">Confirmada</p>
                                </div>
                            @elseif($alumno->propuesta_practica && $alumno->propuesta_practica->oferta?->empresa)
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-200 dark:border-amber-800" title="Candidato asignado a oferta pero sin práctica formal confirmada aún">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $alumno->propuesta_practica->oferta->empresa->nombre }}</span>
                                    </span>
                                    <p class="text-[11px] text-amber-700 font-semibold pl-1">Propuesta (sin confirmar)</p>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Sin asignar</span>
                            @endif
                        </td>
                        <td class="text-right space-x-1">
                            @if($alumno->user->cv_url)
                            <a href="{{ route('alumnos.cv', $alumno) }}" data-cv="{{ $alumno->user->cv_url }}" download="{{ $alumno->cv_download_filename }}" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors inline-flex" title="Descargar CV ({{ $alumno->user->name }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            @endif
                            <a href="{{ route('alumnos.show', $alumno) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0048FE] hover:bg-slate-100 transition-colors inline-flex" title="Ver Perfil y Expediente">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @can('update-alumno', $alumno)
                            <a href="{{ route('alumnos.edit', $alumno) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors inline-flex" title="Editar Alumno">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan
                            @can('deactivate-alumno')
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
                            @endcan
                            @can('delete-alumno')
                            <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente este alumno?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-700 hover:bg-slate-100 transition-colors inline-flex" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endcan
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
                </div>

                <div class="pt-2 border-t border-slate-100 space-y-1">
                    <div class="flex flex-wrap gap-1">
                        @php
                            $ciclos = $alumno->grupos->map(fn($g) => $g->linea?->ciclo)->filter()->unique('id');
                        @endphp
                        @forelse($ciclos as $ciclo)
                            <span class="stitch-badge-info text-[10px]">{{ $ciclo->codigo }}</span>
                        @empty
                            <span class="text-[10px] text-slate-400 italic">Sin ciclo</span>
                        @endforelse
                    </div>
                    <div class="flex flex-wrap gap-1">
                        @forelse($alumno->grupos as $grp)
                            <span class="stitch-badge-neutral text-[10px]">{{ $grp->nombre }}</span>
                        @empty
                            <span class="text-[10px] text-slate-400 italic">Sin grupo</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Prácticas 2025-2026:</span>
                        <span class="font-semibold text-slate-800">
                            @if(!$alumno->soloEnGruposPrimero())
                                @if($alumno->empresa_practicas_primero)
                                    {{ $alumno->empresa_practicas_primero }}
                                    @if($alumno->horas_practicas_primero) ({{ $alumno->horas_practicas_primero }}h) @endif
                                @elseif($alumno->sin_practicas_primero)
                                    <span class="text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">Sin prácticas (0h)</span>
                                @else
                                    <span class="text-slate-400 italic">Sin empresa</span>
                                @endif
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Prácticas 2026-2027:</span>
                        @if($alumno->repitiendo_modulos_primero)
                            <span class="bg-orange-100 text-orange-900 border border-orange-300 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                No hace prácticas
                            </span>
                        @elseif($alumno->practica_confirmada && $alumno->practica_confirmada->empresa)
                            <span class="bg-emerald-100 text-emerald-900 border border-emerald-300 px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $alumno->practica_confirmada->empresa->nombre }}</span>
                            </span>
                        @elseif($alumno->propuesta_practica && $alumno->propuesta_practica->oferta?->empresa)
                            <span class="bg-amber-100 text-amber-900 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $alumno->propuesta_practica->oferta->empresa->nombre }} (Propuesta)</span>
                            </span>
                        @else
                            <span class="text-slate-400 italic text-[11px]">Sin asignar</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    @if($alumno->user->cv_url)
                    <a href="{{ route('alumnos.cv', $alumno) }}" data-cv="{{ $alumno->user->cv_url }}" download="{{ $alumno->cv_download_filename }}" class="stitch-btn-secondary !text-rose-600 !border-rose-200 hover:!bg-rose-50 text-xs py-1.5 px-3 inline-flex items-center gap-1 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Descargar CV</span>
                    </a>
                    @endif
                    <a href="{{ route('alumnos.show', $alumno) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Ver</a>
                    @can('update-alumno', $alumno)
                    <a href="{{ route('alumnos.edit', $alumno) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Editar</a>
                    @endcan
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
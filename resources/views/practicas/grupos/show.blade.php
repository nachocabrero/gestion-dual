<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold font-display text-slate-900 tracking-tight">Prácticas por Grupo</h2>
                <p class="text-sm text-slate-500 mt-1">Gestión de asignaciones para {{ $grupo->nombre ?? 'Grupo ' . $grupo->numero }}</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Selector de grupo -->
        <div class="stitch-card p-6 md:p-8">
            <label for="grupo" class="block font-medium text-sm text-slate-700 mb-2">Seleccionar grupo:</label>
            <select id="grupo" class="stitch-input max-w-md" onchange="if(this.value) window.location.href='/practicas/grupos/'+this.value">
                @php
                    $grupos = $grupos ?? \App\Models\Grupo::where('curso_academico_id', \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first()?->id)
                        ->with('linea')
                        ->orderBy('nombre')
                        ->get();
                @endphp
                @foreach($grupos as $g)
                <option value="{{ $g->id }}" {{ $g->id == $grupo->id ? 'selected' : '' }}>
                    {{ $g->nombre ?? ('Grupo ' . $g->numero) }} — {{ $g->linea?->nombre ?? '—' }}@if($g->tutor_id === auth()->id()) (Tutoría)@endif
                </option>
                @endforeach
            </select>
        </div>

        <!-- SECCIÓN 1: Alumnos sin práctica -->
        <div class="stitch-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Alumnos sin práctica asignada</h3>
                        <p class="text-xs text-slate-500 font-medium">{{ count($alumnosSinPractica) }} pendientes de asignación</p>
                    </div>
                </div>

                @if($tieneEvaluaciones ?? false)
                <!-- Controles de ordenación si el grupo tiene observaciones de evaluación -->
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ordenar por:</span>
                    <div class="inline-flex rounded-xl p-1 bg-white border border-slate-200 shadow-2xs">
                        <a href="{{ route('practicas.grupos.show', ['grupo' => $grupo->id, 'orden' => 'nombre']) }}"
                           class="px-3 py-1 text-xs rounded-lg font-semibold transition-all {{ ($orden ?? '') === 'nombre' ? 'bg-[#0048FE] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            Nombre (A-Z)
                        </a>
                        <a href="{{ route('practicas.grupos.show', ['grupo' => $grupo->id, 'orden' => 'puesto']) }}"
                           class="px-3 py-1 text-xs rounded-lg font-semibold transition-all flex items-center gap-1 {{ in_array($orden ?? 'puesto', ['puesto', 'evaluacion']) ? 'bg-[#0048FE] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <span>Puesto en clase</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>

            @if(count($alumnosSinPractica) === 0)
                <div class="p-6 text-center text-sm font-medium text-emerald-600 bg-emerald-50/30">
                    ✅ Todos los alumnos de este grupo tienen práctica asignada.
                </div>
            @else
            <!-- Desktop Table (con scroll horizontal protegido) -->
            <div class="stitch-table-container hidden md:block">
                <table class="stitch-table min-w-[700px]">
                    <thead>
                        <tr>
                            <th class="min-w-[260px]">Alumno</th>
                            @if($tieneEvaluaciones ?? false)
                            <th class="text-center min-w-[190px]">Puesto en clase</th>
                            @endif
                            <th class="min-w-[220px]">Candidaturas en ofertas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnosSinPractica as $item)
                        <tr class="{{ $item['tiene_oferta_aceptada'] ? 'bg-amber-50/40' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($item['alumno']->user->avatar_url)
                                        <img src="{{ asset('storage/' . $item['alumno']->user->avatar_url) }}" alt="{{ $item['alumno']->user->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                            {{ strtoupper(substr($item['alumno']->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('alumnos.show', $item['alumno']) }}" class="font-bold text-slate-900 text-sm hover:text-[#0048FE] hover:underline">
                                                {{ $item['alumno']->user->name }}
                                            </a>
                                            @if($item['tiene_oferta_aceptada'])
                                            <span class="stitch-badge-warning inline-flex text-[11px] px-2 py-0.5" title="Alumno preseleccionado como candidato en una oferta">⚡ Candidato en oferta</span>
                                            @endif
                                            @if($item['alumno']->repitiendo_modulos_primero)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Repitiendo módulos de 1º (no realiza prácticas según normativa)">
                                                Repite 1º (Sin prácticas)
                                            </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="font-semibold text-slate-400">1º:</span>
                                            @if($item['alumno']->empresa_practicas_primero)
                                                <strong class="text-slate-700 font-semibold">{{ $item['alumno']->empresa_practicas_primero }}</strong>
                                                @if($item['alumno']->horas_practicas_primero)
                                                    <span class="text-slate-500 font-medium">({{ $item['alumno']->horas_practicas_primero }}h)</span>
                                                @endif
                                            @elseif($item['alumno']->sin_practicas_primero)
                                                <span class="text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-medium" title="Convalidación de 1º o sin prácticas previas">Sin prácticas 1º (0h)</span>
                                            @else
                                                <span class="text-slate-400 italic">Sin prácticas en 1º</span>
                                            @endif

                                            @if(!empty($item['total_observaciones']) && $item['total_observaciones'] > 0)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-semibold border border-blue-200" title="{{ $item['total_observaciones'] }} observación(es) docente(s)">
                                                💬 {{ $item['total_observaciones'] }} obs.
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if($tieneEvaluaciones ?? false)
                            <td class="text-center align-middle">
                                @php
                                    $puestoItem = $item['puesto'] ?? $item['posicion'] ?? null;
                                @endphp
                                @if(!empty($puestoItem))
                                    <div class="flex flex-col items-center gap-1">
                                        @if($puestoItem == 1)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs" title="1º Mejor alumno según evaluación del profesorado">
                                                🥇 Puesto 1 en clase (Mejor alumno)
                                            </span>
                                        @elseif($puestoItem == 2)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-300 shadow-2xs" title="2º Mejor alumno según evaluación del profesorado">
                                                🥈 Puesto 2 en clase (2º mejor)
                                            </span>
                                        @elseif($puestoItem == 3)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs" title="3º Mejor alumno según evaluación del profesorado">
                                                🥉 Puesto 3 en clase
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs" title="Puesto {{ $puestoItem }} en clase">
                                                Puesto {{ $puestoItem }} en clase
                                            </span>
                                        @endif
                                        @if(($item['total_observaciones'] ?? 0) > 1)
                                            <span class="text-[11px] text-slate-500 font-medium">media {{ $item['media_puesto'] }} ({{ $item['total_observaciones'] }} obs.)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">
                                        {{ ($item['total_observaciones'] ?? 0) > 0 ? 'Sin puesto en clase' : 'Sin evaluar' }}
                                    </span>
                                @endif
                            </td>
                            @endif
                            <td>
                                @if($item['ofertas_aceptadas']->isNotEmpty())
                                    <div class="flex flex-col gap-1.5">
                                    @foreach($item['ofertas_aceptadas'] as $sol)
                                        <div class="text-sm flex items-center justify-between gap-2">
                                            <div class="min-w-0 truncate">
                                                <a href="{{ route('ofertas.show', $sol->oferta) }}" class="font-semibold text-slate-800 hover:text-[#0048FE] hover:underline">
                                                    {{ $sol->oferta->empresa->nombre }}
                                                </a>
                                                <span class="text-slate-500 text-xs">({{ $sol->oferta->especialidad_requerida }})</span>
                                            </div>
                                            @if(in_array($sol->oferta->estado, ['pendiente', 'solicitada']))
                                                <form method="POST" action="{{ route('ofertas.solicitudes.desasignar', $sol) }}" class="inline shrink-0" onsubmit="return confirm('¿Desasignar a este alumno de la oferta?')">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 hover:underline font-semibold" title="Desasignar alumno de la oferta">
                                                        Desasignar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (md:hidden) -->
            <div class="p-4 space-y-3 md:hidden">
                @foreach($alumnosSinPractica as $item)
                @php
                    $puestoItem = $item['puesto'] ?? $item['posicion'] ?? null;
                @endphp
                <div class="stitch-card p-4 space-y-3 border border-slate-200 {{ $item['tiene_oferta_aceptada'] ? 'bg-amber-50/30 border-amber-200' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($item['alumno']->user->avatar_url)
                                <img src="{{ asset('storage/' . $item['alumno']->user->avatar_url) }}" alt="{{ $item['alumno']->user->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                    {{ strtoupper(substr($item['alumno']->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('alumnos.show', $item['alumno']) }}" class="font-bold text-slate-900 text-sm hover:text-[#0048FE] hover:underline block truncate">
                                    {{ $item['alumno']->user->name }}
                                </a>
                                <p class="text-xs text-slate-400 truncate">{{ $item['alumno']->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        @if($item['tiene_oferta_aceptada'])
                            <span class="stitch-badge-warning inline-flex text-[11px] px-2 py-0.5">⚡ Candidato en oferta</span>
                        @endif
                        @if($item['alumno']->repitiendo_modulos_primero)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Repite 1º (Sin prácticas)
                            </span>
                        @endif
                        @if($tieneEvaluaciones ?? false)
                            @if(!empty($puestoItem))
                                @if($puestoItem == 1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        🥇 Puesto 1 en clase (Mejor alumno)
                                    </span>
                                @elseif($puestoItem == 2)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-300">
                                        🥈 Puesto 2 en clase (2º mejor)
                                    </span>
                                @elseif($puestoItem == 3)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        🥉 Puesto 3 en clase
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        Puesto {{ $puestoItem }} en clase
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs italic">
                                    {{ ($item['total_observaciones'] ?? 0) > 0 ? 'Sin puesto en clase' : 'Sin evaluar' }}
                                </span>
                            @endif
                        @endif
                    </div>

                    <div class="pt-2 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-400">1º Curso:</span>
                            <span class="text-slate-700 font-semibold">
                                @if($item['alumno']->empresa_practicas_primero)
                                    {{ $item['alumno']->empresa_practicas_primero }} ({{ $item['alumno']->horas_practicas_primero ?? 0 }}h)
                                @elseif($item['alumno']->sin_practicas_primero)
                                    Sin prácticas (0h)
                                @else
                                    Sin prácticas en 1º
                                @endif
                            </span>
                        </div>
                        @if($item['ofertas_aceptadas']->isNotEmpty())
                        <div class="pt-1.5 border-t border-slate-100">
                            <span class="font-medium text-slate-400 block mb-1">Candidaturas en ofertas:</span>
                            <div class="space-y-1.5">
                                @foreach($item['ofertas_aceptadas'] as $sol)
                                <div class="flex items-center justify-between gap-2 bg-slate-50 p-2 rounded-lg">
                                    <div class="min-w-0 truncate">
                                        <a href="{{ route('ofertas.show', $sol->oferta) }}" class="font-semibold text-slate-800 hover:text-[#0048FE]">
                                            {{ $sol->oferta->empresa->nombre }}
                                        </a>
                                        <span class="text-slate-500 text-xs">({{ $sol->oferta->especialidad_requerida }})</span>
                                    </div>
                                    @if(in_array($sol->oferta->estado, ['pendiente', 'solicitada']))
                                    <form method="POST" action="{{ route('ofertas.solicitudes.desasignar', $sol) }}" class="inline shrink-0" onsubmit="return confirm('¿Desasignar a este alumno de la oferta?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold px-2 py-1 rounded bg-rose-50 border border-rose-200">
                                            Desasignar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- SECCIÓN 2: Empresas con plazas libres -->
        <div class="stitch-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#0048FE] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Plazas disponibles</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ $ofertasConPlazas['total_libres'] }} plazas libres en total para este grupo</p>
                </div>
            </div>

            @if(count($ofertasConPlazas['ofertas']) === 0)
                <div class="p-6 text-center text-sm font-medium text-slate-500 bg-slate-50/30">
                    No hay ofertas con plazas libres para este grupo.
                </div>
            @else
            <!-- Desktop Table (con scroll horizontal protegido) -->
            <div class="stitch-table-container hidden md:block">
                <table class="stitch-table min-w-[720px]">
                    <thead>
                        <tr>
                            <th class="min-w-[180px]">Empresa</th>
                            <th class="min-w-[150px]">Especialidad</th>
                            <th class="text-center min-w-[110px]">Plazas libres</th>
                            <th class="text-center min-w-[140px]">Detalle asignación</th>
                            <th class="text-right min-w-[140px] whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ofertasConPlazas['ofertas'] as $of)
                        <tr>
                            <td>
                                <a href="{{ route('ofertas.show', $of['oferta']) }}" class="font-bold text-slate-900 hover:text-[#0048FE] hover:underline inline-flex items-center gap-1.5" title="Ver detalle de la oferta">
                                    <span>{{ $of['oferta']->empresa->nombre }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="text-slate-600 text-sm">
                                <a href="{{ route('ofertas.show', $of['oferta']) }}" class="hover:text-slate-900">
                                    {{ $of['oferta']->especialidad_requerida }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="stitch-badge-success text-sm font-bold px-2.5 py-1">{{ $of['plazas_libres'] }}</span>
                            </td>
                            <td class="text-center text-xs font-medium text-slate-500">
                                {{ $of['plazas_asignadas'] }} / {{ $of['total_plazas'] }} asignadas
                            </td>
                            <td class="text-right space-x-1.5 whitespace-nowrap shrink-0">
                                <a href="{{ route('ofertas.enviar-form', ['oferta' => $of['oferta'], 'grupo_id' => $grupo->id]) }}" class="stitch-btn-primary text-xs py-1.5 px-3 inline-flex items-center gap-1.5 font-semibold shrink-0" title="Asignar alumnos de este grupo a la oferta">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    <span>Asignar alumnos</span>
                                </a>
                                <a href="{{ route('ofertas.show', $of['oferta']) }}" class="stitch-btn-secondary text-xs py-1.5 px-2.5 shrink-0" title="Ver detalle completo de la oferta">
                                    Ver oferta
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (md:hidden) -->
            <div class="p-4 space-y-3 md:hidden">
                @foreach($ofertasConPlazas['ofertas'] as $of)
                <div class="stitch-card p-4 space-y-3 border border-slate-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm font-display">
                                {{ $of['oferta']->empresa->nombre }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $of['oferta']->especialidad_requerida }}</p>
                        </div>
                        <span class="stitch-badge-success text-xs font-bold px-2 py-0.5 shrink-0">
                            {{ $of['plazas_libres'] }} {{ $of['plazas_libres'] == 1 ? 'plaza libre' : 'plazas libres' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Asignación:</span>
                        <span class="font-semibold text-slate-700">{{ $of['plazas_asignadas'] }} / {{ $of['total_plazas'] }} asignadas</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                        <a href="{{ route('ofertas.enviar-form', ['oferta' => $of['oferta'], 'grupo_id' => $grupo->id]) }}" class="stitch-btn-primary text-xs py-2 px-3 flex-1 justify-center items-center gap-1.5 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span>Asignar alumnos</span>
                        </a>
                        <a href="{{ route('ofertas.show', $of['oferta']) }}" class="stitch-btn-secondary text-xs py-2 px-3">
                            Ver oferta
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- SECCIÓN 3: Alumnos con práctica asignada -->
        <div class="stitch-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Alumnos con práctica asignada</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ count($alumnosConPractica) }} asignados</p>
                </div>
            </div>

            @if(count($alumnosConPractica) === 0)
                <div class="p-6 text-center text-sm font-medium text-slate-500 bg-slate-50/30">
                    Ningún alumno tiene práctica asignada aún.
                </div>
            @else
            <!-- Desktop Table (con scroll horizontal protegido) -->
            <div class="stitch-table-container hidden md:block">
                <table class="stitch-table min-w-[700px]">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Alumno</th>
                            @if($tieneEvaluaciones ?? false)
                            <th class="text-center min-w-[190px]">Puesto en clase</th>
                            @endif
                            <th class="min-w-[160px]">Empresa</th>
                            <th class="min-w-[140px]">Especialidad</th>
                            <th class="text-center min-w-[120px]">Estado Convenio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnosConPractica as $item)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($item['alumno']->user->avatar_url)
                                        <img src="{{ asset('storage/' . $item['alumno']->user->avatar_url) }}" alt="{{ $item['alumno']->user->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                            {{ strtoupper(substr($item['alumno']->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('alumnos.show', $item['alumno']) }}" class="font-bold text-slate-900 text-sm hover:text-[#0048FE] hover:underline block truncate">
                                            {{ $item['alumno']->user->name }}
                                        </a>
                                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="font-semibold text-slate-400">1º:</span>
                                            @if($item['alumno']->empresa_practicas_primero)
                                                <strong class="text-slate-700 font-semibold">{{ $item['alumno']->empresa_practicas_primero }}</strong>
                                                @if($item['alumno']->horas_practicas_primero)
                                                    <span class="text-slate-500 font-medium">({{ $item['alumno']->horas_practicas_primero }}h)</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic">Sin prácticas en 1º</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if($tieneEvaluaciones ?? false)
                            <td class="text-center align-middle">
                                @php
                                    $puestoItem = $item['puesto'] ?? null;
                                @endphp
                                @if(!empty($puestoItem))
                                    <div class="flex flex-col items-center gap-1">
                                        @if($puestoItem == 1)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs" title="1º Mejor alumno según evaluación del profesorado">
                                                🥇 Puesto 1 en clase (Mejor alumno)
                                            </span>
                                        @elseif($puestoItem == 2)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-300 shadow-2xs" title="2º Mejor alumno según evaluación del profesorado">
                                                🥈 Puesto 2 en clase (2º mejor)
                                            </span>
                                        @elseif($puestoItem == 3)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs" title="3º Mejor alumno según evaluación del profesorado">
                                                🥉 Puesto 3 en clase
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs" title="Puesto {{ $puestoItem }} en clase">
                                                Puesto {{ $puestoItem }} en clase
                                            </span>
                                        @endif
                                        @if(($item['total_observaciones'] ?? 0) > 1)
                                            <span class="text-[11px] text-slate-500 font-medium">media {{ $item['media_puesto'] }} ({{ $item['total_observaciones'] }} obs.)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">
                                        {{ ($item['total_observaciones'] ?? 0) > 0 ? 'Sin puesto en clase' : 'Sin evaluar' }}
                                    </span>
                                @endif
                            </td>
                            @endif
                            <td class="text-slate-700 font-medium">
                                {{ $item['empresa']->nombre }}
                            </td>
                            <td class="text-slate-500">
                                {{ $item['practica']->oferta->especialidad_requerida ?? '—' }}
                            </td>
                            <td class="text-center">
                                @if($item['practica']->convenio_firmado)
                                <span class="stitch-badge-success">Firmado</span>
                                @else
                                <span class="stitch-badge-neutral">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (md:hidden) -->
            <div class="p-4 space-y-3 md:hidden">
                @foreach($alumnosConPractica as $item)
                @php
                    $puestoItem = $item['puesto'] ?? null;
                @endphp
                <div class="stitch-card p-4 space-y-3 border border-slate-200">
                    <div class="flex items-center gap-3">
                        @if($item['alumno']->user->avatar_url)
                            <img src="{{ asset('storage/' . $item['alumno']->user->avatar_url) }}" alt="{{ $item['alumno']->user->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-[#0048FE] text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                {{ strtoupper(substr($item['alumno']->user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('alumnos.show', $item['alumno']) }}" class="font-bold text-slate-900 text-sm hover:text-[#0048FE] hover:underline block truncate">
                                {{ $item['alumno']->user->name }}
                            </a>
                            <p class="text-xs text-slate-400 truncate">{{ $item['alumno']->user->email }}</p>
                        </div>
                    </div>

                    @if($tieneEvaluaciones ?? false)
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Evaluación:</span>
                        @if(!empty($puestoItem))
                            @if($puestoItem == 1)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    🥇 Puesto 1 en clase (Mejor alumno)
                                </span>
                            @elseif($puestoItem == 2)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-300">
                                    🥈 Puesto 2 en clase (2º mejor)
                                </span>
                            @elseif($puestoItem == 3)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    🥉 Puesto 3 en clase
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                    Puesto {{ $puestoItem }} en clase
                                </span>
                            @endif
                        @else
                            <span class="text-slate-400 text-xs italic">
                                {{ ($item['total_observaciones'] ?? 0) > 0 ? 'Sin puesto en clase' : 'Sin evaluar' }}
                            </span>
                        @endif
                    </div>
                    @endif

                    <div class="pt-2 border-t border-slate-100 text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Empresa:</span>
                            <span class="font-bold text-slate-900">{{ $item['empresa']->nombre }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Especialidad:</span>
                            <span class="text-slate-600">{{ $item['practica']->oferta->especialidad_requerida ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Estado Convenio:</span>
                            @if($item['practica']->convenio_firmado)
                                <span class="stitch-badge-success text-[11px] px-2 py-0.5">Firmado</span>
                            @else
                                <span class="stitch-badge-neutral text-[11px] px-2 py-0.5">Pendiente</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</x-app-layout>

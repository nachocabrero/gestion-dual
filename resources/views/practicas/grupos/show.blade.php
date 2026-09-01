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
                    $grupos = \App\Models\Grupo::where('curso_academico_id', \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first()?->id)
                        ->with('linea')
                        ->orderBy('nombre')
                        ->get();
                @endphp
                @foreach($grupos as $g)
                <option value="{{ $g->id }}" {{ $g->id == $grupo->id ? 'selected' : '' }}>
                    {{ $g->nombre ?? ('Grupo ' . $g->numero) }} — {{ $g->linea?->nombre ?? '—' }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- SECCIÓN 1: Alumnos sin práctica -->
        <div class="stitch-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Alumnos sin práctica asignada</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ count($alumnosSinPractica) }} pendientes de asignación</p>
                </div>
            </div>

            @if(count($alumnosSinPractica) === 0)
                <div class="p-6 text-center text-sm font-medium text-emerald-600 bg-emerald-50/30">
                    ✅ Todos los alumnos de este grupo tienen práctica asignada.
                </div>
            @else
            <div class="stitch-table-container">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th class="text-center">Media Puesto</th>
                            <th>Ofertas aceptadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnosSinPractica as $item)
                        <tr class="{{ $item['tiene_oferta_aceptada'] ? 'bg-amber-50/40' : '' }}">
                            <td>
                                <span class="font-semibold text-slate-900">{{ $item['alumno']->user->name }}</span>
                                @if($item['tiene_oferta_aceptada'])
                                <span class="ml-2 stitch-badge-warning inline-flex">⚡ Oferta aceptada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['media_puesto'])
                                    <span class="font-bold {{ $item['media_puesto'] <= 10 ? 'text-emerald-600' : ($item['media_puesto'] <= 20 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $item['media_puesto'] }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item['ofertas_aceptadas']->isNotEmpty())
                                    <div class="flex flex-col gap-1">
                                    @foreach($item['ofertas_aceptadas'] as $sol)
                                        <div class="text-sm">
                                            <span class="font-semibold text-slate-800">{{ $sol->oferta->empresa->nombre }}</span>
                                            <span class="text-slate-500 text-xs">({{ $sol->oferta->especialidad_requerida }})</span>
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
            <div class="stitch-table-container">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Especialidad</th>
                            <th class="text-center">Plazas libres</th>
                            <th class="text-right">Detalle asignación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ofertasConPlazas['ofertas'] as $of)
                        <tr>
                            <td class="font-semibold text-slate-900">
                                {{ $of['oferta']->empresa->nombre }}
                            </td>
                            <td class="text-slate-600">
                                {{ $of['oferta']->especialidad_requerida }}
                            </td>
                            <td class="text-center">
                                <span class="stitch-badge-success text-sm font-bold px-2.5 py-1">{{ $of['plazas_libres'] }}</span>
                            </td>
                            <td class="text-right text-xs font-medium text-slate-500">
                                {{ $of['plazas_asignadas'] }} / {{ $of['total_plazas'] }} asignadas
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
            <div class="stitch-table-container">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Empresa</th>
                            <th>Especialidad</th>
                            <th class="text-center">Estado Convenio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnosConPractica as $item)
                        <tr>
                            <td class="font-semibold text-slate-900">
                                {{ $item['alumno']->user->name }}
                            </td>
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
            @endif
        </div>

    </div>
</x-app-layout>

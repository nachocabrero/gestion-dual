<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Prácticas por Grupo — ') . ($grupo->nombre ?? 'Grupo ' . $grupo->numero) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Selector de grupo -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('practicas.grupos.show', $grupo->id) }}">
                        <label for="grupo" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                            Seleccionar grupo:
                        </label>
                        <select id="grupo" name="grupo" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" onchange="this.form.submit()">
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
                    </form>
                </div>
            </div>

            <!-- SECCIÓN 1: Alumnos sin práctica -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Alumnos sin práctica asignada
                        <span class="text-sm font-normal text-gray-500">({{ count($alumnosSinPractica) }})</span>
                    </h3>

                    @if(count($alumnosSinPractica) === 0)
                        <p class="text-sm text-green-600 dark:text-green-400">✅ Todos los alumnos tienen práctica asignada.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Alumno</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Media Puesto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ofertas aceptadas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($alumnosSinPractica as $item)
                                <tr class="{{ $item['tiene_oferta_aceptada'] ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item['alumno']->user->name }}
                                        @if($item['tiene_oferta_aceptada'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            ⚡ Oferta aceptada
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        @if($item['media_puesto'])
                                            <span class="font-semibold {{ $item['media_puesto'] <= 10 ? 'text-green-600' : ($item['media_puesto'] <= 20 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $item['media_puesto'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        @if($item['ofertas_aceptadas']->isNotEmpty())
                                            @foreach($item['ofertas_aceptadas'] as $sol)
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium">{{ $sol->oferta->empresa->nombre }}</span>
                                                <span class="text-xs text-gray-500">({{ $sol->oferta->especialidad_requerida }})</span>
                                            </div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">—</span>
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

            <!-- SECCIÓN 2: Empresas con plazas libres -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Plazas disponibles
                        <span class="text-sm font-normal text-gray-500">({{ $ofertasConPlazas['total_libres'] }} plazas libres en total)</span>
                    </h3>

                    @if(count($ofertasConPlazas['ofertas']) === 0)
                        <p class="text-sm text-gray-500">No hay ofertas con plazas libres para este grupo.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Especialidad</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plazas libres</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Detalle</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($ofertasConPlazas['ofertas'] as $of)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $of['oferta']->empresa->nombre }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $of['oferta']->especialidad_requerida }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="font-bold text-green-600">{{ $of['plazas_libres'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $of['plazas_asignadas'] }} / {{ $of['total_plazas'] }} asignadas
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            <!-- SECCIÓN 3: Alumnos con práctica asignada -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Alumnos con práctica asignada
                        <span class="text-sm font-normal text-gray-500">({{ count($alumnosConPractica) }})</span>
                    </h3>

                    @if(count($alumnosConPractica) === 0)
                        <p class="text-sm text-gray-500">Ningún alumno tiene práctica asignada aún.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Alumno</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Especialidad</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($alumnosConPractica as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $item['alumno']->user->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $item['empresa']->nombre }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $item['practica']->oferta->especialidad_requerida ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($item['practica']->convenio_firmado)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Convenio firmado
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Sin convenio
                                        </span>
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

        </div>
    </div>
</x-app-layout>

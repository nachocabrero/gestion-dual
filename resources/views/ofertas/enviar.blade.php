<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Asignar alumnado a oferta de prácticas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $oferta->especialidad_requerida }}</h3>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $oferta->empresa?->nombre }}</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-[#0048FE] dark:bg-blue-900 dark:text-blue-200">
                                {{ $oferta->num_alumnos }} {{ $oferta->num_alumnos == 1 ? 'plaza disponible' : 'plazas disponibles' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $oferta->descripcion ?? 'Sin descripción adicional' }}</p>
                        @if($oferta->grupos->isNotEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Grupos destinatarios:
                            @foreach($oferta->grupos as $grupo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#0048FE]/10 text-[#0048FE] mr-1">
                                    {{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}
                                </span>
                            @endforeach
                        </p>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Selecciona los alumnos a los que se les asignará plaza en esta oferta. Al guardar, quedarán vinculados como asignados.
                        </p>
                    </div>

                    @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if($oferta->grupos->isEmpty() && count($grupos) === 0)
                    <div class="text-center py-16 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl">
                        <p class="text-lg font-medium text-gray-900 dark:text-white">Esta oferta no está dirigida a ningún grupo</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-6">
                            Asígnala a uno o varios grupos antes de poder vincular a los alumnos.
                        </p>
                        <a href="{{ route('ofertas.edit', $oferta) }}"
                           class="inline-flex items-center px-4 py-2 bg-[#0048FE] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Asignar grupos
                        </a>
                    </div>
                    @else
                    <form method="POST" action="{{ route('ofertas.enviar', $oferta) }}">
                        @csrf

                        @if(request('grupo_id') || !empty($grupoSeleccionadoId))
                            <input type="hidden" name="grupo_id" value="{{ request('grupo_id') ?? $grupoSeleccionadoId }}">
                        @endif
                        @if(request('redirect_to'))
                            <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                        @endif

                        <div x-data="envioOferta({{ Js::from($grupos) }}, {{ Js::from($alumnosAsignadosIds ?? []) }})">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-5 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30 border border-slate-200 dark:border-slate-700">
                                <div class="flex gap-2">
                                    <button type="button" @click="seleccionarTodos()"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200">
                                        Seleccionar todos los alumnos
                                    </button>
                                    <button type="button" @click="seleccionarNinguno()"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200">
                                        Desmarcar todos
                                    </button>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Alumnos seleccionados: <span class="text-[#0048FE] font-bold" x-text="seleccion.length"></span>
                                    <span class="text-xs text-slate-400 font-normal">/ {{ $oferta->num_alumnos }} plazas</span>
                                </span>
                            </div>

                            <div x-show="grupos.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <p class="text-lg">No hay alumnos en los grupos a los que está dirigida la oferta.</p>
                            </div>

                            <template x-for="grupo in grupos" :key="grupo.id">
                                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mb-4 bg-white dark:bg-slate-800 shadow-2xs">
                                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   :checked="grupoSeleccionadoCompleto(grupo)"
                                                   @change="toggleGrupo(grupo, $event.target.checked)"
                                                   class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                            <span class="font-bold text-gray-900 dark:text-white text-base" x-text="grupo.nombre"></span>
                                            <span class="text-xs text-slate-500 font-medium" x-text="'· ' + grupo.alumnos.length + ' alumnos'"></span>
                                        </label>

                                        <!-- Controles de ordenación si el grupo tiene observaciones de evaluación -->
                                        <template x-if="grupo.tiene_evaluaciones">
                                            <div class="flex items-center gap-1.5 text-xs bg-slate-50 dark:bg-slate-700/50 p-1 rounded-xl border border-slate-200 dark:border-slate-600">
                                                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 px-1.5">Ordenar por:</span>
                                                <button type="button" 
                                                        @click="setOrdenGrupo(grupo.id, 'nombre')"
                                                        :class="getOrdenGrupo(grupo.id) === 'nombre' ? 'bg-white dark:bg-slate-800 text-[#0048FE] font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900'"
                                                        class="px-2.5 py-1 rounded-lg text-xs transition-all">
                                                    Nombre (A-Z)
                                                </button>
                                                <button type="button" 
                                                        @click="setOrdenGrupo(grupo.id, 'evaluacion')"
                                                        :class="getOrdenGrupo(grupo.id) === 'evaluacion' ? 'bg-[#0048FE] text-white font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900'"
                                                        class="px-2.5 py-1 rounded-lg text-xs transition-all flex items-center gap-1">
                                                    <span>Evaluación (Puestos)</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Lista de Alumnos del Grupo -->
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        <template x-for="alumno in getAlumnosOrdenados(grupo)" :key="alumno.id">
                                            <div class="flex items-start justify-between gap-2 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50/40 dark:bg-slate-700/20 hover:bg-white dark:hover:bg-slate-700/50 transition-all">
                                                <label class="flex items-start gap-2.5 min-w-0 cursor-pointer flex-1">
                                                    <input type="checkbox" name="alumno_ids[]" :value="alumno.id" x-model="seleccion"
                                                           class="mt-1 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE] shadow-2xs">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <!-- Distintivo de posición si el grupo tiene evaluaciones y el alumno tiene puesto -->
                                                            <template x-if="grupo.tiene_evaluaciones && alumno.posicion">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold shadow-2xs"
                                                                      :class="alumno.posicion === 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (alumno.posicion === 2 ? 'bg-blue-100 text-blue-800 border border-blue-300' : (alumno.posicion === 3 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-300'))"
                                                                      :title="alumno.posicion === 1 ? '1º Mejor alumno según evaluación del profesorado' : (alumno.posicion === 2 ? '2º Mejor alumno según evaluación del profesorado' : 'Posición ' + alumno.posicion + ' en evaluación del profesorado')">
                                                                    <span x-text="alumno.posicion === 1 ? '🥇 #1 Mejor alumno' : (alumno.posicion === 2 ? '🥈 #2 Segundo' : (alumno.posicion === 3 ? '🥉 #3' : '#' + alumno.posicion))"></span>
                                                                </span>
                                                            </template>
                                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate" x-text="alumno.nombre"></span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 mt-1 text-xs text-gray-400">
                                                            <span class="truncate" x-text="alumno.email"></span>
                                                            <span x-show="alumno.tiene_vehiculo_propio" class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" title="Dispone de vehículo propio">
                                                                🚗 Vehículo
                                                            </span>
                                                        </div>
                                                    </div>
                                                </label>
                                                <!-- Botón de ver observaciones si tiene -->
                                                <template x-if="alumno.total_observaciones > 0">
                                                    <button type="button" 
                                                            @click="verObservacionesAlumno(alumno)"
                                                            class="shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-[#0048FE] hover:bg-blue-50 dark:hover:bg-blue-900/40 transition-colors"
                                                            :title="'Ver ' + alumno.total_observaciones + ' observación(es) del profesorado'">
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 rounded border border-blue-200 dark:border-blue-800">
                                                            💬 <span x-text="alumno.total_observaciones"></span>
                                                        </span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <p x-show="grupo.alumnos.length === 0" class="text-xs text-gray-400 col-span-full py-2">Sin alumnos matriculados en este grupo.</p>
                                    </div>
                                </div>
                            </template>

                            <!-- Modal de observaciones del alumno -->
                            <div x-show="alumnoObsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                    <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="alumnoObsModal = null"></div>
                                    <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 relative z-10">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                                            <div class="flex items-center gap-2">
                                                <span class="w-8 h-8 rounded-lg bg-blue-100 text-[#0048FE] flex items-center justify-center font-bold">💬</span>
                                                <div>
                                                    <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="alumnoObsModal?.nombre"></h3>
                                                    <p class="text-xs text-slate-500">Observaciones de evaluación del profesorado</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="alumnoObsModal = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold p-1">
                                                ✕
                                            </button>
                                        </div>
                                        <div class="mt-4 space-y-3 max-h-96 overflow-y-auto pr-1">
                                            <template x-for="obs in (alumnoObsModal?.observaciones || [])" :key="obs.id">
                                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-600 text-xs">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="obs.profesor_nombre"></span>
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="obs.puesto">
                                                                <span class="px-2 py-0.5 rounded font-extrabold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                                                    Puesto: #<span x-text="obs.puesto"></span>
                                                                </span>
                                                            </template>
                                                            <span class="text-slate-400 text-[10px]" x-text="obs.fecha"></span>
                                                        </div>
                                                    </div>
                                                    <template x-if="obs.titulo">
                                                        <h5 class="font-semibold text-slate-900 dark:text-white mb-1" x-text="obs.titulo"></h5>
                                                    </template>
                                                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed" x-text="obs.contenido"></p>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-5 flex justify-end">
                                            <button type="button" @click="alumnoObsModal = null" class="stitch-btn-secondary text-xs py-2 px-4">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mt-6">
                                <x-primary-button>Guardar asignación</x-primary-button>
                                <a href="{{ (request('grupo_id') || !empty($grupoSeleccionadoId)) ? route('practicas.grupos.show', request('grupo_id') ?? $grupoSeleccionadoId) : route('ofertas.show', $oferta) }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function envioOferta(grupos, asignados) {
            const ordenesInit = {};
            grupos.forEach(g => {
                ordenesInit[g.id] = g.tiene_evaluaciones ? 'evaluacion' : 'nombre';
            });
            return {
                grupos: grupos,
                seleccion: Array.isArray(asignados) ? [...asignados] : [],
                ordenes: ordenesInit,
                alumnoObsModal: null,
                getOrdenGrupo(grupoId) {
                    return this.ordenes[grupoId] || 'nombre';
                },
                setOrdenGrupo(grupoId, orden) {
                    this.ordenes[grupoId] = orden;
                },
                verObservacionesAlumno(alumno) {
                    this.alumnoObsModal = alumno;
                },
                getAlumnosOrdenados(grupo) {
                    const orden = this.getOrdenGrupo(grupo.id);
                    const list = [...grupo.alumnos];
                    if (orden === 'evaluacion' && grupo.tiene_evaluaciones) {
                        list.sort((a, b) => {
                            if (a.posicion !== null && b.posicion !== null) {
                                if (a.posicion !== b.posicion) return a.posicion - b.posicion;
                                return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });
                            }
                            if (a.posicion !== null && b.posicion === null) return -1;
                            if (a.posicion === null && b.posicion !== null) return 1;
                            return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });
                        });
                    } else {
                        list.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' }));
                    }
                    return list;
                },
                idsGrupo(grupo) {
                    return grupo.alumnos.map(a => a.id);
                },
                grupoSeleccionadoCompleto(grupo) {
                    const ids = this.idsGrupo(grupo);
                    return ids.length > 0 && ids.every(id => this.seleccion.includes(id));
                },
                toggleGrupo(grupo, checked) {
                    const ids = this.idsGrupo(grupo);
                    if (checked) {
                        ids.forEach(id => { if (!this.seleccion.includes(id)) this.seleccion.push(id); });
                    } else {
                        this.seleccion = this.seleccion.filter(id => !ids.includes(id));
                    }
                },
                seleccionarTodos() {
                    const ids = [];
                    this.grupos.forEach(g => g.alumnos.forEach(a => ids.push(a.id)));
                    this.seleccion = [...new Set(ids)];
                },
                seleccionarNinguno() {
                    this.seleccion = [];
                }
            };
        }
    </script>
</x-app-layout>
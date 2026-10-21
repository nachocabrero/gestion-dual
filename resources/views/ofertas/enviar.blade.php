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
                                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mb-3">
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox"
                                               :checked="grupoSeleccionadoCompleto(grupo)"
                                               @change="toggleGrupo(grupo, $event.target.checked)"
                                               class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="grupo.nombre"></span>
                                        <span class="text-xs text-gray-400" x-text="'· ' + grupo.alumnos.length + ' alumnos'"></span>
                                    </label>
                                    <div class="mt-3 grid gap-1.5 sm:grid-cols-2">
                                        <template x-for="alumno in grupo.alumnos" :key="alumno.id">
                                            <label class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/50 cursor-pointer border border-transparent hover:border-slate-200 transition-colors">
                                                <input type="checkbox" name="alumno_ids[]" :value="alumno.id" x-model="seleccion"
                                                       class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                                <div class="min-w-0">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-200 block truncate" x-text="alumno.nombre"></span>
                                                    <span class="text-xs text-gray-400 block truncate" x-text="alumno.email"></span>
                                                </div>
                                            </label>
                                        </template>
                                        <p x-show="grupo.alumnos.length === 0" class="text-xs text-gray-400 col-span-full">Sin alumnos matriculados en este grupo.</p>
                                    </div>
                                </div>
                            </template>

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
            return {
                grupos: grupos,
                seleccion: Array.isArray(asignados) ? [...asignados] : [],
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
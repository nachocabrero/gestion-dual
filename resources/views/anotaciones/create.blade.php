<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">
                    {{ __('Anotaciones y Puesto de la Clase') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Gestiona las observaciones y el orden de los alumnos de tu grupo en una vista unificada.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('anotaciones.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver a Anotaciones
                </a>
            </div>
        </div>
    </x-slot>

    <div class="stitch-container space-y-6">
        @if(session('success'))
        <div class="stitch-alert-success">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="stitch-alert-danger">
            <ul class="list-disc pl-5 space-y-1 text-xs">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Selector de Grupo / Clase -->
        <div class="stitch-toolbar flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                <label for="select_grupo" class="text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Grupo / Clase:
                </label>
                @if($grupos->isNotEmpty())
                <div class="w-full sm:w-80">
                    <select id="select_grupo" class="stitch-input text-xs font-semibold" onchange="window.location.href = '{{ route('anotaciones.create') }}?grupo_id=' + this.value">
                        @foreach($grupos as $g)
                        <option value="{{ $g->id }}" {{ $selectedGrupo && $selectedGrupo->id == $g->id ? 'selected' : '' }}>
                            {{ $g->nombre }} ({{ $g->linea->ciclo->codigo ?? 'Ciclo' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @if($selectedGrupo)
            <div class="flex items-center gap-2 shrink-0">
                <span class="stitch-badge-neutral text-xs">
                    {{ $alumnos->count() }} alumnos en este grupo
                </span>
                @if($selectedGrupo->cursoAcademico)
                <span class="stitch-badge-info text-xs">
                    Curso {{ $selectedGrupo->cursoAcademico->nombre }}
                </span>
                @endif
            </div>
            @endif
        </div>

        @if($grupos->isEmpty())
        <div class="stitch-card p-12 text-center text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <h3 class="text-base font-bold text-slate-700">No tienes grupos asignados</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                No tienes asignado ningún grupo al que impartas clase o del que seas tutor docente. Si crees que se trata de un error, contacta con la jefatura de estudios.
            </p>
        </div>
        @elseif(!$selectedGrupo || $alumnos->isEmpty())
        <div class="stitch-card p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-sm font-medium text-slate-600">Este grupo no tiene alumnos matriculados actualmente.</p>
        </div>
        @else

        <!-- Contenedor Alpine.js para ordenación reactiva -->
        <div x-data="anotacionesGrupoApp({{ Js::from($alumnosData) }})" class="space-y-4">
            
            <!-- Aviso informativo de ordenación dinámica -->
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3.5 flex items-center justify-between text-xs text-blue-900 gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0048FE] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Ordenación automática:</strong> Conforme asignes o modifiques el puesto en la clase (1, 2, 3...), el listado se reordenará en tiempo real. Los alumnos sin puesto se mantienen al final.</span>
                </div>
                <button type="submit" form="form-anotaciones-grupo" class="stitch-btn-primary text-xs py-1.5 px-3 shrink-0 hidden sm:inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar Cambios
                </button>
            </div>

            <form id="form-anotaciones-grupo" method="POST" action="{{ route('anotaciones.store') }}">
                @csrf
                <input type="hidden" name="grupo_id" value="{{ $selectedGrupo->id }}">

                <!-- Vista Tabla Desktop -->
                <div class="stitch-table-container hidden sm:block shadow-sm">
                    <table class="stitch-table">
                        <thead>
                            <tr>
                                <th class="text-center w-24">Puesto</th>
                                <th class="w-1/4 min-w-[200px]">Alumno</th>
                                <th class="w-1/4 min-w-[180px]">Título</th>
                                <th class="flex-1 min-w-[260px]">Contenido / Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in sortedAlumnos" :key="item.id">
                                <tr class="hover:bg-slate-50/80 transition-colors" :class="item.puesto ? 'bg-indigo-50/20' : ''">
                                    <!-- Puesto en clase -->
                                    <td class="text-center align-middle">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <input type="hidden" :name="'anotaciones[' + item.id + '][alumno_id]'" :value="item.id">
                                            <input type="hidden" :name="'anotaciones[' + item.id + '][anotacion_id]'" :value="item.anotacion_id">
                                            
                                            <div class="relative flex items-center justify-center">
                                                <input type="number" 
                                                       :name="'anotaciones[' + item.id + '][puesto]'" 
                                                       :value="item.puesto"
                                                       @input.debounce.500ms="updatePuesto(item, $event.target.value)"
                                                       @change="updatePuesto(item, $event.target.value)"
                                                       min="1" 
                                                       max="50" 
                                                       placeholder="—"
                                                       title="Puesto del alumno en la clase"
                                                       class="stitch-input text-center font-bold text-xs !py-1.5 w-16 text-slate-800 focus:border-[#0048FE] focus:ring-1 focus:ring-[#0048FE]">
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Alumno -->
                                    <td class="align-middle">
                                        <div class="flex items-center gap-3">
                                            <template x-if="item.avatar_url">
                                                <img :src="item.avatar_url" :alt="item.name" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                            </template>
                                            <template x-if="!item.avatar_url">
                                                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-sm" title="Sin foto de perfil">
                                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                </div>
                                            </template>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm" x-text="item.name"></p>
                                                <p class="text-xs text-slate-400" x-text="item.email"></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Título -->
                                    <td class="align-middle">
                                        <input type="text" 
                                               :name="'anotaciones[' + item.id + '][titulo]'" 
                                               x-model="item.titulo" 
                                               placeholder="Título (ej: Rendimiento BD...)" 
                                               maxlength="255"
                                               class="stitch-input text-xs !py-1.5 w-full">
                                    </td>

                                    <!-- Contenido / Observación (compacto) -->
                                    <td class="align-middle">
                                        <input type="text" 
                                               :name="'anotaciones[' + item.id + '][contenido]'" 
                                               x-model="item.contenido" 
                                               placeholder="Observación o anotación del alumno..." 
                                               maxlength="2000"
                                               class="stitch-input text-xs !py-1.5 w-full">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Vista Tarjetas Mobile -->
                <div class="grid grid-cols-1 gap-3 sm:hidden">
                    <template x-for="(item, index) in sortedAlumnos" :key="item.id">
                        <div class="stitch-card p-4 space-y-3" :class="item.puesto ? 'border-indigo-200 bg-indigo-50/10' : ''">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <template x-if="item.avatar_url">
                                        <img :src="item.avatar_url" :alt="item.name" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                    </template>
                                    <template x-if="!item.avatar_url">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-sm" title="Sin foto de perfil">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                    </template>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm" x-text="item.name"></h4>
                                        <p class="text-xs text-slate-400" x-text="item.email"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-xs font-semibold text-slate-500">Puesto:</span>
                                    <input type="number" 
                                           :name="'anotaciones[' + item.id + '][puesto]'" 
                                           :value="item.puesto"
                                           @input.debounce.500ms="updatePuesto(item, $event.target.value)"
                                           @change="updatePuesto(item, $event.target.value)"
                                           min="1" 
                                           max="50" 
                                           placeholder="—" 
                                           class="stitch-input text-center font-bold text-xs !py-1 w-14">
                                </div>
                            </div>

                            <input type="hidden" :name="'anotaciones[' + item.id + '][alumno_id]'" :value="item.id">
                            <input type="hidden" :name="'anotaciones[' + item.id + '][anotacion_id]'" :value="item.anotacion_id">

                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <div>
                                    <input type="text" 
                                           :name="'anotaciones[' + item.id + '][titulo]'" 
                                           x-model="item.titulo" 
                                           placeholder="Título de la anotación..." 
                                           class="stitch-input text-xs !py-1.5 w-full">
                                </div>
                                <div>
                                    <input type="text" 
                                           :name="'anotaciones[' + item.id + '][contenido]'" 
                                           x-model="item.contenido" 
                                           placeholder="Contenido u observaciones..." 
                                           class="stitch-input text-xs !py-1.5 w-full">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Barra de Acciones Inferior -->
                <div class="stitch-card p-4 mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/70 border-slate-200">
                    <div class="text-xs text-slate-500">
                        <span class="font-semibold text-slate-700">Nota:</span> Se guardarán o actualizarán las anotaciones de aquellos alumnos en los que se haya introducido puesto, título u observación.
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <a href="{{ route('anotaciones.index') }}" class="stitch-btn-secondary text-xs py-2 px-4 w-full sm:w-auto text-center">
                            Cancelar
                        </a>
                        <button type="submit" class="stitch-btn-primary text-xs py-2 px-6 w-full sm:w-auto flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Guardar Anotaciones del Grupo
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function anotacionesGrupoApp(initialAlumnos) {
            return {
                alumnos: initialAlumnos || [],
                get sortedAlumnos() {
                    return [...this.alumnos].sort((a, b) => {
                        const pA = (a.puesto !== '' && a.puesto !== null && !isNaN(a.puesto)) ? parseInt(a.puesto, 10) : null;
                        const pB = (b.puesto !== '' && b.puesto !== null && !isNaN(b.puesto)) ? parseInt(b.puesto, 10) : null;

                        if (pA !== null && pB !== null) {
                            if (pA !== pB) return pA - pB;
                            return a.name.localeCompare(b.name, 'es', { sensitivity: 'base' });
                        }
                        if (pA !== null) return -1;
                        if (pB !== null) return 1;

                        const nameCmp = a.name.localeCompare(b.name, 'es', { sensitivity: 'base' });
                        if (nameCmp !== 0) return nameCmp;

                        return a.initial_index - b.initial_index;
                    });
                },
                updatePuesto(item, value) {
                    const clean = String(value).trim();
                    if (clean === '') {
                        item.puesto = '';
                    } else {
                        const parsed = parseInt(clean, 10);
                        item.puesto = isNaN(parsed) ? '' : Math.max(1, Math.min(parsed, 50));
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
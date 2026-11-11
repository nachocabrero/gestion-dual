<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">
                    @if(auth()->id() === $profesor->user_id)
                        Editar Perfil Docente
                    @else
                        Editar Profesor
                    @endif
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if(auth()->id() === $profesor->user_id)
                        Gestiona tus datos, especialidad, grupos a los que das clase y módulos que impartes
                    @else
                        Modificación de datos, roles, tutorías y docencia de {{ $profesor->user->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('profesores.show', $profesor) }}" class="hlanz-btn-secondary">Ver perfil</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('profesores.update', $profesor) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $profesor->user->name) }}" required class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        @error('name') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $profesor->user->email) }}" required class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        @error('email') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Especialidad</label>
                        <input type="text" name="especialidad" value="{{ old('especialidad', $profesor->especialidad) }}" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Familia profesional</label>
                        <p class="text-xs text-slate-500 mb-2">Filtrar grupos por familia profesional (puedes cambiar de familia o ver todas las familias para asignar grupos y módulos).</p>
                        <select name="familia_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]" onchange="window.location = '{{ route('profesores.edit', $profesor) }}' + (this.value ? '?familia=' + this.value : '')">
                            <option value="">— Todas las familias —</option>
                            @foreach($familias as $f)
                            <option value="{{ $f->id }}" {{ (string) old('familia_id', $familia?->id) === (string) $f->id ? 'selected' : '' }}>{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
                    <div class="mb-4 flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tutor del grupo (opcional)</label>
                            @php
                                $tutorGrupo = $profesor->gruposTutor()->first();
                                $tutorGrupoId = $tutorGrupo?->id;
                                $tutorEnLista = false;
                                foreach($gruposCursoActual as $cicloGrupos) {
                                    if ($cicloGrupos->contains('id', $tutorGrupoId)) {
                                        $tutorEnLista = true;
                                        break;
                                    }
                                }
                            @endphp
                            <select name="tutor_grupo_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Ninguno</option>
                                @if($tutorGrupo && !$tutorEnLista)
                                    <optgroup label="Tutoría actual asignada">
                                        <option value="{{ $tutorGrupo->id }}" selected>{{ $tutorGrupo->nombre }} @if($tutorGrupo->linea?->ciclo) ({{ $tutorGrupo->linea->ciclo->nombre }}) @endif</option>
                                    </optgroup>
                                @endif
                                @foreach($gruposCursoActual as $cicloGrupos)
                                    @php $cicloTutor = $cicloGrupos->first()->linea->ciclo; @endphp
                                    <optgroup label="{{ $cicloTutor->nombre }}">
                                        @foreach($cicloGrupos as $grupo)
                                            <option value="{{ $grupo->id }}" {{ old('tutor_grupo_id', $tutorGrupoId) == $grupo->id ? 'selected' : '' }}>{{ $grupo->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center mt-6">
                            <label class="flex items-center text-sm text-slate-700">
                                <input type="checkbox" name="es_coordinador_dual" value="1" {{ old('es_coordinador_dual', $profesor->es_coordinador_dual) ? 'checked' : '' }} class="mr-2 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                Coordinador Dual
                            </label>
                        </div>
                    </div>
                    @else
                        @php $tutorGrupo = $profesor->gruposTutor()->first(); @endphp
                        @if($tutorGrupo)
                        <div class="mb-4 p-3.5 rounded-xl bg-blue-50/60 border border-blue-200 text-xs text-blue-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#0048FE] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Tutoría oficial asignada: <strong>{{ $tutorGrupo->nombre }}</strong> ({{ $tutorGrupo->linea?->ciclo?->nombre ?? 'Ciclo' }}). Solo jefatura/coordinación puede reasignar tutorías oficiales.</span>
                        </div>
                        @endif
                    @endif

                    <!-- Grupos a los que da clase y módulo formativo que imparte -->
                    <div class="mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="mb-3">
                            <label class="block text-sm font-bold text-slate-900">
                                Grupos a los que da clase y módulos que imparte
                                @if($cursoActual)<span class="ml-1 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60">(Curso actual: {{ $cursoActual->nombre }})</span>@endif
                            </label>
                            <p class="text-xs text-slate-500 mt-1">
                                Marca las casillas de los grupos a los que das clase este curso y selecciona el módulo o asignatura correspondiente que impartes en cada uno.
                            </p>
                        </div>

                        <div class="max-h-80 overflow-y-auto border border-slate-200 bg-white rounded-xl p-3 text-slate-900 divide-y divide-slate-100 shadow-inner">
                            @php
                                $imparte = collect($profesor->gruposImpartidos);
                            @endphp
                            @forelse($gruposCursoActual as $cicloGrupos)
                            @php $ciclo = $cicloGrupos->first()->linea->ciclo; @endphp
                            <div class="pt-3 pb-2 first:pt-0">
                                <div class="font-bold text-xs text-blue-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                    <span>{{ $ciclo->nombre }}</span>
                                    <span class="text-[11px] font-normal text-slate-400">({{ $ciclo->asignaturas->count() }} módulos disponibles)</span>
                                </div>
                                <div class="space-y-2.5">
                                @foreach($cicloGrupos as $grupo)
                                @php
                                    $oldGrupo = old("grupos.{$grupo->id}");
                                    $marcado = $oldGrupo !== null ? !empty($oldGrupo['activo']) : (bool) ($imparte->first(fn($g) => $g->id == $grupo->id));
                                    $asigActiva = $oldGrupo !== null ? ($oldGrupo['asignatura_id'] ?? '') : ($imparte->first(fn($g) => $g->id == $grupo->id)?->pivot->asignatura_id ?? '');
                                @endphp
                                <div class="p-2.5 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 transition-colors" x-data="{ marcado: {{ $marcado ? 'true' : 'false' }} }">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer sm:w-1/3 shrink-0">
                                            <input type="checkbox" name="grupos[{{ $grupo->id }}][activo]" value="1" x-model="marcado" {{ $marcado ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE] w-4 h-4">
                                            <span class="text-sm font-semibold text-slate-800">{{ $grupo->nombre }}</span>
                                        </label>
                                        <div class="flex-1">
                                            <select name="grupos[{{ $grupo->id }}][asignatura_id]" x-ref="sel{{ $grupo->id }}" :disabled="!marcado" x-bind:class="marcado && $refs.sel{{ $grupo->id }}.value === '' ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400' : ''" class="block w-full rounded-xl border-slate-300 px-3 py-1.5 text-sm focus:border-[#0048FE] focus:ring-[#0048FE] disabled:bg-slate-100 disabled:text-slate-400">
                                                <option value="">— Seleccionar módulo que imparte —</option>
                                                @foreach($ciclo->asignaturas as $asig)
                                                <option value="{{ $asig->id }}" {{ (string) $asigActiva === (string) $asig->id ? 'selected' : '' }}>{{ $asig->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <p x-show="marcado && $refs.sel{{ $grupo->id }}.value === ''" class="text-xs text-rose-600 font-semibold mt-1 ml-6" style="display: none;">
                                        ⚠️ Debes seleccionar el módulo que impartes en este grupo (es obligatorio).
                                    </p>
                                </div>
                                @endforeach
                                </div>
                            </div>
                            @empty
                            <div class="py-6 text-center text-sm text-slate-400">
                                @if($familia)
                                No hay grupos de {{ $familia->nombre }} en el curso actual.
                                @else
                                No hay grupos disponibles en el curso actual.
                                @endif
                            </div>
                            @endforelse
                        </div>
                        @error('grupos')
                        <span class="text-rose-600 text-xs mt-2 block font-semibold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Historial de grupos impartidos (cursos anteriores) -->
                    <div class="mb-4" x-data="{ abierto: false }">
                        <button type="button" @click="abierto = !abierto" class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors text-sm font-bold text-slate-700">
                            <span>Historial de grupos impartidos en cursos anteriores ({{ $historial->count() }})</span>
                            <svg x-show="!abierto" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <svg x-show="abierto" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <div x-show="abierto" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-3 border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                            @forelse($historial->groupBy(fn($g) => $g->cursoAcademico?->nombre ?? 'Sin curso') as $cursoNombre => $gruposHist)
                            <div class="px-4 py-2 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $cursoNombre }}</div>
                            @foreach($gruposHist as $grupo)
                            <div class="px-4 py-2 bg-white flex items-center justify-between gap-4">
                                <span class="text-sm font-semibold text-slate-900">{{ $grupo->nombre }}</span>
                                <span class="text-xs text-slate-500">
                                    {{ $grupo->asignaturaHistorial?->nombre ?? '—' }}
                                    <span class="text-slate-400">({{ $grupo->linea->ciclo->nombre }})</span>
                                </span>
                            </div>
                            @endforeach
                            @empty
                            <p class="px-4 py-3 text-sm text-slate-400">Sin historial de cursos anteriores.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="hlanz-btn-primary">Guardar cambios</button>
                        <a href="{{ route('profesores.show', $profesor) }}" class="hlanz-btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
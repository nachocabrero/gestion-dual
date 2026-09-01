<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Editar Profesor</h2>
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
                        <p class="text-xs text-slate-500 mb-2">Al elegir la familia se recargará la lista de grupos disponibles (solo se muestran grupos de esta familia en el curso actual).</p>
                        <select name="familia_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]" onchange="window.location = '{{ route('profesores.edit', $profesor) }}' + (this.value ? '?familia=' + this.value : '')">
                            <option value="">— Seleccionar familia —</option>
                            @foreach($familias as $f)
                            <option value="{{ $f->id }}" {{ old('familia_id', $familia?->id) == $f->id ? 'selected' : '' }}>{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tutor del grupo (opcional)</label>
                            @php
                                $tutorGrupoId = $profesor->gruposTutor()->first()->id ?? null;
                            @endphp
                            <select name="tutor_grupo_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Ninguno</option>
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

                    <!-- Grupos del curso actual por familia profesional -->
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Grupos a los que da clase y asignatura
                            @if($cursoActual)<span class="ml-1 text-xs font-normal text-slate-400">(curso actual: {{ $cursoActual->nombre }})</span>@endif
                        </label>
                        <div class="max-h-64 overflow-y-auto border border-slate-200 rounded-xl p-2 text-slate-900">
                            @php
                                $imparte = collect($profesor->gruposImpartidos);
                            @endphp
                            @forelse($gruposCursoActual as $cicloGrupos)
                            @php $ciclo = $cicloGrupos->first()->linea->ciclo; @endphp
                            <div class="font-bold text-xs text-slate-500 uppercase tracking-wider mt-2 mb-1">{{ $ciclo->nombre }}</div>
                            @foreach($cicloGrupos as $grupo)
                            @php
                                $oldGrupo = old("grupos.{$grupo->id}");
                                $marcado = $oldGrupo !== null ? !empty($oldGrupo['activo']) : (bool) ($imparte->first(fn($g) => $g->id == $grupo->id));
                                $asigActiva = $oldGrupo !== null ? ($oldGrupo['asignatura_id'] ?? '') : ($imparte->first(fn($g) => $g->id == $grupo->id)?->pivot->asignatura_id ?? '');
                            @endphp
                            <div class="py-1" x-data="{ marcado: {{ $marcado ? 'true' : 'false' }} }">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="grupos[{{ $grupo->id }}][activo]" value="1" x-model="marcado" {{ $marcado ? 'checked' : '' }} class="mr-2 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                    <label class="text-sm whitespace-nowrap">{{ $grupo->nombre }}</label>
                                    <select name="grupos[{{ $grupo->id }}][asignatura_id]" x-ref="sel{{ $grupo->id }}" x-bind:class="marcado && $refs.sel{{ $grupo->id }}.value === '' ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400' : ''" class="block w-full rounded-xl border-slate-300 px-2 py-1 text-sm focus:border-[#0048FE] focus:ring-[#0048FE]">
                                        <option value="">— Asignatura —</option>
                                        @foreach($ciclo->asignaturas as $asig)
                                        <option value="{{ $asig->id }}" {{ (string) $asigActiva === (string) $asig->id ? 'selected' : '' }}>{{ $asig->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p x-show="marcado && $refs.sel{{ $grupo->id }}.value === ''" class="text-xs text-rose-500 mt-1 ml-1">Elige una asignatura para este grupo (es obligatoria).</p>
                            </div>
                            @endforeach
                            @empty
                            <p class="text-sm text-slate-400">
                                @if($familia)
                                No hay grupos de {{ $familia->nombre }} en el curso actual.
                                @else
                                Selecciona una familia profesional para ver sus grupos.
                                @endif
                            </p>
                            @endforelse
                        </div>
                        @error('grupos')
                        <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Historial de grupos impartidos (cursos anteriores) -->
                    <div class="mb-4" x-data="{ abierto: false }">
                        <button type="button" @click="abierto = !abierto" class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors text-sm font-bold text-slate-700">
                            <span>Historial de grupos impartidos ({{ $historial->count() }})</span>
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
                        <button type="submit" class="hlanz-btn-primary">Actualizar</button>
                        <a href="{{ route('profesores.show', $profesor) }}" class="hlanz-btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
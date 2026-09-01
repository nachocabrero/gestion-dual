<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('alumnos.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Nuevo Alumno
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Alta de expediente académico y usuario en el sistema</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <form method="POST" action="{{ route('alumnos.store') }}" class="space-y-6">
            @csrf

            <!-- Datos Personales Card -->
            <div class="stitch-card p-6 sm:p-8 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Datos Personales y Acceso
                    </h3>
                    <p class="text-xs text-slate-500">Información básica del alumno para su perfil institucional</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="stitch-label">Nombre Completo *</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="stitch-input" placeholder="Ej. María García López">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="email" class="stitch-label">Correo Electrónico *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="stitch-input" placeholder="alumno@ejemplo.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password" class="stitch-label">Contraseña *</label>
                        <input id="password" name="password" type="password" required class="stitch-input" placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="stitch-label">Confirmar Contraseña *</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="stitch-input" placeholder="••••••••">
                    </div>

                    <div>
                        <label for="telefono" class="stitch-label">Teléfono de Contacto</label>
                        <input id="telefono" name="telefono" type="text" value="{{ old('telefono') }}" class="stitch-input" placeholder="600 000 000">
                    </div>

                    <div>
                        <label for="fecha_nacimiento" class="stitch-label">Fecha de Nacimiento</label>
                        <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}" class="stitch-input">
                    </div>

                    <div class="md:col-span-2">
                        <label for="domicilio" class="stitch-label">Domicilio Habitual</label>
                        <input id="domicilio" name="domicilio" type="text" value="{{ old('domicilio') }}" class="stitch-input" placeholder="Calle, número, piso, ciudad...">
                    </div>

                    <div class="md:col-span-2">
                        <label for="linkedin_url" class="stitch-label">Perfil de LinkedIn</label>
                        <input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url') }}" class="stitch-input" placeholder="https://linkedin.com/in/nombre-usuario">
                    </div>
                </div>
            </div>

            <!-- Datos Académicos Card -->
            <div class="stitch-card p-6 sm:p-8 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        Matriculación y Grupos
                    </h3>
                    <p class="text-xs text-slate-500">Asignación de grupos clase, tutor de prácticas y ciclos matriculados</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="stitch-label">Grupos Asignados</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                            @foreach($grupos as $grupo)
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white transition-colors cursor-pointer">
                                <input type="checkbox" name="grupos_ids[]" value="{{ $grupo->id }}" {{ is_array(old('grupos_ids')) && in_array($grupo->id, old('grupos_ids')) ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                <div>
                                    <span class="text-sm font-bold text-slate-900 block">{{ $grupo->nombre }}</span>
                                    <span class="text-xs text-slate-500">{{ $grupo->linea->ciclo->codigo ?? '' }} • Turno {{ ucfirst($grupo->linea->turno ?? '') }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="tutor_practicas_id" class="stitch-label">Tutor de Prácticas Asignado</label>
                        <select id="tutor_practicas_id" name="tutor_practicas_id" class="stitch-input">
                            <option value="">Seleccionar profesor tutor...</option>
                            @foreach($grupos->pluck('tutor')->filter() as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_practicas_id') == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="stitch-label">Matrícula en Ciclos Formativos</label>
                        <p class="text-xs text-slate-500 mb-2">Selecciona un ciclo y especifica el curso académico para registrar la matrícula.</p>

                        <div class="flex flex-col sm:flex-row gap-2 mb-3">
                            <select id="nuevo_ciclo" class="stitch-input sm:flex-1">
                                <option value="">Seleccionar ciclo...</option>
                                @foreach($grupos->pluck('linea.ciclo')->flatten()->unique('id') as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->codigo }} — {{ $ciclo->nombre }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="nuevo_curso" placeholder="2026-2027" class="stitch-input sm:w-36" value="2026-2027">
                            <button type="button" id="btn-add-create" class="stitch-btn-secondary text-xs shrink-0">
                                + Añadir Matrícula
                            </button>
                        </div>

                        <div id="matriculas-container-create" class="space-y-2">
                            @if(is_array(old('matriculas')))
                            @foreach(old('matriculas') as $m)
                            <input type="hidden" name="matriculas[][ciclo_id]" value="{{ $m['ciclo_id'] }}">
                            <input type="hidden" name="matriculas[][curso_academico]" value="{{ $m['curso_academico'] }}">
                            @endforeach
                            @endif
                        </div>
                        <x-input-error :messages="$errors->get('matriculas')" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('alumnos.index') }}" class="stitch-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="stitch-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Alumno</span>
                </button>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('btn-add-create').addEventListener('click', function() {
        const cicloSelect = document.getElementById('nuevo_ciclo');
        const cursoInput = document.getElementById('nuevo_curso');
        const container = document.getElementById('matriculas-container-create');

        if (!cicloSelect.value || !cursoInput.value) {
            alert('Por favor selecciona un ciclo e indica el curso académico.');
            return;
        }

        const cicloTexto = cicloSelect.options[cicloSelect.selectedIndex].text;
        const cicloId = cicloSelect.value;
        const cursoVal = cursoInput.value;

        const tag = document.createElement('div');
        tag.className = 'flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-sm font-medium';
        tag.innerHTML = `
            <span><strong>${cicloTexto}</strong> (Curso ${cursoVal})</span>
            <button type="button" class="text-rose-600 hover:text-rose-800 text-xs font-bold" onclick="this.parentElement.remove()">Eliminar</button>
            <input type="hidden" name="matriculas[][ciclo_id]" value="${cicloId}">
            <input type="hidden" name="matriculas[][curso_academico]" value="${cursoVal}">
        `;
        container.appendChild(tag);
    });
    </script>
</x-app-layout>
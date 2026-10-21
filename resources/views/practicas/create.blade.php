<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            @if(!empty($preselectedOfertaId))
                <a href="{{ route('ofertas.show', $preselectedOfertaId) }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            @else
                <a href="{{ route('practicas.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            @endif
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    {{ __('Nueva Práctica') }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Asignación y registro de periodo de formación en empresa colaboradora</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Se encontraron errores al procesar el formulario</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($preselectedOfertaId))
            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs sm:text-sm flex items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg bg-[#0048FE] text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span>Creando práctica vinculada a la <strong>Oferta de Prácticas #{{ $preselectedOfertaId }}</strong>. Al guardarla, el estado del alumno pasará automáticamente a <strong>Asignado</strong>.</span>
                </div>
                <a href="{{ route('ofertas.show', $preselectedOfertaId) }}" class="text-xs font-bold text-[#0048FE] hover:underline shrink-0">Ver oferta →</a>
            </div>
            @endif

            <div class="stitch-card p-6 sm:p-8"
                 x-data="practicaCreateForm({
                     alumnos: {{ Js::from($alumnosData) }},
                     tutores: {{ Js::from($tutoresData) }},
                     initialAlumnoId: '{{ old('alumno_id', $preselectedAlumnoId ?? '') }}',
                     initialEmpresaId: '{{ old('empresa_id', $preselectedEmpresaId ?? '') }}',
                     initialTutorId: '{{ old('tutor_laboral_id', '') }}',
                     initialHoras: '{{ old('horas_acumuladas', '') }}'
                 })">

                <form method="POST" action="{{ route('practicas.store') }}" class="space-y-6">
                    @csrf

                    @if(!empty($preselectedOfertaId))
                        <input type="hidden" name="oferta_id" value="{{ $preselectedOfertaId }}">
                    @endif

                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-bold font-display text-slate-900">Datos Principales de la Práctica</h3>
                        <p class="text-xs text-slate-500">Selecciona el alumno y la empresa receptora</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Alumno -->
                        <div>
                            <label for="alumno_id" class="stitch-label">Alumno Candidato *</label>
                            <select id="alumno_id" name="alumno_id" required
                                    x-model="selectedAlumno"
                                    @change="onAlumnoChange()"
                                    class="stitch-input @error('alumno_id') border-rose-300 ring-rose-200 @enderror">
                                <option value="">Seleccionar alumno...</option>
                                @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}">{{ $alumno->user->name ?? ('Alumno #' . $alumno->id) }}</option>
                                @endforeach
                            </select>
                            @error('alumno_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Empresa -->
                        <div>
                            <label for="empresa_id" class="stitch-label">Empresa Colaboradora *</label>
                            <select id="empresa_id" name="empresa_id" required
                                    x-model="selectedEmpresa"
                                    @change="onEmpresaChange()"
                                    class="stitch-input @error('empresa_id') border-rose-300 ring-rose-200 @enderror">
                                <option value="">Seleccionar empresa...</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tutor Laboral -->
                        <div>
                            <label for="tutor_laboral_id" class="stitch-label">Tutor Laboral (Empresa)</label>
                            <select id="tutor_laboral_id" name="tutor_laboral_id"
                                    x-model="selectedTutor"
                                    class="stitch-input @error('tutor_laboral_id') border-rose-300 ring-rose-200 @enderror"
                                    :disabled="!selectedEmpresa">
                                <option value="">Seleccionar tutor laboral...</option>
                                <template x-for="tutor in availableTutores" :key="tutor.id">
                                    <option :value="tutor.id" x-text="tutor.nombre"></option>
                                </template>
                            </select>
                            <template x-if="!selectedEmpresa">
                                <p class="mt-1 text-xs text-slate-400">Selecciona primero la empresa para elegir uno de sus tutores.</p>
                            </template>
                            <template x-if="selectedEmpresa && availableTutores.length === 0">
                                <p class="mt-1 text-xs text-amber-600">Esta empresa no tiene tutores laborales registrados aún.</p>
                            </template>
                            @error('tutor_laboral_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Curso Académico -->
                        <div>
                            <label for="curso_academico_id" class="stitch-label">Curso Académico *</label>
                            <select id="curso_academico_id" name="curso_academico_id" required
                                    class="stitch-input @error('curso_academico_id') border-rose-300 ring-rose-200 @enderror">
                                <option value="">Seleccionar curso...</option>
                                @foreach($cursos as $curso)
                                <option value="{{ $curso->id }}" {{ old('curso_academico_id', $curso->is_active ? $curso->id : '') == $curso->id ? 'selected' : '' }}>
                                    {{ $curso->nombre }} {{ $curso->is_active ? '(Curso Activo)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('curso_academico_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <div class="border-b border-slate-100 pb-4 mb-5">
                            <h3 class="text-lg font-bold font-display text-slate-900">Temporalización y Cómputo de Horas</h3>
                            <p class="text-xs text-slate-500">Fechas de estancia y horas de formación acreditadas</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Fecha Inicio -->
                            <div>
                                <label for="fecha_inicio" class="stitch-label">Fecha de Inicio *</label>
                                <input id="fecha_inicio" type="date" name="fecha_inicio" required
                                       value="{{ old('fecha_inicio') }}"
                                       class="stitch-input @error('fecha_inicio') border-rose-300 ring-rose-200 @enderror">
                                @error('fecha_inicio') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Fecha Fin -->
                            <div>
                                <label for="fecha_fin" class="stitch-label">Fecha de Finalización *</label>
                                <input id="fecha_fin" type="date" name="fecha_fin" required
                                       value="{{ old('fecha_fin') }}"
                                       class="stitch-input @error('fecha_fin') border-rose-300 ring-rose-200 @enderror">
                                @error('fecha_fin') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Horas acumuladas -->
                            <div class="md:col-span-2">
                                <label for="horas_acumuladas" class="stitch-label">Horas Acumuladas en este periodo *</label>
                                <input id="horas_acumuladas" type="number" name="horas_acumuladas" required min="0"
                                       x-model="horasAcumuladas"
                                       class="stitch-input @error('horas_acumuladas') border-rose-300 ring-rose-200 @enderror">
                                @error('horas_acumuladas') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                                <!-- Anotación de horas y empresa de 1º curso -->
                                <div class="mt-3">
                                    <template x-if="currentAlumno">
                                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 space-y-1 text-xs">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-900">
                                                <svg class="w-4 h-4 text-[#0048FE] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Anotación de Prácticas de 1º Curso:</span>
                                            </div>
                                            <p class="leading-relaxed">
                                                El alumno realizó las prácticas de 1º en
                                                <span class="font-bold text-slate-900" x-text="currentAlumno.empresa_primero ? currentAlumno.empresa_primero : 'sin empresa registrada'"></span>,
                                                con un total de <span class="font-bold text-slate-900" x-text="currentAlumno.horas_primero + ' horas'"></span> acreditadas en 1º curso.
                                            </p>
                                            <p class="text-[11px] text-slate-500">
                                                (Este valor se ha cargado por defecto en las horas acumuladas; puedes modificarlo si esta práctica corresponde a un cómputo distinto).
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="!currentAlumno">
                                        <p class="text-xs text-slate-400 italic">
                                            Selecciona un alumno para verificar dónde realizó las prácticas en 1º curso y cargar sus horas por defecto.
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <!-- Convenio firmado -->
                            <div class="md:col-span-2 pt-2">
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:bg-slate-100/50 transition-colors">
                                    <input type="checkbox" name="convenio_firmado" value="1" {{ old('convenio_firmado') ? 'checked' : '' }}
                                           class="w-4 h-4 rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 block">Convenio y Acuerdo de Colaboración Firmado</span>
                                        <span class="text-[11px] text-slate-500 block">Marcar si la empresa y el centro ya han formalizado el anexo correspondiente</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        @if(!empty($preselectedOfertaId))
                            <a href="{{ route('ofertas.show', $preselectedOfertaId) }}" class="stitch-btn-secondary">
                                Cancelar
                            </a>
                        @else
                            <a href="{{ route('practicas.index') }}" class="stitch-btn-secondary">
                                Cancelar
                            </a>
                        @endif
                        <button type="submit" class="stitch-btn-primary">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Registrar Práctica
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function practicaCreateForm(config) {
        return {
            alumnos: config.alumnos || [],
            tutores: config.tutores || [],
            selectedAlumno: config.initialAlumnoId ? String(config.initialAlumnoId) : '',
            selectedEmpresa: config.initialEmpresaId ? String(config.initialEmpresaId) : '',
            selectedTutor: config.initialTutorId ? String(config.initialTutorId) : '',
            horasAcumuladas: config.initialHoras !== '' ? Number(config.initialHoras) : 0,
            horasIniciadasManualmente: config.initialHoras !== '',

            init() {
                if (this.selectedAlumno && !this.horasIniciadasManualmente) {
                    this.syncAlumnoHoras();
                }
            },

            get currentAlumno() {
                var currentId = this.selectedAlumno;
                if (!currentId) return null;
                return this.alumnos.find(function(a) {
                    return String(a.id) === String(currentId);
                }) || null;
            },

            get availableTutores() {
                var empId = this.selectedEmpresa;
                if (!empId) return [];
                return this.tutores.filter(function(t) {
                    return String(t.empresa_id) === String(empId);
                });
            },

            onAlumnoChange() {
                this.syncAlumnoHoras();
            },

            syncAlumnoHoras() {
                var al = this.currentAlumno;
                if (al) {
                    this.horasAcumuladas = Number(al.horas_primero || 0);
                } else {
                    this.horasAcumuladas = 0;
                }
            },

            onEmpresaChange() {
                var tutores = this.availableTutores;
                var currentTutor = this.selectedTutor;
                var exists = tutores.some(function(t) {
                    return String(t.id) === String(currentTutor);
                });
                if (!exists) {
                    this.selectedTutor = '';
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
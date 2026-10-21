<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('mensajes.index') }}" class="hover:text-[#0048FE] transition-colors">Mensajería</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Redactar comunicado</span>
                </div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Nuevo Comunicado Oficial
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    Envío masivo con notificación en plataforma y aviso por correo electrónico institucional
                </p>
            </div>
            <div>
                <a href="{{ route('mensajes.index') }}" class="stitch-btn-secondary inline-flex items-center gap-1.5 text-xs font-semibold">
                    <span>← Volver al historial</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto" x-data="mensajeriaForm()">

        @if($errors->any())
        <div class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Por favor revisa los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('mensajes.store') }}" class="space-y-6">
            @csrf

            <!-- 1. SELECCIÓN DE DESTINATARIOS -->
            <div class="stitch-card p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0048FE] flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Destinatarios del Comunicado</h3>
                        <p class="text-xs text-slate-500">Selecciona el tipo de audiencia a la que irá dirigido el mensaje</p>
                    </div>
                </div>

                <!-- Selector de Tipo de Destinatario (3 opciones solicitadas) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                    <!-- Opción 1: Grupos completos -->
                    <label :class="tipo === 'grupos' ? 'border-[#0048FE] bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="p-2 rounded-lg bg-blue-100 text-[#0048FE]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            <input type="radio" name="tipo_destinatario" value="grupos" x-model="tipo" class="text-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <span class="font-bold text-sm text-slate-900">Grupos completos</span>
                        <span class="text-xs text-slate-500 mt-1">Enviar a todos los alumnos de uno o varios grupos académicos.</span>
                    </label>

                    <!-- Opción 2: Alumnos específicos dentro de un grupo -->
                    <label :class="tipo === 'alumnos_grupo' ? 'border-[#0048FE] bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="p-2 rounded-lg bg-amber-100 text-amber-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="radio" name="tipo_destinatario" value="alumnos_grupo" x-model="tipo" class="text-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <span class="font-bold text-sm text-slate-900">Alumnos seleccionados</span>
                        <span class="text-xs text-slate-500 mt-1">Elegir un grupo y marcar específicamente las personas deseadas.</span>
                    </label>

                    <!-- Opción 3: Profesorado de ciclos -->
                    <label :class="tipo === 'profesores_ciclos' ? 'border-[#0048FE] bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="p-2 rounded-lg bg-purple-100 text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input type="radio" name="tipo_destinatario" value="profesores_ciclos" x-model="tipo" class="text-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <span class="font-bold text-sm text-slate-900">Profesorado de ciclos</span>
                        <span class="text-xs text-slate-500 mt-1">Docentes que imparten o tutelan uno o varios ciclos formativos.</span>
                    </label>
                </div>

                <!-- SUB-SECCIÓN: GRUPOS COMPLETOS -->
                <div x-show="tipo === 'grupos'" x-transition class="space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Selecciona los grupos:</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="toggleAllGrupos(true)" class="text-xs font-semibold text-[#0048FE] hover:underline">
                                Marcar todos
                            </button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="toggleAllGrupos(false)" class="text-xs font-semibold text-slate-500 hover:underline">
                                Desmarcar todos
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($grupos as $grupo)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-slate-50/80 cursor-pointer transition-colors">
                            <input type="checkbox" name="grupo_ids[]" value="{{ $grupo->id }}"
                                   x-model="selectedGrupos"
                                   class="mt-1 rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-sm text-slate-900 truncate">{{ $grupo->nombre }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $grupo->alumnos->filter(fn($a) => $a->user && $a->user->is_active)->count() }} alumno(s) activos
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- SUB-SECCIÓN: ALUMNOS DENTRO DE UN GRUPO -->
                <div x-show="tipo === 'alumnos_grupo'" x-transition class="space-y-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Paso 1: Elige el grupo
                        </label>
                        <select name="grupo_id" x-model="selectedGrupoParaAlumnos" @change="onGrupoAlumnosChange()"
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-[#0048FE] focus:ring-[#0048FE]">
                            <option value="">-- Selecciona un grupo para cargar sus alumnos --</option>
                            @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">
                                {{ $grupo->nombre }} ({{ $grupo->alumnos->filter(fn($a) => $a->user && $a->user->is_active)->count() }} alumnos)
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="selectedGrupoParaAlumnos" x-transition class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                                <span>Paso 2: Marca las personas destinatarias</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800"
                                      x-text="selectedAlumnos.length + ' seleccionados'"></span>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="text" x-model="filtroAlumnoText" placeholder="Buscar por nombre o email..."
                                       class="text-xs rounded-lg border-slate-200 py-1.5 px-3 focus:border-[#0048FE] focus:ring-[#0048FE] w-52">
                                <button type="button" @click="toggleAllCurrentAlumnos(true)" class="text-xs font-semibold text-[#0048FE] hover:underline whitespace-nowrap">
                                    Todos
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="toggleAllCurrentAlumnos(false)" class="text-xs font-semibold text-slate-500 hover:underline whitespace-nowrap">
                                    Ninguno
                                </button>
                            </div>
                        </div>

                        <div class="max-h-60 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100 bg-white p-2">
                            <template x-for="alumno in currentGrupoAlumnos" :key="alumno.id">
                                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="checkbox" name="alumno_ids[]" :value="alumno.id"
                                               x-model="selectedAlumnos"
                                               class="rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 truncate" x-text="alumno.name"></div>
                                            <div class="text-[11px] text-slate-400 truncate" x-text="alumno.email"></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Alumno</span>
                                </label>
                            </template>

                            <div x-show="currentGrupoAlumnos.length === 0" class="text-center py-6 text-xs text-slate-400">
                                No se encontraron alumnos en este grupo que coincidan con la búsqueda.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUB-SECCIÓN: PROFESORADO DE CICLOS -->
                <div x-show="tipo === 'profesores_ciclos'" x-transition class="space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Selecciona los ciclos formativos:</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="toggleAllCiclos(true)" class="text-xs font-semibold text-[#0048FE] hover:underline">
                                Marcar todos
                            </button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="toggleAllCiclos(false)" class="text-xs font-semibold text-slate-500 hover:underline">
                                Desmarcar todos
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($ciclos as $ciclo)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/30 cursor-pointer transition-colors">
                            <input type="checkbox" name="ciclo_ids[]" value="{{ $ciclo->id }}"
                                   x-model="selectedCiclos"
                                   class="mt-1 rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-sm text-slate-900">{{ $ciclo->nombre }}</div>
                                <div class="text-xs text-purple-700 font-medium">
                                    {{ $ciclo->familia->nombre ?? 'Ciclo Formativo' }} · Grado {{ ucfirst($ciclo->grado) }}
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- 2. CONTENIDO DEL MENSAJE -->
            <div class="stitch-card p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0048FE] flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Redacción del Mensaje</h3>
                        <p class="text-xs text-slate-500">Este texto llegará como notificación in-app y correo electrónico con el diseño oficial y logo</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="asunto" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Asunto del comunicado <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="asunto" name="asunto" value="{{ old('asunto') }}" required
                               placeholder="Ej: Convocatoria extraordinaria de FCT / Jornadas técnicas de empresa"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-[#0048FE] focus:ring-[#0048FE] font-medium">
                        <p class="text-[11px] text-slate-400 mt-1">
                            El asunto del correo enviado será: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600">Nueva notificación de Gestión DUAL H.Lanz: [Tu asunto]</code>
                        </p>
                    </div>

                    <div>
                        <label for="cuerpo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Cuerpo del mensaje <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="cuerpo" name="cuerpo" rows="7" required
                                  placeholder="Escribe aquí el contenido completo del comunicado para los destinatarios..."
                                  class="w-full rounded-xl border-slate-200 text-sm focus:border-[#0048FE] focus:ring-[#0048FE] leading-relaxed">{{ old('cuerpo') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 3. AVISO INFORMATIVO Y CONFIRMACIÓN -->
            <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-slate-600 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-blue-100 text-[#0048FE] shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <p class="font-bold text-slate-800">Entrega garantizada multicanal:</p>
                    <p>
                        1. <strong>Notificación in-app:</strong> Cada destinatario verá el aviso en su campana de notificaciones y al pinchar en él accederá al mensaje (quedando registrado como leído).
                    </p>
                    <p>
                        2. <strong>Correo electrónico:</strong> Recibirán un email corporativo con el logotipo del centro, el cuerpo del mensaje redactado y un botón de acceso directo a la plataforma.
                    </p>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('mensajes.index') }}" class="stitch-btn-secondary text-sm">
                    Cancelar
                </a>
                <button type="submit" class="stitch-btn-primary inline-flex items-center gap-2 text-sm shadow-md shadow-blue-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Enviar comunicado ahora</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function mensajeriaForm() {
            return {
                tipo: @json(old('tipo_destinatario', 'grupos')),
                selectedGrupos: @json(old('grupo_ids', [])),
                selectedCiclos: @json(old('ciclo_ids', [])),
                selectedGrupoParaAlumnos: @json(old('grupo_id', '')),
                selectedAlumnos: @json(old('alumno_ids', [])),
                filtroAlumnoText: '',
                gruposData: @json($gruposData),
                ciclosCount: {{ $ciclos->count() }},

                get currentGrupoAlumnos() {
                    if (!this.selectedGrupoParaAlumnos) return [];
                    const g = this.gruposData.find(x => x.id == this.selectedGrupoParaAlumnos);
                    if (!g) return [];
                    if (!this.filtroAlumnoText.trim()) return g.alumnos;
                    const q = this.filtroAlumnoText.toLowerCase();
                    return g.alumnos.filter(a => a.name.toLowerCase().includes(q) || a.email.toLowerCase().includes(q));
                },

                toggleAllGrupos(check) {
                    if (check) {
                        this.selectedGrupos = this.gruposData.map(g => String(g.id));
                    } else {
                        this.selectedGrupos = [];
                    }
                },

                toggleAllCiclos(check) {
                    if (check) {
                        this.selectedCiclos = @json($ciclos->pluck('id')->map(fn($id) => (string)$id));
                    } else {
                        this.selectedCiclos = [];
                    }
                },

                toggleAllCurrentAlumnos(check) {
                    const currentIds = this.currentGrupoAlumnos.map(a => String(a.id));
                    if (check) {
                        this.selectedAlumnos = Array.from(new Set([...this.selectedAlumnos, ...currentIds]));
                    } else {
                        this.selectedAlumnos = this.selectedAlumnos.filter(id => !currentIds.includes(String(id)));
                    }
                },

                onGrupoAlumnosChange() {
                    this.selectedAlumnos = [];
                    this.filtroAlumnoText = '';
                }
            };
        }
    </script>
</x-app-layout>

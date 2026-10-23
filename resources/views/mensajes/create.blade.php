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

                <!-- Selector de Tipo de Destinatario (4 opciones) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
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

                    <!-- Opción 4: Empresas colaboradoras -->
                    <label :class="tipo === 'empresas' ? 'border-[#0048FE] bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="p-2 rounded-lg bg-emerald-100 text-emerald-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <input type="radio" name="tipo_destinatario" value="empresas" x-model="tipo" class="text-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <span class="font-bold text-sm text-slate-900">Empresas</span>
                        <span class="text-xs text-slate-500 mt-1">Por profesor de seguimiento y/o empresas sin asignar.</span>
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

                <!-- SUB-SECCIÓN: EMPRESAS COLABORADORAS -->
                <div x-show="tipo === 'empresas'" x-transition class="space-y-4 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block">
                                Selección combinada de empresas colaboradoras
                            </span>
                            <span class="text-xs text-slate-500">
                                Selecciona por profesor de seguimiento y/o empresas sin asignar.
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800"
                                  x-text="selectedEmpresas.length + ' empresa(s) seleccionada(s)'"></span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="toggleAllEmpresas(true)" class="text-xs font-semibold text-[#0048FE] hover:underline">
                                    Todas
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="toggleAllEmpresas(false)" class="text-xs font-semibold text-slate-500 hover:underline">
                                    Ninguna
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador de empresas -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" x-model="filtroEmpresaText" placeholder="Filtrar empresa por nombre, CIF o email..."
                                   class="stitch-input !pl-9 text-xs">
                        </div>
                    </div>

                    <!-- Lista de categorías por profesor y sin asignar -->
                    <div class="space-y-3">
                        <!-- Categorías por profesor -->
                        @foreach($profesoresConEmpresas as $prof)
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-xs"
                             x-show="matchesProfesorFilter({{ $prof->id }})" x-cloak>
                            <div class="flex items-center justify-between p-3.5 bg-slate-50/70 border-b border-slate-100 hover:bg-slate-100/60 transition-colors">
                                <label class="flex items-center gap-3 cursor-pointer min-w-0 flex-1">
                                    <input type="checkbox" name="profesor_ids[]" value="{{ $prof->id }}"
                                           :checked="isProfesorChecked({{ $prof->id }})"
                                           @change="toggleProfesor({{ $prof->id }}, $event.target.checked)"
                                           class="rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                                    <div class="min-w-0">
                                        <span class="font-bold text-sm text-slate-900 block truncate">{{ $prof->user->name ?? 'Profesor #' . $prof->id }}</span>
                                        <span class="text-xs text-slate-500">{{ $prof->empresas->count() }} empresa(s) de seguimiento</span>
                                    </div>
                                </label>
                                <button type="button" @click="toggleProfesorOpen({{ $prof->id }})" class="p-1 rounded-lg hover:bg-slate-200 text-slate-500 text-xs inline-flex items-center gap-1 shrink-0">
                                    <span x-text="isProfesorOpen({{ $prof->id }}) ? 'Ocultar' : 'Ver empresas'"></span>
                                    <svg class="w-4 h-4 transform transition-transform" :class="isProfesorOpen({{ $prof->id }}) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <div x-show="isProfesorOpen({{ $prof->id }})" class="p-3 bg-white divide-y divide-slate-100 max-h-56 overflow-y-auto">
                                @foreach($prof->empresas as $emp)
                                <label class="flex items-center justify-between py-2 px-2 rounded-lg hover:bg-slate-50 cursor-pointer"
                                       x-show="matchesEmpresaFilter('{{ addslashes($emp->nombre) }}', '{{ $emp->cif }}', '{{ $emp->email }}')">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="empresa_ids[]" value="{{ $emp->id }}"
                                               x-model="selectedEmpresas"
                                               class="rounded text-[#0048FE] focus:ring-[#0048FE] border-slate-300">
                                        <div class="min-w-0">
                                            <span class="text-xs font-semibold text-slate-800 block truncate">{{ $emp->nombre }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $emp->cif }}</span>
                                        </div>
                                    </div>
                                    <div class="text-[11px] shrink-0 ml-2">
                                        @if($emp->email)
                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-mono truncate max-w-[200px] inline-block">{{ $emp->email }}</span>
                                        @else
                                            <span class="text-slate-400 italic text-[10px]">Sin email</span>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <!-- Categoría: Empresas SIN profesor asignado -->
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-xs"
                             x-show="matchesSinProfesorFilter()" x-cloak>
                            <div class="flex items-center justify-between p-3.5 bg-amber-50/50 border-b border-amber-100 hover:bg-amber-100/50 transition-colors">
                                <label class="flex items-center gap-3 cursor-pointer min-w-0 flex-1">
                                    <input type="checkbox" name="sin_profesor" value="1"
                                           x-model="selectedSinProfesor"
                                           @change="toggleSinProfesor($event.target.checked)"
                                           class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                                    <div class="min-w-0">
                                        <span class="font-bold text-sm text-slate-900 block truncate">Empresas sin profesor asignado</span>
                                        <span class="text-xs text-amber-700 font-medium">{{ $empresasSinProfesor->count() }} empresa(s) sin seguimiento</span>
                                    </div>
                                </label>
                                <button type="button" @click="sinProfesorOpen = !sinProfesorOpen" class="p-1 rounded-lg hover:bg-amber-200/50 text-slate-600 text-xs inline-flex items-center gap-1 shrink-0">
                                    <span x-text="sinProfesorOpen ? 'Ocultar' : 'Ver empresas'"></span>
                                    <svg class="w-4 h-4 transform transition-transform" :class="sinProfesorOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                            <div x-show="sinProfesorOpen" class="p-3 bg-white divide-y divide-slate-100 max-h-56 overflow-y-auto">
                                @foreach($empresasSinProfesor as $emp)
                                <label class="flex items-center justify-between py-2 px-2 rounded-lg hover:bg-slate-50 cursor-pointer"
                                       x-show="matchesEmpresaFilter('{{ addslashes($emp->nombre) }}', '{{ $emp->cif }}', '{{ $emp->email }}')">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="empresa_ids[]" value="{{ $emp->id }}"
                                               x-model="selectedEmpresas"
                                               class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                                        <div class="min-w-0">
                                            <span class="text-xs font-semibold text-slate-800 block truncate">{{ $emp->nombre }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $emp->cif }}</span>
                                        </div>
                                    </div>
                                    <div class="text-[11px] shrink-0 ml-2">
                                        @if($emp->email)
                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-mono truncate max-w-[200px] inline-block">{{ $emp->email }}</span>
                                        @else
                                            <span class="text-slate-400 italic text-[10px]">Sin email</span>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
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

                // Empresas
                profesoresEmpresasData: @json($profesoresEmpresasData),
                empresasSinProfesorData: @json($empresasSinProfesorData),
                selectedProfesorIds: @json(old('profesor_ids', [])),
                selectedSinProfesor: @json((bool) old('sin_profesor', false)),
                selectedEmpresas: @json(old('empresa_ids', [])),
                filtroEmpresaText: '',
                openProfesorTabs: {},
                sinProfesorOpen: false,

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
                },

                // Métodos para selección de empresas
                isProfesorOpen(profId) {
                    return !!this.openProfesorTabs[profId];
                },

                toggleProfesorOpen(profId) {
                    this.openProfesorTabs[profId] = !this.openProfesorTabs[profId];
                },

                isProfesorChecked(profId) {
                    const prof = this.profesoresEmpresasData.find(p => p.id == profId);
                    if (!prof || prof.empresas.length === 0) return false;
                    const empIds = prof.empresas.map(e => String(e.id));
                    return empIds.every(id => this.selectedEmpresas.includes(id));
                },

                toggleProfesor(profId, checked) {
                    const prof = this.profesoresEmpresasData.find(p => p.id == profId);
                    if (!prof) return;
                    const empIds = prof.empresas.map(e => String(e.id));
                    if (checked) {
                        this.selectedEmpresas = Array.from(new Set([...this.selectedEmpresas, ...empIds]));
                        if (!this.selectedProfesorIds.includes(String(profId))) {
                            this.selectedProfesorIds.push(String(profId));
                        }
                    } else {
                        this.selectedEmpresas = this.selectedEmpresas.filter(id => !empIds.includes(String(id)));
                        this.selectedProfesorIds = this.selectedProfesorIds.filter(id => id != String(profId));
                    }
                },

                toggleSinProfesor(checked) {
                    const sinProfEmpIds = this.empresasSinProfesorData.map(e => String(e.id));
                    this.selectedSinProfesor = checked;
                    if (checked) {
                        this.selectedEmpresas = Array.from(new Set([...this.selectedEmpresas, ...sinProfEmpIds]));
                    } else {
                        this.selectedEmpresas = this.selectedEmpresas.filter(id => !sinProfEmpIds.includes(String(id)));
                    }
                },

                toggleAllEmpresas(checked) {
                    if (checked) {
                        const allEmpresas = [];
                        this.profesoresEmpresasData.forEach(p => {
                            p.empresas.forEach(e => allEmpresas.push(String(e.id)));
                        });
                        this.empresasSinProfesorData.forEach(e => allEmpresas.push(String(e.id)));
                        this.selectedEmpresas = Array.from(new Set(allEmpresas));
                        this.selectedProfesorIds = this.profesoresEmpresasData.map(p => String(p.id));
                        this.selectedSinProfesor = true;
                    } else {
                        this.selectedEmpresas = [];
                        this.selectedProfesorIds = [];
                        this.selectedSinProfesor = false;
                    }
                },

                matchesEmpresaFilter(nombre, cif, email) {
                    if (!this.filtroEmpresaText.trim()) return true;
                    const q = this.filtroEmpresaText.toLowerCase();
                    return (nombre && nombre.toLowerCase().includes(q)) ||
                           (cif && cif.toLowerCase().includes(q)) ||
                           (email && email.toLowerCase().includes(q));
                },

                matchesProfesorFilter(profId) {
                    if (!this.filtroEmpresaText.trim()) return true;
                    const prof = this.profesoresEmpresasData.find(p => p.id == profId);
                    if (!prof) return false;
                    const q = this.filtroEmpresaText.toLowerCase();
                    if (prof.name && prof.name.toLowerCase().includes(q)) return true;
                    return prof.empresas.some(e =>
                        (e.nombre && e.nombre.toLowerCase().includes(q)) ||
                        (e.cif && e.cif.toLowerCase().includes(q)) ||
                        (e.email && e.email.toLowerCase().includes(q))
                    );
                },

                matchesSinProfesorFilter() {
                    if (!this.filtroEmpresaText.trim()) return true;
                    const q = this.filtroEmpresaText.toLowerCase();
                    return this.empresasSinProfesorData.some(e =>
                        (e.nombre && e.nombre.toLowerCase().includes(q)) ||
                        (e.cif && e.cif.toLowerCase().includes(q)) ||
                        (e.email && e.email.toLowerCase().includes(q))
                    );
                }
            };
        }
    </script>
</x-app-layout>

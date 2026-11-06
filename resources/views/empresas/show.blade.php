<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('empresas.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                        {{ $empresa->nombre }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium">CIF: {{ $empresa->cif }} • Empresa Colaboradora</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('empresas.edit', $empresa) }}" class="stitch-btn-secondary text-xs py-2 px-3">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar Empresa
                </a>
                <a href="{{ route('empresas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                    Volver al listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Datos Generales -->
        <div class="stitch-card p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Datos Fiscales y Contacto
                    </h3>
                    <p class="text-xs text-slate-500">Ficha informativa de la entidad</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($empresa->admite_alumnos_curso_actual)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200" title="Coge alumnado en el presente curso escolar">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Coge alumnado
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Ha indicado que no coge alumnado este curso escolar">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> No coge alumnado este curso
                        </span>
                    @endif

                    @if($empresa->is_active)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activa
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactiva
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Nombre / Razón Social</span>
                    <span class="text-slate-900 font-medium">{{ $empresa->nombre }}</span>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">CIF / NIF</span>
                    <span class="text-slate-900 font-mono font-bold">{{ $empresa->cif }}</span>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Teléfono(s)</span>
                    @if($empresa->telefono && $empresa->telefono_2)
                        <span class="text-slate-900 font-medium">
                            <a href="tel:{{ $empresa->telefono }}" class="hover:underline text-slate-900">{{ $empresa->telefono }}</a> - <a href="tel:{{ $empresa->telefono_2 }}" class="hover:underline text-slate-900">{{ $empresa->telefono_2 }}</a>
                        </span>
                    @elseif($empresa->telefono)
                        <span class="text-slate-900 font-medium">
                            <a href="tel:{{ $empresa->telefono }}" class="hover:underline text-slate-900">{{ $empresa->telefono }}</a>
                        </span>
                    @elseif($empresa->telefono_2)
                        <span class="text-slate-900 font-medium">
                            <a href="tel:{{ $empresa->telefono_2 }}" class="hover:underline text-slate-900">{{ $empresa->telefono_2 }}</a>
                        </span>
                    @else
                        <span class="text-slate-500">—</span>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Dirección</span>
                    <span class="text-slate-900 font-medium">{{ $empresa->direccion ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Email General</span>
                    @if($empresa->email)
                        <a href="mailto:{{ $empresa->email }}" class="text-[#0048FE] hover:underline font-medium">{{ $empresa->email }}</a>
                    @else
                        <span class="text-slate-500">—</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Representante Legal</span>
                    <span class="text-slate-900 font-medium">{{ $empresa->responsable_nombre ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">DNI Representante</span>
                    <span class="text-slate-900 font-mono">{{ $empresa->responsable_dni ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Profesor de Seguimiento</span>
                    @if($empresa->profesor && $empresa->profesor->user)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $empresa->profesor->user->name }}
                        </span>
                    @else
                        <span class="text-slate-400 italic text-xs">Sin asignar</span>
                    @endif
                </div>
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Coge Alumnado Este Curso</span>
                    @if($empresa->admite_alumnos_curso_actual)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sí, disponible
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> No coge alumnado
                        </span>
                    @endif
                </div>
            </div>

            @if($empresa->anotaciones)
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-amber-950">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="space-y-1">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900">Anotaciones / Referencias / Información sobre Becas</h4>
                        <p class="text-sm text-amber-900/90 whitespace-pre-line leading-relaxed">{{ $empresa->anotaciones }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Tutores Laborales -->
        <div class="stitch-card p-6 sm:p-8 space-y-6" x-data="{ nuevoTutorOpen: false }">
            <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Tutores Laborales ({{ $empresa->tutoresLaborales->count() }})
                    </h3>
                    <p class="text-xs text-slate-500">Contactos directos de seguimiento de alumnos en prácticas</p>
                </div>
                <button type="button" @click="nuevoTutorOpen = !nuevoTutorOpen"
                        class="stitch-btn-primary text-xs py-1.5 px-3 self-start sm:self-auto flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="nuevoTutorOpen ? 'Cerrar formulario' : 'Añadir Tutor Laboral'">Añadir Tutor Laboral</span>
                </button>
            </div>

            <!-- Formulario desplegable para nuevo tutor -->
            <div x-show="nuevoTutorOpen" style="display: none;" x-transition class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 space-y-4">
                <div class="flex items-center justify-between border-b border-blue-100 pb-2">
                    <h4 class="text-sm font-bold text-slate-900">Registrar Nuevo Tutor Laboral</h4>
                    <span class="text-xs text-slate-500">Se asociará directamente a {{ $empresa->nombre }}</span>
                </div>
                <form method="POST" action="{{ route('empresas.tutores.store', $empresa) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="stitch-label">Nombre Completo *</label>
                            <input type="text" name="nombre" required placeholder="Nombre del tutor" class="stitch-input bg-white text-xs">
                        </div>
                        <div>
                            <label class="stitch-label">DNI / NIE *</label>
                            <input type="text" name="dni" required placeholder="12345678Z" class="stitch-input uppercase bg-white text-xs">
                        </div>
                        <div>
                            <label class="stitch-label">Email</label>
                            <input type="email" name="email" placeholder="tutor@empresa.com" class="stitch-input bg-white text-xs">
                        </div>
                        <div>
                            <label class="stitch-label">Teléfono</label>
                            <input type="text" name="telefono" placeholder="600 000 000" class="stitch-input bg-white text-xs">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="nuevoTutorOpen = false" class="stitch-btn-secondary text-xs py-1.5 px-3">Cancelar</button>
                        <button type="submit" class="stitch-btn-primary text-xs py-1.5 px-3">Guardar Tutor</button>
                    </div>
                </form>
            </div>

            @if($empresa->tutoresLaborales->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($empresa->tutoresLaborales as $tutor)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#0048FE] flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($tutor->nombre, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-sm text-slate-900 truncate">{{ $tutor->nombre }}</h4>
                                @if($tutor->dni)
                                <p class="text-[11px] font-mono text-slate-500">DNI: {{ $tutor->dni }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-slate-600 space-y-1 pt-1">
                            @if($tutor->email)
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <a href="mailto:{{ $tutor->email }}" class="text-[#0048FE] hover:underline truncate">{{ $tutor->email }}</a>
                            </div>
                            @endif
                            @if($tutor->telefono)
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $tutor->telefono }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">No hay tutores laborales registrados para esta empresa.</p>
            @endif
        </div>

        <!-- Registro de Comunicaciones -->
        <div class="stitch-card p-6 sm:p-8 space-y-6" x-data="{
            nuevaComOpen: false,
            editModalOpen: false,
            editForm: {
                id: null,
                tipo: 'telefono',
                motivo: '',
                respuesta: '',
                actionUrl: ''
            },
            abrirEdicion(com) {
                this.editForm.id = com.id;
                this.editForm.tipo = com.tipo;
                this.editForm.motivo = com.motivo;
                this.editForm.respuesta = com.respuesta || '';
                this.editForm.actionUrl = '{{ url('empresas/' . $empresa->id . '/comunicaciones') }}/' + com.id;
                this.editModalOpen = true;
            }
        }">
            <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Registro de Comunicaciones ({{ $comunicacionesActual->count() + $comunicacionesPasadas->sum->count() }})
                    </h3>
                    <p class="text-xs text-slate-500">Historial de contactos con la empresa por teléfono, email o visita presencial</p>
                </div>
                @if($canManageComunicaciones)
                <button type="button" @click="nuevaComOpen = !nuevaComOpen"
                        class="stitch-btn-primary text-xs py-1.5 px-3 self-start sm:self-auto flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="nuevaComOpen ? 'Cerrar formulario' : 'Añadir Comunicación'">Añadir Comunicación</span>
                </button>
                @endif
            </div>

            <!-- Formulario desplegable para nueva comunicación -->
            @if($canManageComunicaciones)
            <div x-show="nuevaComOpen" style="display: none;" x-transition class="p-5 rounded-2xl border border-blue-200 bg-blue-50/40 space-y-4">
                <div class="flex items-center justify-between border-b border-blue-100 pb-2">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Registrar Nueva Comunicación
                    </h4>
                    <span class="text-xs text-slate-500">Se registrará automáticamente con fecha y hora actual</span>
                </div>
                <form method="POST" action="{{ route('empresas.comunicaciones.store', $empresa) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="stitch-label block mb-1.5 font-bold text-xs text-slate-700">Canal de Contacto *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-800 transition-colors">
                                <input type="radio" name="tipo" value="telefono" checked class="text-blue-600 focus:ring-blue-500">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>Teléfono</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-800 transition-colors">
                                <input type="radio" name="tipo" value="email" class="text-indigo-600 focus:ring-indigo-500">
                                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Email / Correo</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-800 transition-colors">
                                <input type="radio" name="tipo" value="presencial" class="text-emerald-600 focus:ring-emerald-500">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Visita presencial</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Motivo de la Comunicación *</label>
                        <textarea name="motivo" required rows="2" placeholder="Ej. Solicitud de plazas para FCT, coordinación de fechas de incorporación, seguimiento del alumno..." class="stitch-input bg-white text-xs w-full"></textarea>
                    </div>

                    <div>
                        <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Respuesta Obtenida (opcional)</label>
                        <textarea name="respuesta" rows="2" placeholder="Ej. Confirman disponibilidad de 2 vacantes en turno de mañana. Contactarán con el tutor laboral..." class="stitch-input bg-white text-xs w-full"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="nuevaComOpen = false" class="stitch-btn-secondary text-xs py-1.5 px-3">Cancelar</button>
                        <button type="submit" class="stitch-btn-primary text-xs py-1.5 px-3">Guardar Comunicación</button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Comunicaciones del Curso Actual (Siempre Visibles) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Curso Actual {{ $cursoActual ? '(' . $cursoActual->nombre . ')' : '' }}
                    </h4>
                    <span class="text-xs text-slate-400 font-medium">{{ $comunicacionesActual->count() }} {{ $comunicacionesActual->count() === 1 ? 'registro' : 'registros' }}</span>
                </div>

                @if($comunicacionesActual->count() > 0)
                    <div class="space-y-3">
                        @foreach($comunicacionesActual as $com)
                            @php
                                $puedeGestionar = $canManageComunicaciones || (auth()->user()->profesor && $com->profesor_id === auth()->user()->profesor->id);
                            @endphp
                            <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-colors shadow-sm space-y-2.5">
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($com->tipo === 'telefono')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                Teléfono
                                            </span>
                                        @elseif($com->tipo === 'email')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                Email
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                Visita presencial
                                            </span>
                                        @endif

                                        <span class="text-xs text-slate-500 font-medium">
                                            {{ $com->fecha ? $com->fecha->format('d/m/Y H:i') : $com->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        <span class="text-xs text-slate-400">•</span>
                                        <span class="text-xs text-slate-600 font-medium">
                                            Por: <strong class="text-slate-800">{{ $com->autor_nombre }}</strong>
                                        </span>
                                    </div>

                                    @if($puedeGestionar)
                                    <div class="flex items-center gap-1">
                                        <button type="button"
                                                @click="abrirEdicion({{ json_encode(['id' => $com->id, 'tipo' => $com->tipo, 'motivo' => $com->motivo, 'respuesta' => $com->respuesta]) }})"
                                                class="p-1 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="Editar texto de la comunicación">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('empresas.comunicaciones.destroy', [$empresa, $com]) }}"
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este registro de comunicación?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                    title="Eliminar registro">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-800 leading-relaxed">
                                    <div class="font-medium text-slate-900 whitespace-pre-line">{{ $com->motivo }}</div>
                                    @if($com->respuesta)
                                        <div class="mt-2 p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-700">
                                            <span class="font-bold text-slate-900 block mb-0.5">Respuesta obtenida:</span>
                                            <span class="whitespace-pre-line">{{ $com->respuesta }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                        No hay registros de comunicación en el curso actual.
                    </p>
                @endif
            </div>

            <!-- Comunicaciones de Cursos Pasados (Colapsadas) -->
            @if($comunicacionesPasadas->isNotEmpty())
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Comunicaciones de Cursos Anteriores
                    </h4>

                    @foreach($comunicacionesPasadas as $nombreCurso => $commsPasadas)
                        <div x-data="{ colapsado: true }" class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50">
                            <button type="button" @click="colapsado = !colapsado" class="w-full p-4 flex items-center justify-between text-left hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="font-bold text-sm text-slate-800">Curso {{ $nombreCurso }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200/80 text-slate-600 font-medium">
                                        {{ $commsPasadas->count() }} {{ $commsPasadas->count() === 1 ? 'comunicación' : 'comunicaciones' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                                    <span x-text="colapsado ? 'Ver comunicaciones previas' : 'Ocultar'">Ver comunicaciones previas</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="colapsado ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <div x-show="!colapsado" style="display: none;" class="p-4 pt-2 space-y-3 border-t border-slate-200/70 bg-white">
                                @foreach($commsPasadas as $com)
                                    @php
                                        $puedeGestionar = $canManageComunicaciones || (auth()->user()->profesor && $com->profesor_id === auth()->user()->profesor->id);
                                    @endphp
                                    <div class="p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/40 space-y-2">
                                        <div class="flex items-start justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                                @if($com->tipo === 'telefono')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Teléfono
                                                    </span>
                                                @elseif($com->tipo === 'email')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        Email
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Visita presencial
                                                    </span>
                                                @endif
                                                <span class="text-slate-500">
                                                    {{ $com->fecha ? $com->fecha->format('d/m/Y H:i') : $com->created_at->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-slate-400">•</span>
                                                <span class="text-slate-600">
                                                    Por: <strong class="text-slate-800">{{ $com->autor_nombre }}</strong>
                                                </span>
                                            </div>

                                            @if($puedeGestionar)
                                            <div class="flex items-center gap-1">
                                                <button type="button"
                                                        @click="abrirEdicion({{ json_encode(['id' => $com->id, 'tipo' => $com->tipo, 'motivo' => $com->motivo, 'respuesta' => $com->respuesta]) }})"
                                                        class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                        title="Editar registro">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form method="POST" action="{{ route('empresas.comunicaciones.destroy', [$empresa, $com]) }}"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este registro de comunicación?');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                            title="Eliminar registro">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-800 leading-relaxed">
                                            <div class="font-medium text-slate-900 whitespace-pre-line">{{ $com->motivo }}</div>
                                            @if($com->respuesta)
                                                <div class="mt-1.5 p-2 rounded bg-white border border-slate-200/80 text-slate-700">
                                                    <span class="font-bold text-slate-900 block mb-0.5">Respuesta:</span>
                                                    <span class="whitespace-pre-line">{{ $com->respuesta }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Modal de Edición de Comunicación -->
            <div x-show="editModalOpen" style="display: none;"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                 x-transition>
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-100"
                     @click.away="editModalOpen = false">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="text-base font-bold font-display text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Editar Registro de Comunicación
                        </h4>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="editForm.actionUrl" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="stitch-label block mb-1.5 font-bold text-xs text-slate-700">Canal de Contacto *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                    <input type="radio" name="tipo" value="telefono" x-model="editForm.tipo" class="text-blue-600 focus:ring-blue-500">
                                    <span>Teléfono</span>
                                </label>
                                <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                    <input type="radio" name="tipo" value="email" x-model="editForm.tipo" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Email</span>
                                </label>
                                <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                    <input type="radio" name="tipo" value="presencial" x-model="editForm.tipo" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>Presencial</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Motivo de la Comunicación *</label>
                            <textarea name="motivo" required rows="3" x-model="editForm.motivo" class="stitch-input bg-white text-xs w-full"></textarea>
                        </div>

                        <div>
                            <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Respuesta Obtenida (opcional)</label>
                            <textarea name="respuesta" rows="3" x-model="editForm.respuesta" class="stitch-input bg-white text-xs w-full"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="stitch-btn-secondary text-xs py-2 px-3">Cancelar</button>
                            <button type="submit" class="stitch-btn-primary text-xs py-2 px-4">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ofertas y Prácticas por Curso -->
        @forelse($bloques as $bloque)
        <div class="stitch-card p-6 sm:p-8 space-y-6" x-data="{ abierto: {{ $bloque->es_actual ? 'true' : 'false' }} }">
            <button type="button" @click="abierto = !abierto" class="w-full flex items-center justify-between text-left group">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-bold font-display text-slate-900 group-hover:text-[#0048FE] transition-colors">
                        {{ $bloque->es_actual ? 'Ofertas y Prácticas — Curso Actual' : 'Ofertas y Prácticas — Curso Anterior' }}
                        @if($bloque->curso)
                            <span class="text-slate-500 font-normal text-sm">({{ $bloque->curso->nombre }})</span>
                        @endif
                    </h3>
                    @if($bloque->es_actual)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#0048FE] border border-blue-200">
                            Curso Activo
                        </span>
                    @endif
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="abierto" @if(!$bloque->es_actual) style="display: none;" @endif class="space-y-6 pt-4 border-t border-slate-100">
                <!-- Ofertas -->
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">Ofertas de Prácticas ({{ $bloque->ofertas->count() }})</h4>
                    @if($bloque->ofertas->count() > 0)
                        <div class="space-y-2">
                            @foreach($bloque->ofertas as $oferta)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ $oferta->especialidad_requerida }}</span>
                                    <span class="text-xs text-slate-500">Vacantes: {{ $oferta->num_alumnos }} alumnos</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if(in_array($oferta->estado, ['abierta', 'activa']))
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Abierta</span>
                                    @elseif($oferta->estado === 'pendiente')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">Pendiente</span>
                                    @elseif($oferta->estado === 'solicitada')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Solicitada</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Cerrada</span>
                                    @endif
                                    <a href="{{ route('ofertas.show', $oferta) }}" class="text-xs font-semibold text-[#0048FE] hover:underline">Ver detalle →</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500">No hay ofertas de prácticas registradas para este curso.</p>
                    @endif
                </div>

                <!-- Prácticas -->
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">Alumnos en Prácticas ({{ $bloque->practicas->count() }})</h4>
                    @if($bloque->practicas->count() > 0)
                        <div class="space-y-2">
                            @foreach($bloque->practicas as $practica)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ $practica->alumno->user->name ?? '—' }}</span>
                                    <span class="text-xs text-slate-500">Periodo: {{ $practica->fecha_inicio?->format('d/m/Y') }} al {{ $practica->fecha_fin?->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium text-slate-600">{{ $practica->horas_acumuladas }}h acumuladas</span>
                                    @if($practica->convenio_firmado)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Convenio Firmado</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Sin Firma</span>
                                    @endif
                                    <a href="{{ route('practicas.show', $practica) }}" class="text-xs font-semibold text-[#0048FE] hover:underline">Ver práctica →</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500">No hay prácticas registradas con esta empresa este curso.</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="stitch-card p-6 sm:p-8 text-center text-slate-500 text-sm">
            Esta empresa no tiene ofertas ni prácticas registradas todavía.
        </div>
        @endforelse
    </div>
</x-app-layout>
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
                <div>
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
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block mb-1">Teléfono</span>
                    <span class="text-slate-900 font-medium">{{ $empresa->telefono ?? '—' }}</span>
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
            </div>
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
                                    @if($oferta->estado === 'activa')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Activa</span>
                                    @elseif($oferta->estado === 'pendiente')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pendiente</span>
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
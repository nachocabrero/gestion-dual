<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Perfil de Alumno</h2>
            <div class="flex items-center gap-2">
                @if($alumno->user->cv_url)
                <a href="{{ asset('storage/' . $alumno->user->cv_url) }}" target="_blank" download class="hlanz-btn-secondary !text-rose-600 !border-rose-200 hover:!bg-rose-50 inline-flex items-center gap-1.5 font-semibold text-xs py-2 px-3" title="Descargar Currículum Vitae">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Descargar CV</span>
                </a>
                @endif
                @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]))
                <a href="{{ route('anotaciones.show', $alumno->id) }}" class="hlanz-btn-secondary inline-flex items-center gap-1.5 text-xs py-2 px-3" title="Ver Anotaciones Tutoriales">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Anotaciones</span>
                </a>
                @endif
                @can('update-alumno', $alumno)
                <a href="{{ route('alumnos.edit', $alumno) }}" class="hlanz-btn-secondary text-xs py-2 px-3">Editar</a>
                @endcan
                @if(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO))
                <a href="{{ route('dashboard') }}" class="hlanz-btn-secondary text-xs py-2 px-3">Volver al panel</a>
                @else
                <a href="{{ route('alumnos.index') }}" class="hlanz-btn-secondary text-xs py-2 px-3">Volver</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            <!-- Datos personales -->
            <div class="hlanz-card p-6">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    @if($alumno->user->avatar_url)
                        <img src="{{ asset('storage/' . $alumno->user->avatar_url) }}" alt="{{ $alumno->user->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-md shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#0048FE] to-cyan-500 text-white flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                            {{ strtoupper(substr($alumno->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold font-display text-slate-900 truncate">{{ $alumno->user->name }}</h3>
                        <p class="text-sm text-slate-500 truncate">{{ $alumno->user->email }}</p>
                        @if($alumno->user->cv_url)
                            <a href="{{ asset('storage/' . $alumno->user->cv_url) }}" target="_blank" download class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-rose-600 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Descargar / Ver CV adjunto</span>
                            </a>
                        @endif
                    </div>
                </div>

                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Información de Contacto y Enlaces</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Teléfono</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->telefono ?? $alumno->user->telefono ?? 'No indicado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Fecha Nacimiento</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? 'No indicada' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Domicilio</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->domicilio ?? 'No indicado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">LinkedIn</span>
                        <p class="font-semibold text-slate-900 mt-0.5 truncate">
                            @php $linkedin = $alumno->linkedin_url ?? $alumno->user->linkedin_url; @endphp
                            @if($linkedin)
                            <a href="{{ $linkedin }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                <span>{{ $linkedin }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @else
                            No indicado
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">GitHub</span>
                        <p class="font-semibold text-slate-900 mt-0.5 truncate">
                            @if($alumno->user->github_url)
                            <a href="{{ $alumno->user->github_url }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                <span>{{ $alumno->user->github_url }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @else
                            No indicado
                            @endif
                        </p>
                    </div>
                    @php 
                        $portfolio = trim($alumno->portfolio_url ?? $alumno->user->portfolio_url ?? ''); 
                    @endphp
                    @if($portfolio && $portfolio !== 'https://' && $portfolio !== 'http://')
                    <div class="sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Portfolio Externo / Web Personal</span>
                        <p class="font-semibold text-slate-900 mt-0.5">
                            <a href="{{ $portfolio }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1.5 font-bold">
                                <span>🌐 {{ $portfolio }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Datos académicos -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Datos Académicos</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Grupos a los que pertenece</span>
                        @if($alumno->grupos->count() > 0)
                            <ul class="list-disc pl-5 mt-2 font-semibold text-slate-900 space-y-1">
                                @foreach($alumno->grupos as $grupo)
                                    <li>
                                        <span>{{ $grupo->nombre }}</span>
                                        @if($grupo->cursoAcademico)<span class="text-slate-500 font-normal">({{ $grupo->cursoAcademico->nombre }})</span>@endif
                                        <span class="text-slate-500 font-normal">— Tutor/a: {{ $grupo->tutor->name ?? 'No asignado' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="font-semibold text-slate-900 mt-1">Sin grupo</p>
                        @endif
                    </div>

                    @if($alumno->ciclosMatriculados && $alumno->ciclosMatriculados->count() > 0)
                    <div class="col-span-2 pt-2 border-t border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ciclos Formativos Matriculados</span>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($alumno->ciclosMatriculados as $ciclo)
                            <span class="stitch-badge-info text-xs inline-flex items-center gap-1">
                                <span class="font-bold">{{ $ciclo->codigo }}</span>
                                <span class="text-blue-700">· {{ $ciclo->nombre }}</span>
                                @if($ciclo->pivot->curso_academico)
                                <span class="text-blue-500">({{ $ciclo->pivot->curso_academico }})</span>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tutor de prácticas</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->tutorPracticas->name ?? 'No asignado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado</span>
                        <p class="font-semibold mt-0.5">
                            @if($alumno->user->isPendingApproval())
                            <span class="stitch-badge-warning inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                En espera (Aprobación pendiente)
                            </span>
                            @elseif($alumno->user->isActive())
                            <span class="stitch-badge-success">Activo</span>
                            @else
                            <span class="stitch-badge-danger">Desactivado</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]))
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Observaciones y anotaciones de seguimiento</span>
                    <a href="{{ route('anotaciones.show', $alumno->id) }}" class="text-xs font-bold text-[#0048FE] hover:underline inline-flex items-center gap-1">
                        <span>Ver observaciones del alumno</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif
            </div>

            <!-- Prácticas de 1º curso y situación académica -->
            @if($alumno->empresa_practicas_primero || $alumno->horas_practicas_primero || $alumno->sin_practicas_primero || $alumno->repitiendo_modulos_primero || $alumno->esSegundoCurso())
            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Prácticas de 1º Curso y Situación Académica
                    </h3>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($alumno->repitiendo_modulos_primero)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Repitiendo módulos de 1º (Sin prácticas)
                            </span>
                        @endif
                        @if($alumno->sin_practicas_primero)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                0h anteriores (Convalidación 1º)
                            </span>
                        @endif
                        @if($alumno->esSegundoCurso())
                            <span class="stitch-badge-info text-xs">Alumno/a de 2º Curso</span>
                        @endif
                    </div>
                </div>

                @if($alumno->repitiendo_modulos_primero)
                <div class="mb-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>No elegible para prácticas este año:</strong> El alumno cursa/repite módulos de 1º curso pendientes. La normativa impide su incorporación a prácticas en empresas durante el presente curso escolar.</span>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Empresa donde realizó prácticas en 1º</span>
                        <p class="font-bold text-slate-900 text-base">
                            @if($alumno->sin_practicas_primero)
                                <span class="text-slate-500 font-medium">Ninguna (1er curso convalidado / sin prácticas)</span>
                            @else
                                {{ $alumno->empresa_practicas_primero ?: 'No indicada' }}
                            @endif
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Horas realizadas en 1º curso</span>
                        <p class="font-bold text-slate-900 text-base">
                            @if($alumno->sin_practicas_primero)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 font-semibold text-sm">
                                    0 horas (Sin prácticas en 1º)
                                </span>
                            @elseif($alumno->horas_practicas_primero !== null)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 text-[#0048FE] border border-blue-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $alumno->horas_practicas_primero }} horas computables
                                </span>
                            @else
                                <span class="text-slate-500">No indicadas</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Prácticas y FCT asignadas -->
            @if($alumno->practicas && $alumno->practicas->count() > 0)
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Prácticas / FCT en Curso
                </h3>

                <div class="space-y-3">
                    @foreach($alumno->practicas as $practica)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $practica->empresa->nombre ?? 'Empresa no asignada' }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if($practica->fecha_inicio)
                                    Desde {{ $practica->fecha_inicio->format('d/m/Y') }}
                                    @if($practica->fecha_fin) hasta {{ $practica->fecha_fin->format('d/m/Y') }} @endif
                                @endif
                                @if($practica->tutorLaboral)
                                    · Tutor laboral: {{ $practica->tutorLaboral->nombre }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="stitch-badge-neutral text-xs font-semibold">
                                {{ $practica->horas_acumuladas }}h acumuladas
                            </span>
                            @if($practica->convenio_firmado)
                            <span class="stitch-badge-success text-xs">Convenio firmado</span>
                            @else
                            <span class="stitch-badge-warning text-xs">Pendiente firma</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Archivos y Documentación del Alumno -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Archivos y Documentación Subida
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Currículum Vitae -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm">Currículum Vitae (CV)</h4>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ $alumno->user->cv_url ? basename($alumno->user->cv_url) : 'No se ha subido ningún archivo' }}
                                </p>
                            </div>
                        </div>
                        @if($alumno->user->cv_url)
                        <a href="{{ asset('storage/' . $alumno->user->cv_url) }}" target="_blank" download class="stitch-btn-secondary !text-rose-600 !border-rose-200 hover:!bg-rose-50 text-xs py-1.5 px-3 shrink-0 inline-flex items-center gap-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Descargar</span>
                        </a>
                        @else
                        <span class="stitch-badge-neutral text-xs shrink-0">No disponible</span>
                        @endif
                    </div>

                    <!-- Foto de Perfil / Avatar -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($alumno->user->avatar_url)
                                <img src="{{ asset('storage/' . $alumno->user->avatar_url) }}" alt="Avatar" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0048FE] flex items-center justify-center shrink-0 border border-blue-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm">Foto de Perfil</h4>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ $alumno->user->avatar_url ? basename($alumno->user->avatar_url) : 'Avatar por iniciales' }}
                                </p>
                            </div>
                        </div>
                        @if($alumno->user->avatar_url)
                        <a href="{{ asset('storage/' . $alumno->user->avatar_url) }}" target="_blank" class="stitch-btn-secondary text-xs py-1.5 px-3 shrink-0 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Ver imagen</span>
                        </a>
                        @else
                        <span class="stitch-badge-neutral text-xs shrink-0">Por defecto</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Proyectos del Alumno y Archivos/Capturas Asociadas -->
            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Proyectos y Evidencias Digitales
                    </h3>
                    <span class="stitch-badge-neutral text-xs">{{ $alumno->proyectos ? $alumno->proyectos->count() : 0 }} proyectos</span>
                </div>

                @if($alumno->proyectos && $alumno->proyectos->count() > 0)
                <div class="space-y-6">
                    @foreach($alumno->proyectos as $proyecto)
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-base font-bold text-slate-900">{{ $proyecto->titulo }}</h4>
                                    @if($proyecto->es_destacado)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">⭐ Destacado</span>
                                    @endif
                                    @if($proyecto->estaCalificado())
                                        <span class="stitch-badge-success text-xs font-bold">{{ $proyecto->calificacion }}/10</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    Ciclo: <span class="font-semibold text-slate-700">{{ $proyecto->ciclo->nombre ?? 'N/A' }}</span>
                                    @if($proyecto->cursoAcademico) · Curso: <span class="font-semibold text-slate-700">{{ $proyecto->cursoAcademico->nombre }}</span> @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                @if($proyecto->enlace_repositorio)
                                <a href="{{ $proyecto->enlace_repositorio }}" target="_blank" class="stitch-btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-1 font-medium" title="Ver repositorio de código">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                    <span>Repo</span>
                                </a>
                                @endif
                                @if($proyecto->enlace_despliegue)
                                <a href="{{ $proyecto->enlace_despliegue }}" target="_blank" class="stitch-btn-secondary !text-emerald-700 !border-emerald-200 hover:!bg-emerald-50 text-xs py-1.5 px-3 inline-flex items-center gap-1 font-medium" title="Ver demostración desplegada">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Demo</span>
                                </a>
                                @endif
                                <a href="{{ route('proyectos.show', $proyecto) }}" class="stitch-btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-1">
                                    <span>Ver Detalle</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        <p class="text-sm text-slate-700 line-clamp-3 whitespace-pre-line">{{ $proyecto->descripcion }}</p>

                        <!-- Archivos e Imágenes subidas del proyecto -->
                        @if($proyecto->imagenes && $proyecto->imagenes->count() > 0)
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Archivos y Capturas Subidas ({{ $proyecto->imagenes->count() }})</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($proyecto->imagenes as $img)
                                <div class="group relative rounded-xl overflow-hidden border border-slate-200 bg-white aspect-video flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $img->url) }}" alt="Captura de {{ $proyecto->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <a href="{{ asset('storage/' . $img->url) }}" target="_blank" class="p-1.5 rounded-lg bg-white/90 text-slate-800 hover:bg-white transition-colors" title="Ver en tamaño completo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ asset('storage/' . $img->url) }}" download class="p-1.5 rounded-lg bg-white/90 text-slate-800 hover:bg-white transition-colors" title="Descargar archivo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="text-sm">Este alumno aún no tiene proyectos ni archivos de proyectos asociados.</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Perfil de Alumno</h2>
            <div class="flex items-center gap-2">
                @if($alumno->user->cv_url)
                <a href="{{ asset('storage/' . $alumno->user->cv_url) }}" target="_blank" download class="hlanz-btn-secondary !text-rose-600 !border-rose-200 hover:!bg-rose-50 inline-flex items-center gap-1.5 font-semibold text-xs py-2 px-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Descargar CV</span>
                </a>
                @endif
                @can('update-alumno', $alumno)
                <a href="{{ route('alumnos.edit', $alumno) }}" class="hlanz-btn-secondary">Editar</a>
                @endcan
                <a href="{{ route('alumnos.index') }}" class="hlanz-btn-secondary">Volver</a>
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

            <!-- Prácticas de 1º curso -->
            @if($alumno->empresa_practicas_primero || $alumno->horas_practicas_primero || $alumno->esSegundoCurso())
            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Prácticas de 1º Curso
                    </h3>
                    @if($alumno->esSegundoCurso())
                        <span class="stitch-badge-info text-xs">Alumno/a de 2º Curso</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Empresa donde realizó prácticas en 1º</span>
                        <p class="font-bold text-slate-900 text-base">
                            {{ $alumno->empresa_practicas_primero ?: 'No indicada' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Horas realizadas en 1º curso</span>
                        <p class="font-bold text-slate-900 text-base">
                            @if($alumno->horas_practicas_primero !== null)
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

        </div>
    </div>
</x-app-layout>

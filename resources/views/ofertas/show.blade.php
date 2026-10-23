@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center w-full">
        <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
            {{ __('Detalle de Oferta') }}
        </h2>
        <a href="{{ route('ofertas.index') }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a ofertas
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-300 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @php
            $statusColors = [
                'abierta' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                'activa' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/60 dark:text-yellow-300',
                'solicitada' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                'cerrada' => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
            ];
            $color = $statusColors[$oferta->estado] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
        @endphp

        <!-- Cabecera de la oferta -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $oferta->especialidad_requerida }}
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst($oferta->estado) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-3xl">
                            {{ $oferta->descripcion ?? 'Sin descripción' }}
                        </p>

                        @if($oferta->grupos->count() > 0)
                        <div class="mt-4 flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dirigida a:</span>
                            @foreach($oferta->grupos as $grupo)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                    {{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        @if(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO) && in_array($oferta->estado, ['abierta', 'activa']))
                            @if(empty($miSolicitud))
                                <form method="POST" action="{{ route('ofertas.postularse', $oferta) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Solicitar esta plaza</span>
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Solicitud registrada ({{ ucfirst($miSolicitud->estado) }})</span>
                                </span>
                            @endif
                        @endif

                        @if($thisCanEnviar && $oferta->estado !== 'cerrada')
                            <a href="{{ route('ofertas.enviar-form', $oferta) }}"
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#0048FE] text-white text-sm font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>Asignar alumnos</span>
                            </a>
                        @endif

                        @if($thisCanEdit)
                            <a href="{{ route('ofertas.edit', $oferta) }}"
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold hover:border-[#0048FE] hover:text-[#0048FE] dark:hover:text-blue-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Editar oferta
                            </a>
                        @endif
                    </div>
                </div>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Empresa</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $oferta->empresa?->nombre ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nº de alumnos</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $oferta->num_alumnos }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Creada por</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $oferta->creador?->name ?? $oferta->creador?->nombre ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fechas</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            @if($oferta->created_at)
                                Creada el {{ $oferta->created_at->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        @if(($thisCanEnviar || $thisCanEdit) && $oferta->token)
        <!-- Enlace Seguro para la Empresa -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6" x-data="{ copiado: false }">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-[#0048FE] dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                            Compartir oferta con la empresa (sin usuario registrado)
                        </h3>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Puedes proporcionar este enlace seguro a la empresa colaboradora para que consulten los candidatos y descarguen sus CVs directamente.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <div class="relative flex items-center">
                        <input type="text" readonly value="{{ route('ofertas.compartida', $oferta->token) }}"
                               class="text-xs px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-300 w-64 sm:w-80 select-all font-mono">
                    </div>

                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ route('ofertas.compartida', $oferta->token) }}'); copiado = true; setTimeout(() => copiado = false, 2500)"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-[#0048FE] text-white hover:bg-blue-700 transition-colors shadow-sm">
                        <svg x-show="!copiado" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <svg x-show="copiado" class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="copiado ? '¡Enlace copiado!' : 'Copiar enlace'"></span>
                    </button>

                    <a href="{{ route('ofertas.compartida', $oferta->token) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                       title="Abrir la vista que verá la empresa">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Ver como empresa</span>
                    </a>

                    <form method="POST" action="{{ route('ofertas.regenerar-token', $oferta) }}" class="inline"
                          onsubmit="return confirm('¿Regenerar el enlace de la empresa? El enlace actual dejará de funcionar inmediatamente.')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-2.5 py-2 rounded-lg text-xs font-medium text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                title="Revocar el enlace actual y generar uno nuevo">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Regenerar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO) && $miSolicitud)
        <!-- Estado de la propia solicitud del alumno -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">
                Estado de tu Solicitud
            </h3>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        @if($miSolicitud->estado === 'aceptado') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300
                        @elseif($miSolicitud->estado === 'rechazado' || $miSolicitud->estado === 'retirado') bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300
                        @else bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 @endif">
                        Estado: {{ ucfirst($miSolicitud->estado) }}
                    </span>
                    @if($miSolicitud->motivo_rechazo)
                        <p class="text-xs text-rose-600 dark:text-rose-400 mt-2">Motivo: {{ $miSolicitud->motivo_rechazo }}</p>
                    @endif
                </div>

                @if(in_array($miSolicitud->estado, ['pendiente', 'aceptado']) && in_array($oferta->estado, ['abierta', 'activa']))
                    <form method="POST" action="{{ route('ofertas.solicitudes.retirar', $miSolicitud) }}" onsubmit="return confirm('¿Seguro que deseas retirar tu solicitud?')">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                            Retirar mi solicitud
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endif

        @if(!auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO))
        <!-- Solicitudes (visible exclusivamente para profesorado, coordinadores y administración) -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Alumnado Asignado y Solicitudes
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $oferta->solicitudes->count() }}
                    </span>
                    @if($thisCanDescargarCvs && $totalCvsDisponibles > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                            <svg class="w-3 h-3 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ $totalCvsDisponibles }} {{ $totalCvsDisponibles === 1 ? 'CV disponible' : 'CVs disponibles' }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if($thisCanDescargarCvs && $oferta->solicitudes->count() > 0)
                        @if($totalCvsDisponibles > 0)
                            <a href="{{ route('ofertas.descargar-cvs', $oferta) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-600/30 transition-all"
                               title="Descargar todos los currículums de esta oferta en un archivo comprimido .ZIP">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar todos los CVs (ZIP)</span>
                            </a>
                        @else
                            <button type="button" disabled
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700/50 cursor-not-allowed"
                                    title="Ningún alumno ha subido su currículum aún">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar todos los CVs (ZIP)</span>
                            </button>
                        @endif
                    @endif

                    @if($thisCanEnviar && $oferta->estado !== 'cerrada')
                    <a href="{{ route('ofertas.enviar-form', $oferta) }}" class="text-xs font-bold text-[#0048FE] hover:underline inline-flex items-center gap-1">
                        <span>Gestionar asignaciones →</span>
                    </a>
                    @endif
                </div>
            </div>

            @if($oferta->solicitudes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Alumno</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</th>
                            @if($thisCanDescargarCvs)
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Currículum (CV)</th>
                            @endif
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($oferta->solicitudes as $solicitud)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 shrink-0">
                                        @if($solicitud->alumno?->user?->avatar_url)
                                            <img src="{{ asset('storage/' . $solicitud->alumno->user->avatar_url) }}" alt="{{ $solicitud->alumno->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($solicitud->alumno?->user?->name ?? 'A', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('alumnos.show', $solicitud->alumno) }}" class="font-semibold text-gray-900 dark:text-white hover:text-[#0048FE] hover:underline block truncate">
                                            {{ $solicitud->alumno?->user?->name ?? '—' }}
                                        </a>
                                        @if($solicitud->alumno?->user?->email)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block truncate">{{ $solicitud->alumno->user->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @php
                                $practicaAsignada = $oferta->practicas->firstWhere('alumno_id', $solicitud->alumno_id)
                                    ?? $solicitud->alumno?->practicas->where('empresa_id', $oferta->empresa_id)->first();
                                $esAsignado = !is_null($practicaAsignada);
                            @endphp
                            <td class="px-6 py-4">
                                @if($esAsignado)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Asignado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            @if($thisCanDescargarCvs)
                            <td class="px-6 py-4 text-center">
                                @php
                                    $hasCv = $solicitud->alumno?->user?->cv_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($solicitud->alumno->user->cv_url);
                                @endphp
                                @if($hasCv)
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <a href="{{ route('ofertas.descargar-cv-alumno', [$oferta, $solicitud->alumno]) }}"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 dark:text-rose-300 transition-colors"
                                            title="Descargar CV de {{ $solicitud->alumno->user->name }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Descargar CV</span>
                                        </a>
                                        <a href="{{ asset('storage/' . $solicitud->alumno->user->cv_url) }}" target="_blank"
                                            class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                                            title="Ver / Abrir en nueva pestaña">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 italic">
                                        <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Sin CV subido
                                    </span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($esAsignado)
                                        <a href="{{ route('practicas.show', $practicaAsignada) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Ver práctica</span>
                                        </a>
                                    @else
                                        @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]))
                                            <a href="{{ route('practicas.create', ['oferta_id' => $oferta->id, 'alumno_id' => $solicitud->alumno_id, 'empresa_id' => $oferta->empresa_id]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-lg bg-[#0048FE] text-white hover:bg-blue-700 shadow-sm transition-colors"
                                               title="Crear nueva práctica para este alumno en esta empresa">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>Crear práctica</span>
                                            </a>
                                        @endif
                                    @endif

                                    @if($thisCanEnviar)
                                        <form method="POST" action="{{ route('ofertas.solicitudes.rechazar', $solicitud) }}" class="inline" onsubmit="return confirm('¿Desasignar a este alumno de la oferta?')">
                                            @csrf
                                            <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold ml-1">Desasignar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg">Todavía no hay solicitudes para esta oferta.</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
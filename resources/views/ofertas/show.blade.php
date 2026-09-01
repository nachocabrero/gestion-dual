@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle de Oferta') }}
        </h2>
        <a href="{{ route('ofertas.index') }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a ofertas
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @php
            $statusColors = [
                'activa' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                'cerrada' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            ];
            $color = $statusColors[$oferta->estado] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
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
                        @if($thisCanEnviar && $oferta->estado !== 'cerrada')
                            <a href="{{ route('ofertas.enviar-form', $oferta) }}"
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#0048FE] text-white text-sm font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                {{ $oferta->estado === 'activa' ? 'Volver a enviar a alumnos' : 'Enviar a alumnos' }}
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

        <!-- Solicitudes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Solicitudes
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $oferta->solicitudes->count() }}
                    </span>
                </h3>
            </div>

            @if($oferta->solicitudes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Alumno</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($oferta->solicitudes as $solicitud)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $solicitud->alumno->nombre }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $solicitudColors = [
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                        'aceptado' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        'rechazado' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                    ];
                                    $sColor = $solicitudColors[$solicitud->estado] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sColor }}">
                                    {{ ucfirst($solicitud->estado) }}
                                </span>
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
    </div>
</div>
@endsection
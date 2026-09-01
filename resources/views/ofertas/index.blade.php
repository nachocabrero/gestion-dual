@extends('layouts.app')

@section('content')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ofertas de Prácticas') }}
        </h2>
        @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN]))
            <a href="{{ route('ofertas.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                + Crear Oferta
            </a>
        @endif
    </div>
@endsection

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filtros -->
        <form method="GET" class="mb-6 flex gap-3">
            <input type="text" name="search" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Buscar..." value="{{ request('search') }}">
            <select name="especialidad" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp }}" {{ request('especialidad') == $esp ? 'selected' : '' }}>{{ $esp }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($ofertas as $oferta)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $oferta->especialidad_requerida }}
                        </h3>
                        @php
                            $statusColors = [
                                'activa' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            ];
                            $color = $statusColors[$oferta->estado] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                            {{ ucfirst($oferta->estado) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $oferta->descripcion ?? 'Sin descripción' }}
                    </p>
                    @if($oferta->grupos->count() > 0)
                    <div class="mb-4 flex flex-wrap gap-1">
                        @foreach($oferta->grupos as $grupo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                {{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}
                            </span>
                        @endforeach
                    </div>
                    @endif
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('ofertas.show', $oferta) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg">No hay ofertas publicadas.</p>
            </div>
            @endforelse
        </div>

        @if(method_exists($ofertas, 'links'))
            <div class="mt-6">
                {{ $ofertas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
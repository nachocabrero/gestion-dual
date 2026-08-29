@extends('layouts.app')

@section('content')

    @section('header')
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mis Proyectos') }}
            </h2>
            @if(auth()->user()->hasRole('alumno'))
                <a href="{{ route('proyectos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    + Nuevo Proyecto
                </a>
            @endif
        </div>
    @endsection

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(auth()->user()->hasAnyRole(['admin', 'profesor']))
                <form method="GET" action="{{ route('proyectos.index') }}" class="mb-6 flex gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <select name="ciclo_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos los ciclos</option>
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}" {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
                </form>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($proyectos as $proyecto)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $proyecto->titulo }}
                                </h3>
                                @if($proyecto->es_destacado)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⭐ Destacado
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                                {{ Str::limit($proyecto->descripcion, 150) }}
                            </p>

                            @if($proyecto->imagenes->count() > 0)
                                <div class="mb-3">
                                    <img src="{{ Storage::url($proyecto->imagenes->first()->url) }}" alt="{{ $proyecto->titulo }}" class="w-full h-32 object-cover rounded-md">
                                </div>
                            @endif

                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3 space-y-1">
                                @if($proyecto->enlace_repositorio)
                                    <a href="{{ $proyecto->enlace_repositorio }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        📦 Repositorio
                                    </a>
                                @endif
                                @if($proyecto->enlace_despliegue)
                                    <a href="{{ $proyecto->enlace_despliegue }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        🚀 Despliegue
                                    </a>
                                @endif
                            </div>

                            @if(auth()->user()->hasAnyRole(['admin', 'profesor']))
                                <div class="text-sm mb-2">
                                    <span class="text-gray-500">Alumno:</span>
                                    <span class="font-medium">{{ $proyecto->alumno->user->name }}</span>
                                </div>
                                <div class="text-sm mb-2">
                                    <span class="text-gray-500">Ciclo:</span>
                                    <span class="font-medium">{{ $proyecto->ciclo->nombre }}</span>
                                </div>
                            @endif

                            @if(auth()->user()->hasRole('alumno') && $proyecto->estaCalificado())
                                <div class="text-sm mb-2">
                                    <span class="text-gray-500">Calificación:</span>
                                    <span class="font-bold text-lg text-green-600">{{ $proyecto->calificacion }}/10</span>
                                </div>
                            @endif

                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('proyectos.show', $proyecto) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                                    Ver detalle
                                </a>
                                @if(auth()->user()->hasRole('alumno') && !$proyecto->estaCalificado())
                                    <a href="{{ route('proyectos.edit', $proyecto) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                        Editar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        <p class="text-lg">No hay proyectos aún.</p>
                        @if(auth()->user()->hasRole('alumno'))
                            <p class="mt-2">¡Sé el primero en subir un proyecto!</p>
                        @endif
                    </div>
                @endforelse
            </div>

            @if(method_exists($proyectos, 'links'))
                <div class="mt-6">
                    {{ $proyectos->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
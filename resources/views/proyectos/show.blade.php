@extends('layouts.app')

@section('content')

    @section('header')
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle del Proyecto') }}
        </h2>
@endsection

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $proyecto->titulo }}</h3>
                        @if($proyecto->es_destacado)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                ⭐ Destacado
                            </span>
                        @endif
                    </div>

                    @if(auth()->user()->hasAnyRole(['admin', 'profesor']))
                        <div class="text-sm text-gray-500 mb-4">
                            <span>Alumno: <strong>{{ $proyecto->alumno->user->name }}</strong></span>
                            <span class="mx-2">|</span>
                            <span>Ciclo: <strong>{{ $proyecto->ciclo->nombre }}</strong></span>
                            <span class="mx-2">|</span>
                            <span>Curso: <strong>{{ $proyecto->cursoAcademico->nombre }}</strong></span>
                        </div>
                    @endif

                    <div class="prose dark:prose-invert max-w-none mb-6">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $proyecto->descripcion }}</p>
                    </div>

                    <!-- Enlaces -->
                    <div class="flex gap-4 mb-6">
                        @if($proyecto->enlace_repositorio)
                            <a href="{{ $proyecto->enlace_repositorio }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">
                                📦 Repositorio
                            </a>
                        @endif
                        @if($proyecto->enlace_despliegue)
                            <a href="{{ $proyecto->enlace_despliegue }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                🚀 Despliegue
                            </a>
                        @endif
                    </div>

                    <!-- Galería de imágenes -->
                    @if($proyecto->imagenes->count() > 0)
                        <h4 class="text-lg font-semibold mb-3">Galería</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                            @foreach($proyecto->imagenes as $img)
                                <div>
                                    <img src="{{ Storage::url($img->url) }}" alt="Proyecto" class="w-full h-40 object-cover rounded-lg cursor-pointer hover:opacity-80" onclick="window.open('{{ Storage::url($img->url) }}', '_blank')">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Calificación (solo profesor) -->
                    @if(auth()->user()->hasRole('profesor') && !$proyecto->estaCalificado())
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-lg font-semibold mb-3">Calificar Proyecto</h4>
                            <form method="POST" action="{{ route('proyectos.calificar', $proyecto) }}">
                                @csrf
                                <div class="flex gap-3 items-end">
                                    <div>
                                        <label for="calificacion" class="block text-sm font-medium text-gray-700">Calificación (1-10)</label>
                                        <input type="number" name="calificacion" id="calificacion" min="1" max="10" step="0.01" required
                                            class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="flex items-center mt-6">
                                        <input type="checkbox" name="es_destacado" id="es_destacado" value="1"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="es_destacado" class="ml-2 text-sm text-gray-700">Marcar como destacado</label>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Calificar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if(auth()->user()->hasRole('profesor') && $proyecto->estaCalificado())
                        <div class="border-t pt-4 mt-4 bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl font-bold text-green-600">{{ $proyecto->calificacion }}/10</span>
                                <span class="text-gray-600 dark:text-gray-400">Calificación</span>
                                @if($proyecto->es_destacado)
                                    <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⭐ Destacado por {{ $proyecto->destacadoPor->name ?? 'Admin' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Acciones -->
                    <div class="flex gap-3 mt-6 pt-4 border-t">
                        @if(auth()->user()->hasRole('alumno') && !$proyecto->estaCalificado())
                            <a href="{{ route('proyectos.edit', $proyecto) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Editar
                            </a>
                        @endif
                        @if(auth()->user()->hasRole('alumno') && !$proyecto->estaCalificado())
                            <form method="POST" action="{{ route('proyectos.destroy', $proyecto) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar este proyecto?')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('proyectos.edit', $proyecto) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Editar
                            </a>
                        @endif
                        <a href="{{ route('proyectos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
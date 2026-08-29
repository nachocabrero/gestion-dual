@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Título y filtros -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Portfolio de Proyectos</h2>
            <p class="text-gray-500 mt-1">Proyectos destacados del departamento de Informática</p>
        </div>
<form method="GET" action="{{ route('portfolio') }}" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Buscar por nombre..."
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select name="ciclo" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos los ciclos</option>
                @foreach($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ request('ciclo') == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->nombre }}
                    </option>
                @endforeach
            </select>
            <select name="curso" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos los cursos</option>
                @foreach($cursos as $curso)
                    <option value="{{ $curso->id }}" {{ request('curso') == $curso->id ? 'selected' : '' }}>
                        {{ $curso->nombre }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
        </form>
    </div>

    <!-- Estadísticas públicas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-2xl font-bold text-indigo-600">{{ $totalProyectos ?? 0 }}</div>
            <div class="text-sm text-gray-500">Proyectos</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalEmpresas ?? 0 }}</div>
            <div class="text-sm text-gray-500">Empresas colaboradoras</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $promedioCalificacion ?? 0 }} / 10</div>
            <div class="text-sm text-gray-500">Nota media</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $destacadosCount ?? 0 }}</div>
            <div class="text-sm text-gray-500">Destacados</div>
        </div>
    </div>

    <!-- Proyectos agrupados por ciclo -->
    @foreach($proyectosAgrupados as $cicloNombre => $cicloProyectos)
        <div class="mb-10">
            <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">{{ $cicloNombre }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cicloProyectos as $proyecto)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                        @if($proyecto->imagenes->count() > 0)
                            <img src="{{ Storage::url($proyecto->imagenes->first()->url) }}"
                                alt="{{ $proyecto->titulo }}"
                                class="w-full h-40 object-cover">
                        @endif
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $proyecto->titulo }}</h4>
                                @if($proyecto->es_destacado)
                                    <span class="text-yellow-500">⭐</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $proyecto->alumno->user->name }}</p>
                            <p class="text-sm text-gray-500 mb-3 line-clamp-3">{{ Str::limit($proyecto->descripcion, 150) }}</p>
                            <div class="flex gap-2 mb-3">
                                @if($proyecto->enlace_repositorio)
                                    <a href="{{ $proyecto->enlace_repositorio }}" target="_blank"
                                        class="text-xs text-indigo-600 hover:text-indigo-800">📦 Repo</a>
                                @endif
                                @if($proyecto->enlace_despliegue)
                                    <a href="{{ $proyecto->enlace_despliegue }}" target="_blank"
                                        class="text-xs text-indigo-600 hover:text-indigo-800">🚀 Live</a>
                                @endif
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">{{ $proyecto->cursoAcademico->nombre }}</span>
                                <span class="text-lg font-bold text-green-600">{{ $proyecto->calificacion }}/10</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($proyectosAgrupados->isEmpty())
        <div class="text-center py-16 text-gray-500">
            <p class="text-xl">No hay proyectos destacados aún.</p>
        </div>
    @endif
</div>
@endsection
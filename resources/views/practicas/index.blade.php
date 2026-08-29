<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestión de Prácticas') }}
            </h2>
            <a href="{{ route('practicas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                + Nueva Práctica
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                    @endif

                    <!-- Filtros -->
                    <form method="GET" class="mb-6 flex gap-4 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar alumno..." class="border rounded px-3 py-2 text-sm flex-1 min-w-[200px]">
                        <select name="estado" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos los estados</option>
                            <option value="en_curso" {{ request('estado') == 'en_curso' ? 'selected' : '' }}>En curso</option>
                            <option value="finalizadas" {{ request('estado') == 'finalizadas' ? 'selected' : '' }}>Finalizadas</option>
                            <option value="pendientes" {{ request('estado') == 'pendientes' ? 'selected' : '' }}>Pendientes</option>
                        </select>
                        <select name="convenio" class="border rounded px-3 py-2 text-sm">
                            <option value="">Convenio</option>
                            <option value="si" {{ request('convenio') == 'si' ? 'selected' : '' }}>Firmado</option>
                            <option value="no" {{ request('convenio') == 'no' ? 'selected' : '' }}>No firmado</option>
                        </select>
                        <select name="curso_academico_id" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos los cursos</option>
                            @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ request('curso_academico_id') == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                            @endforeach
                        </select>
                        <x-primary-button class="text-sm py-1">Filtrar</x-primary-button>
                    </form>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alumno</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fechas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Horas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Convenio</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($practicas as $practica)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $practica->alumno->user->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->empresa->nombre }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->cursoAcademico->nombre }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->fecha_inicio->format('d/m/Y') }} - {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : '...' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->horas_acumuladas }}h</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($practica->convenio_firmado)
                                        <span class="text-green-600 font-semibold">Sí</span>
                                        @else
                                        <span class="text-red-600 font-semibold">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($practica->estaEnCurso())
                                        <span class="text-blue-600 font-semibold">En curso</span>
                                        @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                                        <span class="text-gray-600">Finalizada</span>
                                        @else
                                        <span class="text-yellow-600">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2">
                                        <a href="{{ route('practicas.show', $practica) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver</a>
                                        <a href="{{ route('practicas.edit', $practica) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Editar</a>
                                        <form action="{{ route('practicas.destroy', $practica) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-800 hover:text-red-900 text-xs font-bold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">No hay prácticas registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $practicas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
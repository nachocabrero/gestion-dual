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
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alumno</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fechas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Horas</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Convenio</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
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
                                    <td class="px-4 py-2 text-sm text-center">
                                        @if($practica->convenio_firmado)
                                        <span title="Firmado" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                        @else
                                        <span title="No Firmado" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        @if($practica->estaEnCurso())
                                        <span title="En curso" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                                        <span title="Finalizada" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                        @else
                                        <span title="Pendiente" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2 text-center">
                                        <a href="{{ route('practicas.show', $practica) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Ver">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('practicas.edit', $practica) }}" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" title="Editar">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('practicas.destroy', $practica) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-800 hover:text-red-900 dark:text-red-600 dark:hover:text-red-500" title="Eliminar">
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No hay prácticas registradas.</td>
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
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Profesorado') }}
            </h2>
            @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <a href="{{ route('profesores.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                + Nuevo Profesor
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('profesores.index') }}" class="mb-4 flex gap-2 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="border rounded px-3 py-1 text-sm flex-1 min-w-[200px]">
                        <select name="es_tutor" class="border rounded px-3 py-1 text-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('es_tutor') == '1' ? 'selected' : '' }}>Tutores</option>
                            <option value="0" {{ request('es_tutor') == '0' ? 'selected' : '' }}>No tutores</option>
                        </select>
                        <select name="es_coordinador" class="border rounded px-3 py-1 text-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('es_coordinador') == '1' ? 'selected' : '' }}>Coordinadores</option>
                            <option value="0" {{ request('es_coordinador') == '0' ? 'selected' : '' }}>No coordinadores</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded text-sm hover:bg-blue-700">Filtrar</button>
                    </form>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Especialidad</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($profesores as $profesor)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $profesor->user->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $profesor->user->email }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $profesor->especialidad ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($profesor->es_tutor)
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded text-xs">Tutor</span>
                                        @endif
                                        @if($profesor->es_coordinador_dual)
                                        <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-xs">Coordinador</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($profesor->user->is_active)
                                        <span class="text-green-600 font-semibold">Activo</span>
                                        @else
                                        <span class="text-red-600 font-semibold">Desactivado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2">
                                        <a href="{{ route('profesores.show', $profesor) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver</a>
                                        <a href="{{ route('profesores.edit', $profesor) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Editar</a>
                                        @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                                        @if($profesor->user->is_active)
                                        <form action="{{ route('profesores.deactivate', $profesor) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Desactivar</button>
                                        </form>
                                        @endif
                                        <form action="{{ route('profesores.destroy', $profesor) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-800 hover:text-red-900 text-xs font-bold">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No hay profesores.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $profesores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
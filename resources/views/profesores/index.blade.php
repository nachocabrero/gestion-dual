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
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Especialidad</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
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
                                        <span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded text-xs">Tutor</span>
                                        @endif
                                        @if($profesor->es_coordinador_dual)
                                        <span class="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 px-2 py-0.5 rounded text-xs">Coordinador</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        @if($profesor->user->is_active)
                                        <span title="Activo" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                        @else
                                        <span title="Desactivado" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2 text-center">
                                        <a href="{{ route('profesores.show', $profesor) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Ver">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('profesores.edit', $profesor) }}" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" title="Editar">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                                        @if($profesor->user->is_active)
                                        <form action="{{ route('profesores.deactivate', $profesor) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Desactivar">
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('profesores.reactivate', $profesor) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" title="Reactivar">
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('profesores.destroy', $profesor) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-800 hover:text-red-900 dark:text-red-600 dark:hover:text-red-500" title="Eliminar">
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No hay profesores.</td>
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
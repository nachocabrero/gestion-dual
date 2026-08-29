<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Filtros -->
                    <form method="GET" class="mb-6 flex gap-4 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email..." class="border rounded px-3 py-2 text-sm flex-1 min-w-[200px]">
                        <select name="role" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos los roles</option>
                            <option value="alumno" {{ request('role') == 'alumno' ? 'selected' : '' }}>Alumno</option>
                            <option value="profesor" {{ request('role') == 'profesor' ? 'selected' : '' }}>Profesor</option>
                            <option value="coordinador_dual" {{ request('role') == 'coordinador_dual' ? 'selected' : '' }}>Coordinador Dual</option>
                            <option value="empresa" {{ request('role') == 'empresa' ? 'selected' : '' }}>Empresa</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <select name="active" class="border rounded px-3 py-2 text-sm">
                            <option value="">Estado</option>
                            <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Desactivados</option>
                        </select>
                        <x-primary-button class="text-sm py-1">Filtrar</x-primary-button>
                    </form>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">RGPD</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($users as $user)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $user->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $user->email }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @foreach($user->roles as $role)
                                            <span class="inline-block bg-gray-200 dark:bg-gray-700 rounded px-2 py-0.5 text-xs mr-1 mb-1 capitalize">{{ str_replace('_', ' ', $role) }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($user->is_active)
                                            <span class="text-green-600 font-semibold">Activo</span>
                                        @else
                                            <span class="text-red-600 font-semibold">Desactivado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($user->hasConsentedRgpd())
                                            <span class="text-green-600">✓</span>
                                        @else
                                            <span class="text-yellow-600">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2">
                                        @if($user->is_active)
                                            <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Desactivar</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs">Reactivar</button>
                                            </form>
                                        @endif
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-800 hover:text-red-900 text-xs font-bold">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No hay usuarios.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
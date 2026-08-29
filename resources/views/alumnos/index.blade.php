<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Alumnado') }}
            </h2>
            @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
            <a href="{{ route('alumnos.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                + Nuevo Alumno
            </a>
            @endif
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nombre o email..." class="border rounded px-3 py-2 text-sm flex-1 min-w-[200px]">
                        <select name="familia" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todas las familias</option>
                            @foreach($familias as $f)
                            <option value="{{ $f->codigo }}" {{ request('familia') == $f->codigo ? 'selected' : '' }}>{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                        <select name="ciclo" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos los ciclos</option>
                            @foreach($ciclos as $c)
                            <option value="{{ $c->codigo }}" {{ request('ciclo') == $c->codigo ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        <select name="linea" class="border rounded px-3 py-2 text-sm">
                            <option value="">Todos los turnos</option>
                            <option value="manana" {{ request('linea') == 'manana' ? 'selected' : '' }}>Mañana</option>
                            <option value="tarde" {{ request('linea') == 'tarde' ? 'selected' : '' }}>Tarde</option>
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ciclo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Grupo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($alumnos as $alumno)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $alumno->user->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $alumno->user->email }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @foreach($alumno->ciclosMatriculados as $ciclo)
                                        <span class="inline-block bg-blue-100 dark:bg-blue-900 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $ciclo->codigo }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($alumno->grupo)
                                        {{ $alumno->grupo->nombre }}
                                        @else
                                        <span class="text-gray-400">Sin grupo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($alumno->user->is_active)
                                        <span class="text-green-600 font-semibold">Activo</span>
                                        @else
                                        <span class="text-red-600 font-semibold">Desactivado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm space-x-2">
                                        <a href="{{ route('alumnos.show', $alumno) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver</a>
                                        <a href="{{ route('alumnos.edit', $alumno) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Editar</a>
                                        @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                                        @if($alumno->user->is_active)
                                        <form action="{{ route('alumnos.deactivate', $alumno) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Desactivar</button>
                                        </form>
                                        @else
                                        <form action="{{ route('alumnos.reactivate', $alumno) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-xs">Reactivar</button>
                                        </form>
                                        @endif
                                        <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-800 hover:text-red-900 text-xs font-bold">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No hay alumnos.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $alumnos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
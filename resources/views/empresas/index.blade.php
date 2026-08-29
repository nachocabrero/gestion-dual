@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Empresas</h1>
        <a href="{{ route('empresas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
            + Nueva empresa
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtros -->
    <form method="GET" action="{{ route('empresas.index') }}" class="bg-gray-800 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-400 text-sm mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nombre, CIF, email..."
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
            </div>
            <div>
                <label class="block text-gray-400 text-sm mb-1">Familia</label>
                <select name="familia" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    <option value="">Todas</option>
                    @foreach($familias as $f)
                        <option value="{{ $f }}" {{ request('familia') == $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-400 text-sm mb-1">Estado</label>
                <select name="active" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    <option value="">Todas</option>
                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">Filtrar</button>
            <a href="{{ route('empresas.index') }}" class="text-gray-400 hover:text-white text-sm ml-4">Limpiar</a>
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-gray-800 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">CIF</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Contacto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tutores</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Convenios</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($empresas as $empresa)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-white font-medium">{{ $empresa->nombre }}</div>
                        <div class="text-gray-400 text-sm">{{ $empresa->direccion }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-300 text-sm">{{ $empresa->cif }}</td>
                    <td class="px-6 py-4">
                        <div class="text-gray-300 text-sm">{{ $empresa->email ?? '—' }}</div>
                        <div class="text-gray-400 text-sm">{{ $empresa->telefono ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-300 text-sm">{{ $empresa->tutoresLaborales->count() }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-300 text-sm">{{ $empresa->convenios->count() }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($empresa->is_active)
                            <span class="px-2 py-1 bg-green-900/50 text-green-400 rounded text-xs">Activa</span>
                        @else
                            <span class="px-2 py-1 bg-red-900/50 text-red-400 rounded text-xs">Inactiva</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('empresas.show', $empresa) }}" class="text-blue-400 hover:text-blue-300 text-sm">Ver</a>
                            <a href="{{ route('empresas.edit', $empresa) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">Editar</a>
                            @if($empresa->is_active)
                                <form action="{{ route('empresas.deactivate', $empresa) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-orange-400 hover:text-orange-300 text-sm">Desactivar</button>
                                </form>
                            @else
                                <form action="{{ route('empresas.reactivate', $empresa) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-400 hover:text-green-300 text-sm">Reactivar</button>
                                </form>
                            @endif
                            <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No hay empresas registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $empresas->links() }}
    </div>
</div>
@endsection
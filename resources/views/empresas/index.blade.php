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
                    <td class="px-6 py-4 text-center">
                        @if($empresa->is_active)
                            <span title="Activa" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-900/50 text-green-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        @else
                            <span title="Inactiva" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-900/50 text-red-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('empresas.show', $empresa) }}" class="text-blue-400 hover:text-blue-300" title="Ver">
                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('empresas.edit', $empresa) }}" class="text-yellow-400 hover:text-yellow-300" title="Editar">
                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($empresa->is_active)
                                <form action="{{ route('empresas.deactivate', $empresa) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-orange-400 hover:text-orange-300" title="Desactivar">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('empresas.reactivate', $empresa) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-400 hover:text-green-300" title="Reactivar">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300" title="Eliminar">
                                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
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
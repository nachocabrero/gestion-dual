<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Ciclos · {{ $familia->nombre }}</h2>
            <a href="{{ route('admin.estructura.ciclos.create', $familia) }}" class="hlanz-btn-primary">+ Nuevo Ciclo</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="text-xs text-slate-500 mb-4">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.familias.show', $familia) }}" class="hover:text-[#0048FE]">{{ $familia->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">Ciclos</span>
            </div>

            <div class="hlanz-card p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Código</th>
                                <th class="p-3">Nombre</th>
                                <th class="p-3">Grado</th>
                                <th class="p-3 text-center">Cursos</th>
                                <th class="p-3 text-center">Líneas</th>
                                <th class="p-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($ciclos as $ciclo)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-mono text-xs text-slate-500">{{ $ciclo->codigo }}</td>
                                <td class="p-3 font-semibold text-slate-900">
                                    <a href="{{ route('admin.estructura.ciclos.show', $ciclo) }}" class="hover:text-[#0048FE]">{{ $ciclo->nombre }}</a>
                                </td>
                                <td class="p-3 capitalize text-slate-600">{{ $ciclo->grado }}</td>
                                <td class="p-3 text-center text-slate-600">{{ $ciclo->duracion_anos }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('admin.estructura.lineas.index', $ciclo) }}" class="hlanz-badge-primary">{{ $ciclo->lineas_count }} líneas</a>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.estructura.ciclos.edit', $ciclo) }}" class="text-yellow-600 text-xs font-bold hover:underline mr-3">Editar</a>
                                    <form action="{{ route('admin.estructura.ciclos.destroy', $ciclo) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este ciclo?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 text-xs font-bold hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="p-8 text-center text-slate-400 text-sm">No hay ciclos en esta familia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

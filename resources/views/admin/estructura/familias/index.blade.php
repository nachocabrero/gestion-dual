<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Familias Profesionales</h2>
            <a href="{{ route('admin.estructura.familias.create') }}" class="hlanz-btn-primary">+ Nueva Familia</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Código</th>
                                <th class="p-3">Nombre</th>
                                <th class="p-3 text-center">Ciclos</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($familias as $familia)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-mono text-xs text-slate-500">{{ $familia->codigo }}</td>
                                <td class="p-3 font-semibold text-slate-900">
                                    <a href="{{ route('admin.estructura.familias.show', $familia) }}" class="hover:text-[#0048FE]">{{ $familia->nombre }}</a>
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('admin.estructura.ciclos.index', $familia) }}" class="hlanz-badge-primary">{{ $familia->ciclos_count }} ciclos</a>
                                </td>
                                <td class="p-3 text-center">
                                    @if($familia->is_active)
                                    <span class="hlanz-badge-success">Activa</span>
                                    @else
                                    <span class="hlanz-badge-danger">Inactiva</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.estructura.familias.edit', $familia) }}" class="text-yellow-600 text-xs font-bold hover:underline mr-3">Editar</a>
                                    <form action="{{ route('admin.estructura.familias.destroy', $familia) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta familia?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 text-xs font-bold hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="p-8 text-center text-slate-400 text-sm">No hay familias registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $familias->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

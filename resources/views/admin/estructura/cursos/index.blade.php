<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Cursos Académicos</h2>
            <a href="{{ route('admin.estructura.cursos.create') }}" class="hlanz-btn-primary">+ Nuevo Curso</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-sm text-slate-700">
                El <strong>curso actual</strong> es el que se usa por defecto en la aplicación. Marca con
                «Hacer actual» el curso que está en marcha. Los cursos anteriores quedan disponibles para
                consultar el histórico.
            </div>

            <div class="hlanz-card p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Curso</th>
                                <th class="p-3">Inicio</th>
                                <th class="p-3">Fin</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-center">Proyectos</th>
                                <th class="p-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($cursos as $curso)
                            <tr class="hover:bg-slate-50 transition-colors {{ $curso->is_active ? 'bg-emerald-50/40' : '' }}">
                                <td class="p-3 font-semibold text-slate-900">{{ $curso->nombre }}</td>
                                <td class="p-3 text-slate-600">{{ optional($curso->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="p-3 text-slate-600">{{ optional($curso->fecha_fin)->format('d/m/Y') }}</td>
                                <td class="p-3 text-center">
                                    @if($curso->is_active)
                                    <span class="hlanz-badge-success">Actual</span>
                                    @else
                                    <span class="hlanz-badge-info">Anterior</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center text-slate-600">{{ $curso->proyectos_count }}</td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    @if(!$curso->is_active)
                                    <form action="{{ route('admin.estructura.cursos.activo', $curso) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="text-emerald-600 text-xs font-bold hover:underline mr-3">Hacer actual</button>
                                    </form>
                                    <form action="{{ route('admin.estructura.cursos.destroy', $curso) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este curso académico?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 text-xs font-bold hover:underline">Eliminar</button>
                                    </form>
                                    @else
                                    <span class="text-xs text-slate-400">Curso en uso</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="p-8 text-center text-slate-400 text-sm">No hay cursos académicos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $cursos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

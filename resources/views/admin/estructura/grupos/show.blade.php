<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.estructura.grupos.edit', $grupo) }}" class="hlanz-btn-secondary">Editar Grupo</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="text-xs text-slate-500">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.familias.show', $grupo->linea->ciclo->familia) }}" class="hover:text-[#0048FE]">{{ $grupo->linea->ciclo->familia->nombre }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.ciclos.show', $grupo->linea->ciclo) }}" class="hover:text-[#0048FE]">{{ $grupo->linea->ciclo->nombre }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.lineas.show', $grupo->linea) }}" class="hover:text-[#0048FE]">{{ $grupo->linea->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
            </div>

            <div class="hlanz-card p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Ciclo</p>
                        <p class="font-semibold text-slate-900">{{ $grupo->linea->ciclo->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Línea / Turno</p>
                        <p class="font-semibold text-slate-900">{{ $grupo->linea->nombre }} ({{ ucfirst($grupo->linea->turno) }})</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Curso académico</p>
                        <p class="font-semibold text-slate-900">{{ $grupo->cursoAcademico?->nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Número</p>
                        <p class="font-semibold text-slate-900">{{ $grupo->numero }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Tutor</p>
                        <p class="font-semibold text-slate-900">{{ $grupo->tutor?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Estado</p>
                        <p class="font-semibold text-slate-900">
                            @if($grupo->is_active)
                            <span class="hlanz-badge-success">Activo</span>
                            @else
                            <span class="hlanz-badge-danger">Inactivo</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <form action="{{ route('admin.estructura.grupos.destroy', $grupo) }}" method="POST" onsubmit="return confirm('¿Eliminar este grupo?')">
                        @csrf @method('DELETE')
                        <button class="text-rose-600 text-xs font-bold hover:underline">Eliminar grupo</button>
                    </form>
                </div>
            </div>

            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900">Alumnos del grupo ({{ $miembros->count() }})</h3>
                </div>

                @if($miembros->isNotEmpty())
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                    @foreach($miembros as $alumno)
                    <div class="flex items-center justify-between gap-4 px-4 py-3 bg-white">
                        <div>
                            <p class="font-semibold text-sm text-slate-900">{{ $alumno->user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $alumno->user->email }}</p>
                        </div>
                        <form action="{{ route('admin.estructura.grupos.alumnos.remove', [$grupo, $alumno]) }}" method="POST" onsubmit="return confirm('¿Quitar a este alumno del grupo?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-bold text-rose-600 hover:underline">Quitar</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-slate-400 text-sm">Este grupo aún no tiene alumnos.</p>
                @endif

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-bold font-display text-slate-900 mb-3">Añadir alumnos al grupo</h4>
                    <form method="GET" action="{{ route('admin.estructura.grupos.show', $grupo) }}" class="flex gap-2 mb-3">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar alumno por nombre o email..."
                            class="flex-1 rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        <button type="submit" class="hlanz-btn-primary">Buscar</button>
                    </form>

                    @if($candidatos->isNotEmpty())
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                        @foreach($candidatos as $alumno)
                        <div class="flex items-center justify-between gap-4 px-4 py-3 bg-white">
                            <div>
                                <p class="font-semibold text-sm text-slate-900">{{ $alumno->user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $alumno->user->email }}</p>
                            </div>
                            <form action="{{ route('admin.estructura.grupos.alumnos.add', $grupo) }}" method="POST">
                                @csrf
                                <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                <button class="hlanz-btn-secondary text-xs">Añadir</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @elseif(request()->filled('buscar'))
                    <p class="text-slate-400 text-sm">No se encontraron alumnos que no estén ya en este grupo.</p>
                    @else
                    <p class="text-slate-400 text-sm">Usa el buscador para encontrar alumnos que añadir.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

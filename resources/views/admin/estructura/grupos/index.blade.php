<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Grupos · {{ $linea->nombre }}</h2>
            <a href="{{ route('admin.estructura.grupos.create', $linea) }}" class="hlanz-btn-primary">+ Nuevo Grupo</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="text-xs text-slate-500 mb-4">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.ciclos.show', $linea->ciclo) }}" class="hover:text-[#0048FE]">{{ $linea->ciclo->nombre }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.lineas.show', $linea) }}" class="hover:text-[#0048FE]">{{ $linea->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">Grupos</span>
            </div>

            <div class="hlanz-card p-6">
                @if($gruposActuales->isNotEmpty())
                    <h4 class="text-sm font-bold font-display text-slate-900 mb-3">
                        Curso actual
                        @if($cursoActual)
                            <span class="hlanz-badge-primary ml-2">{{ $cursoActual->nombre }}</span>
                        @endif
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        @foreach($gruposActuales as $grupo)
                        <a href="{{ route('admin.estructura.grupos.show', $grupo) }}" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-900">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
                                <span class="hlanz-badge-primary">{{ $grupo->alumnos_count }} alumnos</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Tutor: {{ $grupo->tutor?->name ?? 'Sin asignar' }}</p>
                        </a>
                        @endforeach
                    </div>
                @endif

                @if($gruposAnteriores->isNotEmpty())
                    <h4 class="text-sm font-bold font-display text-slate-900 mb-3">Cursos anteriores</h4>
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                        @foreach($gruposAnteriores as $grupo)
                        <a href="{{ route('admin.estructura.grupos.show', $grupo) }}" class="flex items-center justify-between gap-4 px-4 py-3 bg-white hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-sm text-slate-900">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
                                <span class="text-xs text-slate-400">{{ $grupo->cursoAcademico?->nombre ?? 'Sin curso' }}</span>
                            </div>
                            <span class="text-xs text-slate-500">{{ $grupo->alumnos_count }} alumnos</span>
                        </a>
                        @endforeach
                    </div>
                @endif

                @if($gruposActuales->isEmpty() && $gruposAnteriores->isEmpty())
                    <p class="text-slate-400 text-sm">No hay grupos en esta línea.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

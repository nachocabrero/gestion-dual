<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">{{ $linea->nombre }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.estructura.grupos.create', $linea) }}" class="hlanz-btn-primary">+ Nuevo Grupo</a>
                <a href="{{ route('admin.estructura.lineas.edit', $linea) }}" class="hlanz-btn-secondary">Editar Línea</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="text-xs text-slate-500">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.familias.show', $linea->ciclo->familia) }}" class="hover:text-[#0048FE]">{{ $linea->ciclo->familia->nombre }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.ciclos.show', $linea->ciclo) }}" class="hover:text-[#0048FE]">{{ $linea->ciclo->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">{{ $linea->nombre }} ({{ ucfirst($linea->turno) }})</span>
            </div>

            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900">Grupos</h3>
                    <a href="{{ route('admin.estructura.grupos.index', $linea) }}" class="text-xs font-bold text-[#0048FE] hover:underline">Ver todos</a>
                </div>

                @if($gruposActuales->isNotEmpty())
                    <h4 class="text-xs font-bold font-display text-slate-500 uppercase tracking-wider mb-3">
                        Curso actual
                        @if($cursoActual)<span class="ml-2 normal-case">({{ $cursoActual->nombre }})</span>@endif
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        @foreach($gruposActuales as $grupo)
                        <a href="{{ route('admin.estructura.grupos.show', $grupo) }}" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-900">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ $grupo->tutor?->name ?? 'Sin tutor' }}</p>
                        </a>
                        @endforeach
                    </div>
                @endif

                @if($gruposAnteriores->isNotEmpty())
                    <h4 class="text-xs font-bold font-display text-slate-500 uppercase tracking-wider mb-3">Cursos anteriores</h4>
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                        @foreach($gruposAnteriores as $grupo)
                        <a href="{{ route('admin.estructura.grupos.show', $grupo) }}" class="flex items-center justify-between gap-4 px-4 py-3 bg-white hover:bg-slate-50 transition-colors">
                            <span class="font-semibold text-sm text-slate-900">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
                            <span class="text-xs text-slate-400">{{ $grupo->cursoAcademico?->nombre ?? 'Sin curso' }}</span>
                        </a>
                        @endforeach
                    </div>
                @endif

                @if($gruposActuales->isEmpty() && $gruposAnteriores->isEmpty())
                    <p class="text-slate-400 text-sm">Aún no hay grupos en esta línea.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

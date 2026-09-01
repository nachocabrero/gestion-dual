<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">{{ $ciclo->nombre }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.estructura.lineas.create', $ciclo) }}" class="hlanz-btn-primary">+ Nueva Línea</a>
                <a href="{{ route('admin.estructura.grupos.create-ciclo', $ciclo) }}" class="hlanz-btn-primary">+ Nuevo Grupo</a>
                <a href="{{ route('admin.estructura.asignaturas.create', $ciclo) }}" class="hlanz-btn-secondary">+ Asignatura</a>
                <a href="{{ route('admin.estructura.ciclos.edit', $ciclo) }}" class="hlanz-btn-secondary">Editar Ciclo</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="text-xs text-slate-500">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.familias.show', $ciclo->familia) }}" class="hover:text-[#0048FE]">{{ $ciclo->familia->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">{{ $ciclo->nombre }}</span>
            </div>

            <div class="hlanz-card p-4 flex flex-wrap gap-4 text-sm text-slate-600">
                <span><strong class="text-slate-900">Grado:</strong> {{ ucfirst($ciclo->grado) }}</span>
                <span><strong class="text-slate-900">Cursos:</strong> {{ $ciclo->duracion_anos }}</span>
                <span><strong class="text-slate-900">Líneas:</strong> {{ $ciclo->lineas->count() }}</span>
                <span><strong class="text-slate-900">Asignaturas:</strong> {{ $ciclo->asignaturas->count() }}</span>
            </div>

            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900">Líneas / Turnos</h3>
                    <a href="{{ route('admin.estructura.lineas.index', $ciclo) }}" class="text-xs font-bold text-[#0048FE] hover:underline">Ver todas</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($ciclo->lineas as $linea)
                    <a href="{{ route('admin.estructura.lineas.show', $linea) }}" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900">{{ $linea->nombre }}</span>
                            <span class="hlanz-badge-primary">{{ ucfirst($linea->turno) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $linea->grupos->count() }} grupo(s)</p>
                    </a>
                    @empty
                    <p class="text-slate-400 text-sm col-span-full">Aún no hay líneas/turnos.</p>
                    @endforelse
                </div>
            </div>

            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900">Asignaturas</h3>
                    <a href="{{ route('admin.estructura.asignaturas.index', $ciclo) }}" class="text-xs font-bold text-[#0048FE] hover:underline">Ver todas</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($ciclo->asignaturas as $asig)
                    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs text-[#0048FE] font-bold">{{ $asig->codigo }}</span>
                            <span class="text-xs text-slate-400">{{ $asig->horas_semanales }}h/sem</span>
                        </div>
                        <p class="font-bold text-sm text-slate-900 mt-1">{{ $asig->nombre }}</p>
                    </div>
                    @empty
                    <p class="text-slate-400 text-sm col-span-full">Aún no hay asignaturas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

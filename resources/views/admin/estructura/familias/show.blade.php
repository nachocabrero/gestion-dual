<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">{{ $familia->nombre }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.estructura.ciclos.create', $familia) }}" class="hlanz-btn-primary">+ Nuevo Ciclo</a>
                <a href="{{ route('admin.estructura.familias.edit', $familia) }}" class="hlanz-btn-secondary">Editar Familia</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="text-xs text-slate-500">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">{{ $familia->nombre }}</span>
            </div>

            <div class="hlanz-card p-4 text-sm text-slate-600">
                <span class="font-mono text-xs text-slate-400">{{ $familia->codigo }}</span> — {{ $familia->descripcion ?? 'Sin descripción' }}
            </div>

            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Ciclos de esta familia</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($familia->ciclos as $ciclo)
                    <a href="{{ route('admin.estructura.ciclos.show', $ciclo) }}" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs text-[#0048FE] font-bold">{{ $ciclo->codigo }}</span>
                            <span class="hlanz-badge-success">{{ $ciclo->lineas->count() }} líneas</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">{{ $ciclo->nombre }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ ucfirst($ciclo->grado) }} · {{ $ciclo->duracion_anos }} curso(s)</p>
                    </a>
                    @empty
                    <p class="text-slate-400 text-sm col-span-full">Aún no hay ciclos. Crea el primero.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

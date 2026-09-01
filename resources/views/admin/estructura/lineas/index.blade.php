<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Líneas · {{ $ciclo->nombre }}</h2>
            <a href="{{ route('admin.estructura.lineas.create', $ciclo) }}" class="hlanz-btn-primary">+ Nueva Línea</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="text-xs text-slate-500 mb-4">
                <a href="{{ route('admin.estructura.familias.index') }}" class="hover:text-[#0048FE]">Familias</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.familias.show', $ciclo->familia) }}" class="hover:text-[#0048FE]">{{ $ciclo->familia->nombre }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.estructura.ciclos.show', $ciclo) }}" class="hover:text-[#0048FE]">{{ $ciclo->nombre }}</a>
                <span class="mx-1">/</span>
                <span class="font-semibold text-slate-700">Líneas</span>
            </div>

            <div class="hlanz-card p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($lineas as $linea)
                    <a href="{{ route('admin.estructura.lineas.show', $linea) }}" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-sm text-slate-900">{{ $linea->nombre }}</span>
                            <span class="hlanz-badge-primary">{{ ucfirst($linea->turno) }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $linea->grupos_count }} grupo(s)</p>
                    </a>
                    @empty
                    <p class="text-slate-400 text-sm col-span-full">No hay líneas en este ciclo.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold font-display text-slate-900 tracking-tight">Prácticas por Grupo</h2>
                <p class="text-sm text-slate-500 mt-1">Selecciona un grupo para gestionar las asignaciones</p>
            </div>
        </div>
    </x-slot>

    <div class="stitch-card p-6 md:p-8">
        <label for="grupo" class="block font-medium text-sm text-slate-700 mb-2">Seleccionar grupo:</label>
        <select id="grupo" class="stitch-input max-w-md" onchange="if(this.value) window.location.href='/practicas/grupos/'+this.value">
            <option value="">-- Elegir grupo --</option>
            @foreach($grupos as $g)
            <option value="{{ $g->id }}" {{ $selectedGroupId == $g->id ? 'selected' : '' }}>
                {{ $g->nombre ?? ('Grupo ' . $g->numero) }} — {{ $g->linea?->nombre ?? '—' }}
            </option>
            @endforeach
        </select>
    </div>
</x-app-layout>

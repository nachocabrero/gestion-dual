<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Nueva Asignatura · {{ $ciclo->nombre }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.asignaturas.store', $ciclo) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej. DAWES" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('codigo') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Desarrollo Web en Entorno Servidor" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('nombre') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Horas/semana</label>
                            <input type="number" name="horas_semanales" value="{{ old('horas_semanales', 4) }}" min="1" max="40" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Guardar</button>
                            <a href="{{ route('admin.estructura.asignaturas.index', $ciclo) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

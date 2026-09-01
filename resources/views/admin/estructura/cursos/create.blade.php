<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Nuevo Curso Académico</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.cursos.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre del curso</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="ej: 2026-2027"
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('nombre') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Fecha de inicio</label>
                            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('fecha_inicio') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Fecha de fin</label>
                            <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}"
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('fecha_fin') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Guardar</button>
                            <a href="{{ route('admin.estructura.cursos.index') }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

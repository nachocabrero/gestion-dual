<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Editar Asignatura</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.asignaturas.update', $asignatura) }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ciclo</label>
                            <select name="ciclo_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                @foreach($ciclos as $c)
                                <option value="{{ $c->id }}" {{ old('ciclo_id', $asignatura->ciclo_id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $asignatura->codigo) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('codigo') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $asignatura->nombre) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Horas/semana</label>
                            <input type="number" name="horas_semanales" value="{{ old('horas_semanales', $asignatura->horas_semanales) }}" min="1" max="40" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" {{ $asignatura->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                Activa
                            </label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Actualizar</button>
                            <a href="{{ route('admin.estructura.asignaturas.index', $asignatura->ciclo_id) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Nuevo Grupo · {{ isset($linea) ? $linea->nombre : $ciclo->nombre }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ isset($linea) ? route('admin.estructura.grupos.store', $linea) : route('admin.estructura.grupos.store-ciclo', $ciclo) }}">
                    @csrf
                    <div class="space-y-4">
                        @isset($lineas)
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Línea / Turno</label>
                            <select name="linea_id" required class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Selecciona una línea</option>
                                @foreach($lineas as $ln)
                                <option value="{{ $ln->id }}" {{ old('linea_id')==$ln->id ? 'selected' : '' }}>{{ $ln->nombre }} ({{ ucfirst($ln->turno) }})</option>
                                @endforeach
                            </select>
                            @error('linea_id') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endisset
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Número</label>
                            <input type="number" name="numero" value="{{ old('numero') }}" min="1" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('numero') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. 1º DAW-Mañana A"
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Curso académico</label>
                            <select name="curso_academico_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Sin curso</option>
                                @foreach($cursos as $curso)
                                <option value="{{ $curso->id }}" {{ old('curso_academico_id')==$curso->id ? 'selected' : '' }}>{{ $curso->nombre }} ({{ $curso->is_active ? 'activo' : 'inactivo' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tutor del grupo</label>
                            <select name="tutor_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Sin tutor</option>
                                @foreach($tutores as $t)
                                <option value="{{ $t->user_id }}" {{ old('tutor_id')==$t->user_id ? 'selected' : '' }}>{{ $t->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Guardar</button>
                            <a href="{{ isset($linea) ? route('admin.estructura.grupos.index', $linea) : route('admin.estructura.ciclos.show', $ciclo) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Editar Grupo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.grupos.update', $grupo) }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Línea</label>
                            <select name="linea_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                @foreach($lineas as $l)
                                <option value="{{ $l->id }}" {{ old('linea_id', $grupo->linea_id) == $l->id ? 'selected' : '' }}>{{ $l->ciclo->nombre }} · {{ $l->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Número</label>
                            <input type="number" name="numero" value="{{ old('numero', $grupo->numero) }}" min="1" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('numero') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $grupo->nombre) }}"
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Curso académico</label>
                            <select name="curso_academico_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Sin curso</option>
                                @foreach($cursos as $curso)
                                <option value="{{ $curso->id }}" {{ old('curso_academico_id', $grupo->curso_academico_id) == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tutor del grupo</label>
                            <select name="tutor_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                <option value="">Sin tutor</option>
                                @foreach($tutores as $t)
                                <option value="{{ $t->user_id }}" {{ old('tutor_id', $grupo->tutor_id) == $t->user_id ? 'selected' : '' }}>{{ $t->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" {{ $grupo->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                Activo
                            </label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Actualizar</button>
                            <a href="{{ route('admin.estructura.grupos.show', $grupo) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

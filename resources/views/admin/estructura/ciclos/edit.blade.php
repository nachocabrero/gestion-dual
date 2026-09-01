<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Editar Ciclo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.ciclos.update', $ciclo) }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Familia</label>
                            <select name="familia_id" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                @foreach($familias as $f)
                                <option value="{{ $f->id }}" {{ old('familia_id', $ciclo->familia_id) == $f->id ? 'selected' : '' }}>{{ $f->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $ciclo->codigo) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('codigo') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $ciclo->nombre) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('nombre') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="2" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">{{ old('descripcion', $ciclo->descripcion) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Grado</label>
                                <select name="grado" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                    @foreach(['basica'=>'Básica','media'=>'Media','superior'=>'Superior','especializacion'=>'Especialización','acreditacion'=>'Acreditación'] as $k=>$v)
                                    <option value="{{ $k }}" {{ old('grado', $ciclo->grado) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Nº de cursos</label>
                                <input type="number" name="duracion_anos" value="{{ old('duracion_anos', $ciclo->duracion_anos) }}" min="1" max="5" required
                                    class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                                @error('duracion_anos') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" {{ $ciclo->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                Activo
                            </label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Actualizar</button>
                            <a href="{{ route('admin.estructura.ciclos.show', $ciclo) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

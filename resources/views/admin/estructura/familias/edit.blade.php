<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Editar Familia</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.familias.update', $familia) }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $familia->codigo) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('codigo') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $familia->nombre) }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('nombre') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="3" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">{{ old('descripcion', $familia->descripcion) }}</textarea>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" {{ $familia->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                                Activa
                            </label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Actualizar</button>
                            <a href="{{ route('admin.estructura.familias.show', $familia) }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

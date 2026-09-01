<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Nueva Familia Profesional</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="hlanz-card p-6">
                <form method="POST" action="{{ route('admin.estructura.familias.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo') }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('codigo') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                                class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                            @error('nombre') <span class="text-rose-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="3" class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="hlanz-btn-primary">Guardar</button>
                            <a href="{{ route('admin.estructura.familias.index') }}" class="hlanz-btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

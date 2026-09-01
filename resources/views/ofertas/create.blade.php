<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva Oferta de Prácticas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('ofertas.store') }}">
                        @csrf

                        <!-- Empresa -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Empresa *</label>
                            <select name="empresa_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                                <option value="">Seleccionar empresa...</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Especialidad requerida -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad requerida *</label>
                            <input type="text" name="especialidad_requerida" value="{{ old('especialidad_requerida') }}" maxlength="100" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                            @error('especialidad_requerida') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número de alumnos -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de alumnos *</label>
                            <input type="number" name="num_alumnos" value="{{ old('num_alumnos', 1) }}" min="1" max="20" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                            @error('num_alumnos') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Grupos a los que va dirigida -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grupo(s) a los que va dirigida</label>
                            <select name="grupo_ids[]" multiple size="6" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}" {{ in_array($grupo->id, old('grupo_ids', [])) ? 'selected' : '' }}>
                                    {{ $grupo->nombre ?: ('Grupo ' . $grupo->numero) }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Mantén Ctrl (o Cmd) pulsado para seleccionar varios grupos.</p>
                            @error('grupo_ids') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                            <textarea name="descripcion" rows="4" maxlength="2000" class="mt-1 block w-full border rounded px-3 py-2 text-sm">{{ old('descripcion') }}</textarea>
                            @error('descripcion') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>Guardar</x-primary-button>
                            <a href="{{ route('ofertas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

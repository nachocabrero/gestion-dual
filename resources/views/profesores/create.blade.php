<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nuevo Profesor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('profesores.store') }}">
                        @csrf

                        <!-- Datos personales -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                            <input type="password" name="password" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <!-- Datos profesionales -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad</label>
                            <input type="text" name="especialidad" value="{{ old('especialidad') }}" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4 flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tutor del grupo (opcional)</label>
                                <select name="tutor_grupo_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                    <option value="">Ninguno</option>
                                    @foreach($ciclos as $ciclo)
                                        <optgroup label="{{ $ciclo->nombre }}">
                                            @foreach($ciclo->lineas()->with('grupos')->get() as $linea)
                                                @foreach($linea->grupos as $grupo)
                                                    <option value="{{ $grupo->id }}" {{ old('tutor_grupo_id') == $grupo->id ? 'selected' : '' }}>{{ $grupo->nombre }}</option>
                                                @endforeach
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center mt-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="es_coordinador_dual" value="1" {{ old('es_coordinador_dual') ? 'checked' : '' }} class="mr-2">
                                    Coordinador Dual
                                </label>
                            </div>
                        </div>

                        <!-- Asignaturas -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Asignaturas</label>
                            <div class="max-h-40 overflow-y-auto border rounded p-2 text-gray-800 dark:text-gray-200">
                                @foreach($ciclos as $ciclo)
                                <div class="font-semibold text-xs mt-2">{{ $ciclo->nombre }}</div>
                                @foreach($ciclo->asignaturas as $asig)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="asignatura_ids[]" value="{{ $asig->id }}" {{ in_array($asig->id, old('asignatura_ids', [])) ? 'checked' : '' }} class="mr-2">
                                    {{ $asig->nombre }}
                                </label>
                                @endforeach
                                @endforeach
                            </div>
                        </div>

                        <!-- Equipos educativos -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Grupos a los que da clase</label>
                            <div class="max-h-40 overflow-y-auto border rounded p-2 text-gray-800 dark:text-gray-200">
                                @foreach($ciclos as $ciclo)
                                <div class="font-semibold text-xs mt-2">{{ $ciclo->nombre }}</div>
                                @foreach($ciclo->lineas()->with(['grupos' => fn($q) => $q->with('tutor')])->get() as $linea)
                                @foreach($linea->grupos as $grupo)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="grupo_ids[]" value="{{ $grupo->id }}" {{ in_array($grupo->id, old('grupo_ids', [])) ? 'checked' : '' }} class="mr-2">
                                    {{ $grupo->nombre }}
                                </label>
                                @endforeach
                                @endforeach
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Crear</button>
                            <a href="{{ route('profesores.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
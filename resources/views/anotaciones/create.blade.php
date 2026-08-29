<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva Anotación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('anotaciones.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="alumno_id">
                                Alumno
                            </label>
                            <select id="alumno_id" name="alumno_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1">
                                <option value="">-- Seleccionar alumno --</option>
                                @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}" {{ request('alumno') == $alumno->id ? 'selected' : '' }}>
                                    {{ $alumno->user->name }} ({{ $alumno->grupo?->nombre ?? '—' }})
                                </option>
                                @endforeach
                            </select>
                            @error('alumno_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="titulo">
                                Título
                            </label>
                            <input id="titulo" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" name="titulo" type="text" value="{{ old('titulo') }}" required>
                            @error('titulo')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="contenido">
                                Contenido
                            </label>
                            <textarea id="contenido" rows="5" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" name="contenido" required>{{ old('contenido') }}</textarea>
                            @error('contenido')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4 flex items-center">
                            <input id="es_publica" name="es_publica" type="checkbox" value="1" {{ old('es_publica') ? 'checked' : '' }}>
                            <label for="es_publica" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Hacer pública (visible para otros profesores)
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Guardar
                            </button>
                            <a href="{{ route('anotaciones.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
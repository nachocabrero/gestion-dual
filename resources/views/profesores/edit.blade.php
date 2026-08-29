<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Profesor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('profesores.update', $profesor) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                            <input type="text" name="name" value="{{ old('name', $profesor->user->name) }}" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" value="{{ old('email', $profesor->user->email) }}" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad</label>
                            <input type="text" name="especialidad" value="{{ old('especialidad', $profesor->especialidad) }}" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4 flex gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="es_tutor" value="1" {{ old('es_tutor', $profesor->es_tutor) ? 'checked' : '' }} class="mr-2">
                                Es tutor de grupo
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="es_coordinador_dual" value="1" {{ old('es_coordinador_dual', $profesor->es_coordinador_dual) ? 'checked' : '' }} class="mr-2">
                                Coordinador Dual
                            </label>
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Actualizar</button>
                            <a href="{{ route('profesores.show', $profesor) }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
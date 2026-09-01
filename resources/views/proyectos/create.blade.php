@extends('layouts.app')

@section('content')

    @section('header')
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nuevo Proyecto') }}
        </h2>
@endsection

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('proyectos.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="titulo" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Título del proyecto *</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('titulo') border-red-500 @enderror">
                            @error('titulo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descripción (máx. 300 palabras) *</label>
                            <textarea name="descripcion" id="descripcion" rows="6" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('descripcion') border-red-500 @enderror"
                                oninput="updateWordCount()">{{ old('descripcion') }}</textarea>
                            <div class="mt-1 text-sm text-gray-500"><span id="word-count">0</span>/300 palabras</div>
                            @error('descripcion')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Enlace repositorio -->
                        <div class="mb-4">
                            <label for="enlace_repositorio" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Enlace repositorio</label>
                            <input type="url" name="enlace_repositorio" id="enlace_repositorio" value="{{ old('enlace_repositorio') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('enlace_repositorio')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Enlace despliegue -->
                        <div class="mb-4">
                            <label for="enlace_despliegue" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Enlace despliegue</label>
                            <input type="url" name="enlace_despliegue" id="enlace_despliegue" value="{{ old('enlace_despliegue') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('enlace_despliegue')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Imágenes -->
                        <div class="mb-4">
                            <label for="imagenes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Imágenes del proyecto (máx. 5MB cada una)</label>
                            <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('imagenes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grupo -->
                        <div class="mb-4">
                            <label for="grupo_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Grupo *</label>
                            <select name="grupo_id" id="grupo_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Seleccionar...</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}" {{ old('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                        {{ $grupo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grupo_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center gap-3 mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Crear Proyecto
                            </button>
                            <a href="{{ route('proyectos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        function updateWordCount() {
            const text = document.getElementById('descripcion').value;
            const count = text.trim().split(/\s+/).filter(w => w.length > 0).length;
            document.getElementById('word-count').textContent = count;
            if (count > 300) {
                document.getElementById('word-count').classList.add('text-red-500');
            } else {
                document.getElementById('word-count').classList.remove('text-red-500');
            }
        }
        updateWordCount();
    </script>
    @endsection
@endsection
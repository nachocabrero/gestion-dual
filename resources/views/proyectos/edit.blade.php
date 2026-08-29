@extends('layouts.app')

@section('content')

    @section('header')
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Proyecto') }}
        </h2>
@endsection

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('proyectos.update', $proyecto) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="titulo" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Título *</label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $proyecto->titulo) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('titulo') border-red-500 @enderror">
                            @error('titulo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descripción (máx. 300 palabras) *</label>
                            <textarea name="descripcion" id="descripcion" rows="6" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('descripcion') border-red-500 @enderror"
                                oninput="updateWordCount()">{{ old('descripcion', $proyecto->descripcion) }}</textarea>
                            <div class="mt-1 text-sm text-gray-500"><span id="word-count">{{ str_word_count($proyecto->descripcion) }}</span>/300 palabras</div>
                            @error('descripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Enlaces -->
                        <div class="mb-4">
                            <label for="enlace_repositorio" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Enlace repositorio</label>
                            <input type="url" name="enlace_repositorio" id="enlace_repositorio" value="{{ old('enlace_repositorio', $proyecto->enlace_repositorio) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('enlace_repositorio')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="enlace_despliegue" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Enlace despliegue</label>
                            <input type="url" name="enlace_despliegue" id="enlace_despliegue" value="{{ old('enlace_despliegue', $proyecto->enlace_despliegue) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('enlace_despliegue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Imágenes actuales -->
                        @if($proyecto->imagenes->count() > 0)
                            <div class="mb-4">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">Imágenes actuales</label>
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach($proyecto->imagenes as $img)
                                        <div class="relative">
                                            <img src="{{ Storage::url($img->url) }}" alt="Proyecto" class="w-full h-24 object-cover rounded">
                                            <label class="absolute top-1 right-1">
                                                <input type="checkbox" name="eliminar_imagenes[]" value="{{ $img->id }}"
                                                    class="w-4 h-4 text-red-600 rounded">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Subir nuevas imágenes -->
                        <div class="mb-4">
                            <label for="imagenes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Subir nuevas imágenes</label>
                            <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('imagenes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center gap-3 mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Actualizar
                            </button>
                            <a href="{{ route('proyectos.show', $proyecto) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
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
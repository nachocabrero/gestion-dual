<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('alumnos.update', $alumno) }}">
                        @csrf
                        @method('PUT')

                        <!-- Datos personales -->
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Datos Personales</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Nombre')" />
                                <x-text-input id="name" name="name" :value="old('name', $alumno->user->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" :value="old('email', $alumno->user->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="telefono" :value="__('Teléfono')" />
                                <x-text-input id="telefono" name="telefono" :value="old('telefono', $alumno->telefono)" />
                            </div>

                            <div>
                                <x-input-label for="fecha_nacimiento" :value="__('Fecha de nacimiento')" />
                                <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" :value="old('fecha_nacimiento', $alumno->fecha_nacimiento?->format('Y-m-d'))" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="domicilio" :value="__('Domicilio')" />
                                <x-text-input id="domicilio" name="domicilio" :value="old('domicilio', $alumno->domicilio)" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
                                <x-text-input id="linkedin_url" name="linkedin_url" :value="old('linkedin_url', $alumno->linkedin_url)" placeholder="https://linkedin.com/in/..." />
                            </div>
                        </div>

                        <!-- Datos académicos -->
                        <h3 class="text-lg font-semibold mb-4 mt-6 text-gray-900 dark:text-white">Datos Académicos</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="grupo_id" :value="__('Grupo')" />
                                <select id="grupo_id" name="grupos_ids[]" multiple class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}" {{ in_array($grupo->id, old('grupos_ids', $alumno->grupos->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $grupo->nombre }} ({{ $grupo->linea->ciclo->codigo }} - {{ $grupo->linea->turno }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="tutor_practicas_id" :value="__('Tutor de prácticas')" />
                                <select id="tutor_practicas_id" name="tutor_practicas_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">Seleccionar tutor</option>
                                    @foreach($tutores as $tutor)
                                    <option value="{{ $tutor->id }}" {{ old('tutor_practicas_id', $alumno->tutor_practicas_id) == $tutor->id ? 'selected' : '' }}>
                                        {{ $tutor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="ciclo_ids[]" :value="__('Ciclos matriculados')" />
                                <div class="space-y-2 mt-2 text-gray-800 dark:text-gray-200">
                                    @php
                                        $ciclosMatricula = $alumno->ciclosMatriculados;
                                        $matriculados = $ciclosMatricula->pluck('id')->toArray();
                                    @endphp
                                    @foreach($grupos->pluck('linea.ciclo')->flatten()->unique('id') as $ciclo)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="ciclo_ids[]" value="{{ $ciclo->id }}" {{ in_array($ciclo->id, $matriculados) ? 'checked' : '' }} class="rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm">{{ $ciclo->codigo }} - {{ $ciclo->nombre }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('ciclo_ids')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-2">
                            <a href="{{ route('alumnos.show', $alumno) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-gray-700">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Actualizar Alumno
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
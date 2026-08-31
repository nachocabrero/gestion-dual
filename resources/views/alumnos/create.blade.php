<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nuevo Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('alumnos.store') }}">
                        @csrf

                        <!-- Datos personales -->
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Datos Personales</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Nombre')" />
                                <x-text-input id="name" name="name" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Contraseña')" />
                                <x-text-input id="password" name="password" type="password" required />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" required />
                            </div>

                            <div>
                                <x-input-label for="telefono" :value="__('Teléfono')" />
                                <x-text-input id="telefono" name="telefono" :value="old('telefono')" />
                            </div>

                            <div>
                                <x-input-label for="fecha_nacimiento" :value="__('Fecha de nacimiento')" />
                                <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" :value="old('fecha_nacimiento')" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="domicilio" :value="__('Domicilio')" />
                                <x-text-input id="domicilio" name="domicilio" :value="old('domicilio')" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
                                <x-text-input id="linkedin_url" name="linkedin_url" :value="old('linkedin_url')" placeholder="https://linkedin.com/in/..." />
                            </div>
                        </div>

                        <!-- Datos académicos -->
                        <h3 class="text-lg font-semibold mb-4 mt-6 text-gray-900 dark:text-white">Datos Académicos</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-input-label for="grupos_ids[]" :value="__('Grupos a los que pertenece')" />
                                <div class="space-y-2 mt-2 text-gray-800 dark:text-gray-200">
                                    @foreach($grupos as $grupo)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="grupos_ids[]" value="{{ $grupo->id }}" {{ is_array(old('grupos_ids')) && in_array($grupo->id, old('grupos_ids')) ? 'checked' : '' }} class="rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm">{{ $grupo->nombre }} ({{ $grupo->linea->ciclo->codigo ?? '' }} - {{ $grupo->linea->turno ?? '' }})</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <x-input-label for="tutor_practicas_id" :value="__('Tutor de prácticas')" />
                                <select id="tutor_practicas_id" name="tutor_practicas_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">Seleccionar tutor</option>
                                    @foreach($grupos->pluck('tutor')->filter() as $tutor)
                                    <option value="{{ $tutor->id }}" {{ old('tutor_practicas_id') == $tutor->id ? 'selected' : '' }}>
                                        {{ $tutor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label :value="__('Ciclos matriculados')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-2">Añade los ciclos en los que matricular al alumno</p>

                                <div class="flex gap-2 mb-2">
                                    <select id="nuevo_ciclo" class="flex-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500">
                                        <option value="">Seleccionar ciclo</option>
                                        @foreach($grupos->pluck('linea.ciclo')->flatten()->unique('id') as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ $ciclo->codigo }} — {{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" id="nuevo_curso" placeholder="2026-2027" class="w-32 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500">
                                    <button type="button" id="btn-add-create" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Añadir</button>
                                </div>

                                <div id="matriculas-container-create" class="space-y-1">
                                    @if(is_array(old('matriculas')))
                                    @foreach(old('matriculas') as $m)
                                    <input type="hidden" name="matriculas[][ciclo_id]" value="{{ $m['ciclo_id'] }}">
                                    <input type="hidden" name="matriculas[][curso_academico]" value="{{ $m['curso_academico'] }}">
                                    @endforeach
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('matriculas')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-2">
                            <a href="{{ route('alumnos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-gray-700">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Crear Alumno
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('btn-add-create').addEventListener('click', function() {
        const cicloSelect = document.getElementById('nuevo_ciclo');
        const cursoInput = document.getElementById('nuevo_curso');
        const container = document.getElementById('matriculas-container-create');

        if (!cicloSelect.value || !cursoInput.value) {
            return;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'matriculas[][ciclo_id]';
        input.value = cicloSelect.value;
        container.appendChild(input);

        const curso = document.createElement('input');
        curso.type = 'hidden';
        curso.name = 'matriculas[][curso_academico]';
        curso.value = cursoInput.value;
        container.appendChild(curso);

        cicloSelect.value = '';
        cursoInput.value = '';
    });
    </script>
</x-app-layout>
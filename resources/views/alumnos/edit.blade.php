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

                            <div>
                                <x-input-label for="linkedin_url" :value="__('LinkedIn URL (Opcional)')" />
                                <x-text-input id="linkedin_url" name="linkedin_url" :value="old('linkedin_url', $alumno->linkedin_url)" placeholder="https://linkedin.com/in/..." />
                            </div>

                            <div>
                                <x-input-label for="portfolio_url" :value="__('Portfolio Externo / Web (Opcional)')" />
                                <x-text-input id="portfolio_url" name="portfolio_url" :value="old('portfolio_url', $alumno->portfolio_url)" placeholder="https://tu-portfolio.com" />
                            </div>
                        </div>

                        <!-- Datos académicos -->
                        <h3 class="text-lg font-semibold mb-4 mt-6 text-gray-900 dark:text-white">Datos Académicos</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label :value="__('Grupos a los que pertenece')" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Marca los grupos a los que pertenece el alumno. Se muestran primero los del curso más reciente.</p>
                                <div class="mt-2 max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 space-y-1">
                                    @foreach($grupos as $grupo)
                                    <label class="flex items-start gap-2 p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox" name="grupos_ids[]" value="{{ $grupo->id }}"
                                            class="mt-1 rounded border-gray-300 dark:border-gray-700 text-slate-900 focus:ring-slate-500"
                                            {{ in_array($grupo->id, old('grupos_ids', $alumno->grupos->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <span class="text-sm">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $grupo->nombre ?? ('Grupo ' . $grupo->numero) }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                {{ $grupo->linea->ciclo->codigo ?? '' }} · {{ $grupo->linea->ciclo->nombre ?? '' }} · {{ ucfirst($grupo->linea->turno ?? '') }}
                                                @if($grupo->cursoAcademico)<span class="ml-1 text-slate-400">({{ $grupo->cursoAcademico->nombre }})</span>@endif
                                            </span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('grupos_ids')" class="mt-2" />
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

                            <div class="md:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Prácticas de 1º Curso (FCT / Dual realizada en primer año)
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Si el alumno está en 2º curso y realizó prácticas en 1º, indica la empresa y el número de horas realizadas.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="empresa_practicas_primero" :value="__('Empresa de prácticas (1º curso)')" />
                                        <x-text-input id="empresa_practicas_primero" name="empresa_practicas_primero" :value="old('empresa_practicas_primero', $alumno->empresa_practicas_primero)" placeholder="Ej. NTT Data, Everis..." />
                                        <x-input-error :messages="$errors->get('empresa_practicas_primero')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="horas_practicas_primero" :value="__('Horas de prácticas en 1º')" />
                                        <x-text-input id="horas_practicas_primero" name="horas_practicas_primero" type="number" min="0" max="2000" :value="old('horas_practicas_primero', $alumno->horas_practicas_primero)" placeholder="Ej. 100" />
                                        <x-input-error :messages="$errors->get('horas_practicas_primero')" class="mt-2" />
                                    </div>
                                </div>
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
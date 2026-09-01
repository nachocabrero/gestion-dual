<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva Práctica') }}
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

                    <form method="POST" action="{{ route('practicas.store') }}">
                        @csrf

                        <!-- Alumno -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alumno</label>
                            <select name="alumno_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                                <option value="">Seleccionar alumno...</option>
                                @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>{{ $alumno->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Empresa -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Empresa</label>
                            <select name="empresa_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                                <option value="">Seleccionar empresa...</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tutor laboral -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tutor laboral</label>
                            <select name="tutor_laboral_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                <option value="">Seleccionar tutor...</option>
                                @foreach($tutores as $tutor)
                                <option value="{{ $tutor->id }}" {{ old('tutor_laboral_id') == $tutor->id ? 'selected' : '' }}>{{ $tutor->nombre }} ({{ $tutor->empresa->nombre }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Curso académico -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Curso académico</label>
                            <select name="curso_academico_id" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                                <option value="">Seleccionar curso...</option>
                                @foreach($cursos as $curso)
                                <option value="{{ $curso->id }}" {{ old('curso_academico_id') == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fechas -->
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha fin</label>
                                <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                            </div>
                        </div>

                        <!-- Horas -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Horas acumuladas</label>
                            <input type="number" name="horas_acumuladas" value="{{ old('horas_acumuladas', 0) }}" min="0" class="mt-1 block w-full border rounded px-3 py-2 text-sm" required>
                            <p class="mt-1 text-xs text-gray-500">Se pueden registrar varias prácticas entre 1º y 2º; la suma total debe alcanzar 500h.</p>
                        </div>

                        <!-- Convenio -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="convenio_firmado" value="1" {{ old('convenio_firmado') ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Convenio firmado</span>
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>Guardar</x-primary-button>
                            <a href="{{ route('practicas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
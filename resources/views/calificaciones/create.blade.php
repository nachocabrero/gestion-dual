<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva Calificación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('calificaciones.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alumno</label>
                            <select name="alumno_id" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                <option value="">— Seleccionar —</option>
                                @foreach($alumnos as $a)
                                <option value="{{ $a->id }}" {{ $alumnoId == $a->id ? 'selected' : '' }}>
                                    {{ $a->user->name }} ({{ $a->grupo?->nombre ?? 'Sin grupo' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asignatura</label>
                            <select name="asignatura_id" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                <option value="">— Seleccionar —</option>
                                @foreach($asignaturas as $asig)
                                <option value="{{ $asig->id }}" {{ $asignaturaId == $asig->id ? 'selected' : '' }}>
                                    {{ $asig->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evaluación</label>
                            <select name="evaluacion" required class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                                <option value="primera" {{ $evaluacion == 'primera' ? 'selected' : '' }}>1ª Evaluación</option>
                                <option value="segunda" {{ $evaluacion == 'segunda' ? 'selected' : '' }}>2ª Evaluación</option>
                                <option value="tercera" {{ $evaluacion == 'tercera' ? 'selected' : '' }}>3ª Evaluación</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nota (0-10)</label>
                            <input type="number" step="0.01" min="0" max="10" name="nota" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                            <textarea name="observaciones" rows="3" class="mt-1 block w-full border rounded px-3 py-2 text-sm"></textarea>
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Crear</button>
                            <a href="{{ route('calificaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
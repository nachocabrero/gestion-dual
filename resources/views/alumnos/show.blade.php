<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Perfil de Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                    @endif

                    <!-- Datos personales -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Datos Personales</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Nombre:</span>
                                <p class="font-medium">{{ $alumno->user->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Email:</span>
                                <p class="font-medium">{{ $alumno->user->email }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Teléfono:</span>
                                <p class="font-medium">{{ $alumno->telefono ?? 'No indicado' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Fecha Nacimiento:</span>
                                <p class="font-medium">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? 'No indicada' }}</p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-sm text-gray-500">Domicilio:</span>
                                <p class="font-medium">{{ $alumno->domicilio ?? 'No indicado' }}</p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-sm text-gray-500">LinkedIn:</span>
                                <p class="font-medium">
                                    @if($alumno->linkedin_url)
                                    <a href="{{ $alumno->linkedin_url }}" target="_blank" class="text-blue-600 underline">{{ $alumno->linkedin_url }}</a>
                                    @else
                                    No indicado
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Académico -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Datos Académicos</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Grupo:</span>
                                <p class="font-medium">{{ $alumno->grupo->nombre ?? 'Sin grupo' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Tutor del grupo:</span>
                                <p class="font-medium">{{ $alumno->grupo->tutor->name ?? 'No asignado' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Tutor de prácticas:</span>
                                <p class="font-medium">{{ $alumno->tutorPracticas->name ?? 'No asignado' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Estado:</span>
                                <p class="font-medium">
                                    @if($alumno->user->is_active)
                                    <span class="text-green-600">Activo</span>
                                    @else
                                    <span class="text-red-600">Desactivado</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ciclos matriculados -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Ciclos Matriculados</h3>
                        <div class="space-y-2">
                            @forelse($alumno->ciclosMatriculados as $ciclo)
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900 p-3 rounded">
                                <div>
                                    <span class="font-semibold">{{ $ciclo->codigo }}</span> - {{ $ciclo->nombre }}
                                    <br>
                                    <span class="text-sm text-gray-500">{{ $ciclo->familia->nombre }} | {{ $ciclo->grado }}</span>
                                </div>
                                <div class="text-right text-sm text-gray-500">
                                    <p>Curso: {{ $ciclo->pivot->curso_academico }}</p>
                                    <p>Matriculado: {{ $ciclo->pivot->matriculado_at?->format('d/m/Y') }}</p>
                                    @if($ciclo->pivot->graduado_at)
                                    <p class="text-green-600">Graduado: {{ $ciclo->pivot->graduado_at->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500">No matriculado en ningún ciclo.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex gap-2">
                        <a href="{{ route('alumnos.edit', $alumno) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                            Editar
                        </a>
                        <a href="{{ route('alumnos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle de Práctica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Alumno</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->alumno->user->name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Empresa</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->empresa->nombre }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Tutor laboral</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->tutorLaboral->nombre ?? 'Sin asignar' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Curso académico</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->cursoAcademico->nombre }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Fecha inicio</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->fecha_inicio->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Fecha fin</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : '...' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Horas</h3>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $practica->horas_acumuladas }}h</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Convenio firmado</h3>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                @if($practica->convenio_firmado)
                                <span class="text-green-600 font-semibold">Sí</span>
                                @else
                                <span class="text-red-600 font-semibold">No</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                @if($practica->estaEnCurso())
                                <span class="text-blue-600 font-semibold">En curso</span>
                                @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                                <span class="text-gray-600">Finalizada</span>
                                @else
                                <span class="text-yellow-600">Pendiente</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('practicas.edit', $practica) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">Editar</a>
                        <a href="{{ route('practicas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis Prácticas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fechas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Horas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Convenio</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($practicas as $practica)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $practica->empresa->nombre }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->cursoAcademico->nombre }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->fecha_inicio->format('d/m/Y') }} - {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : '...' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $practica->horas_acumuladas }}h</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($practica->convenio_firmado)
                                        <span class="text-green-600 font-semibold">Sí</span>
                                        @else
                                        <span class="text-red-600 font-semibold">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($practica->estaEnCurso())
                                        <span class="text-blue-600 font-semibold">En curso</span>
                                        @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                                        <span class="text-gray-600">Finalizada</span>
                                        @else
                                        <span class="text-yellow-600">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No tienes prácticas registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Perfil del Profesor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Datos personales -->
                <h3 class="text-lg font-semibold mb-4">Datos Personales</h3>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><span class="text-gray-500">Nombre:</span> {{ $profesor->user->name }}</div>
                    <div><span class="text-gray-500">Email:</span> {{ $profesor->user->email }}</div>
                    <div><span class="text-gray-500">Especialidad:</span> {{ $profesor->especialidad ?? '—' }}</div>
                    <div><span class="text-gray-500">Estado:</span>
                        @if($profesor->user->is_active)
                        <span class="text-green-600 font-semibold">Activo</span>
                        @else
                        <span class="text-red-600 font-semibold">Desactivado</span>
                        @endif
                    </div>
                </div>

                <!-- Roles -->
                <h3 class="text-lg font-semibold mb-4">Roles</h3>
                <div class="mb-6 space-x-2">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm">Profesor</span>
                    @if($profesor->es_tutor)
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm">Tutor</span>
                    @endif
                    @if($profesor->es_coordinador_dual)
                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded text-sm">Coordinador Dual</span>
                    @endif
                </div>

                <!-- Grupos tutor -->
                @if($profesor->gruposTutor->count() > 0)
                <h3 class="text-lg font-semibold mb-4">Grupos Tutor</h3>
                <ul class="list-disc list-inside mb-6">
                    @foreach($profesor->gruposTutor as $grupo)
                    <li>{{ $grupo->nombre }} ({{ $grupo->linea->ciclo->nombre }})</li>
                    @endforeach
                </ul>
                @endif

                <!-- Asignaturas -->
                @if($profesor->asignaturas->count() > 0)
                <h3 class="text-lg font-semibold mb-4">Asignaturas</h3>
                <ul class="list-disc list-inside mb-6">
                    @foreach($profesor->asignaturas as $asig)
                    <li>{{ $asig->nombre }} ({{ $asig->horas_semanales }}h/semana)</li>
                    @endforeach
                </ul>
                @endif

                <!-- Sustituciones -->
                @if($profesor->sustituciones->count() > 0)
                <h3 class="text-lg font-semibold mb-4">Sustituciones</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Sustituto</th>
                                <th class="px-3 py-2 text-left">Asignatura</th>
                                <th class="px-3 py-2 text-left">Grupo</th>
                                <th class="px-3 py-2 text-left">Desde</th>
                                <th class="px-3 py-2 text-left">Hasta</th>
                                <th class="px-3 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($profesor->sustituciones as $sus)
                            <tr>
                                <td class="px-3 py-2">{{ $sus->profesorSustituto->user->name }}</td>
                                <td class="px-3 py-2">{{ $sus->asignatura?->nombre ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $sus->grupo?->nombre ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $sus->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $sus->fecha_fin->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">
                                    <form action="{{ route('profesores.sustituciones.destroy', $sus) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-xs">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Acciones -->
                <div class="flex gap-2 mt-6">
                    <a href="{{ route('profesores.edit', $profesor) }}" class="bg-yellow-600 text-white px-4 py-2 rounded text-sm hover:bg-yellow-700">Editar</a>
                    <a href="{{ route('profesores.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
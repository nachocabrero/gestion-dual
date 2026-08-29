<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Calificaciones de ' . $alumno->user->name) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Info alumno -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold">{{ $alumno->user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $alumno->grupo?->nombre ?? 'Sin grupo' }}</p>
                </div>

                <!-- Medias por evaluación -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded p-3 text-center">
                        <div class="text-xs text-gray-500">1ª Evaluación</div>
                        <div class="text-xl font-bold {{ floatval($medias['primera'] ?? 0) >= 5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $medias['primera'] }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded p-3 text-center">
                        <div class="text-xs text-gray-500">2ª Evaluación</div>
                        <div class="text-xl font-bold {{ floatval($medias['segunda'] ?? 0) >= 5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $medias['segunda'] }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded p-3 text-center">
                        <div class="text-xs text-gray-500">3ª Evaluación</div>
                        <div class="text-xl font-bold {{ floatval($medias['tercera'] ?? 0) >= 5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $medias['tercera'] }}
                        </div>
                    </div>
                </div>

                <!-- Tabla de calificaciones -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Asignatura</th>
                                <th class="px-3 py-2 text-left">1ª</th>
                                <th class="px-3 py-2 text-left">2ª</th>
                                <th class="px-3 py-2 text-left">3ª</th>
                                <th class="px-3 py-2 text-left">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @php $byAsig = $calificaciones->groupBy('asignatura_id'); @endphp
                            @forelse($byAsig as $asigId => $cals)
                            @php $asig = $cals->first()->asignatura; @endphp
                            <tr>
                                <td class="px-3 py-2 font-medium">{{ $asig->nombre }}</td>
                                @foreach(\App\Models\Calificacion::evaluaciones() as $eval)
                                @php $c = $cals->firstWhere('evaluacion', $eval); @endphp
                                <td class="px-3 py-2 text-center {{ $c && $c->nota >= 5 ? 'text-green-600 font-semibold' : ($c && $c->nota !== null ? 'text-red-600 font-semibold' : 'text-gray-400') }}">
                                    {{ $c && $c->nota !== null ? number_format($c->nota, 2) : '—' }}
                                </td>
                                @endforeach
                                <td class="px-3 py-2 text-gray-500 text-xs">{{ $cals->first()?->observaciones ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">Sin calificaciones.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <a href="{{ route('calificaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
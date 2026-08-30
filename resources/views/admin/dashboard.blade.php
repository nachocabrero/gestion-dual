<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Resumen de usuarios -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Alumnos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Alumnos</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalAlumnos }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="text-green-500">{{ $totalAlumnosActivos }}</span> activos ·
                                    <span class="text-red-500">{{ $totalAlumnosInactivos }}</span> inactivos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profesores -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profesores</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalProfesores }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="text-green-500">{{ $totalProfesoresActivos }}</span> activos ·
                                    <span class="text-red-500">{{ $totalProfesoresInactivos }}</span> inactivos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empresas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Empresas</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalEmpresas }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="text-green-500">{{ $totalEmpresas }}</span> activas ·
                                    <span class="text-red-500">{{ $totalEmpresasInactivas }}</span> inactivas
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prácticas -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Prácticas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-yellow-600">{{ $practicasPendientes }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pendientes</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ $practicasEnCurso }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">En curso</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-green-600">{{ $practicasFinalizadas }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Finalizadas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Convenios -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Convenios</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-green-600">{{ $conveniosFirmados }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Firmados</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 text-center">
                            <p class="text-3xl font-bold text-red-600">{{ $conveniosNoFirmados }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">No firmados</p>
                        </div>
                    </div>

                    <!-- Filtros convenios -->
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-3 mb-4">
                        <select name="convenio_familia" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md text-sm">
                            <option value="">Todas las familias</option>
                            @foreach(\App\Models\Familia::all() as $fam)
                                <option value="{{ $fam->id }}" {{ request('convenio_familia') == $fam->id ? 'selected' : '' }}>{{ $fam->nombre }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="convenio_curso" placeholder="Curso académico" value="{{ request('convenio_curso') }}" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md text-sm px-3">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600">Filtrar</button>
                        @if(request()->hasAny(['convenio_familia', 'convenio_curso']))
                        <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-md text-sm hover:bg-gray-300">Limpiar</a>
                        @endif
                    </form>

                    <!-- Tabla convenios -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ciclo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($convenios as $convenio)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $convenio->empresa->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $convenio->ciclo->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $convenio->curso_academico }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $convenio->estado === 'firmado' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                            {{ $convenio->estado === 'firmado' ? 'Firmado' : 'No firmado' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-sm text-center text-gray-500">No hay convenios</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $convenios->links() }}
                    </div>
                </div>
            </div>

            <!-- Proyectos destacados por ciclo -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Proyectos Destacados</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($destacadosPorCiclo as $cicloNombre => $count)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $cicloNombre }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $count }}</p>
                        </div>
                        @endforeach
                    </div>

                    @if($proyectosDestacados->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach($proyectosDestacados as $cicloNombre => $proyectos)
                        @foreach($proyectos->take(3) as $proyecto)
                        <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-yellow-500">★</span>
                            <div>
                                <p class="text-sm font-medium">{{ $proyecto->titulo }}</p>
                                <p class="text-xs text-gray-500">{{ $proyecto->alumno->user->name }} · {{ $proyecto->calificacion }}/10</p>
                            </div>
                        </div>
                        @endforeach
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ciclos y alumnos -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ciclos Formativos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($ciclos as $ciclo)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $ciclo->nombre }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ciclo->alumnos_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500">alumnos activos</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actividad Reciente</h3>
                    @if($actividadReciente->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($actividadReciente as $notif)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold
                                @if($notif->tipo === 'empresa_asignada') bg-green-500
                                @elseif($notif->tipo === 'estado_acuerdo') bg-yellow-500
                                @elseif($notif->tipo === 'proyecto_calificado') bg-blue-500
                                @else bg-gray-500 @endif">
                                {{ strtoupper(substr($notif->tipo, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $notif->titulo }}</p>
                                <p class="text-xs text-gray-500">{{ $notif->usuario->name ?? 'N/A' }} · {{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 text-center py-4">No hay actividad reciente</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
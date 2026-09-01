@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">{{ $empresa->nombre }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('empresas.edit', $empresa) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                Editar
            </a>
            <a href="{{ route('empresas.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Datos generales -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-white mb-4">Datos fiscales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-400">Nombre:</span>
                <span class="text-white ml-2">{{ $empresa->nombre }}</span>
            </div>
            <div>
                <span class="text-gray-400">CIF:</span>
                <span class="text-white ml-2">{{ $empresa->cif }}</span>
            </div>
            <div>
                <span class="text-gray-400">Dirección:</span>
                <span class="text-white ml-2">{{ $empresa->direccion ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400">Teléfono:</span>
                <span class="text-white ml-2">{{ $empresa->telefono ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400">Email:</span>
                <span class="text-white ml-2">{{ $empresa->email ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400">Responsable:</span>
                <span class="text-white ml-2">{{ $empresa->responsable_nombre ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400">DNI Responsable:</span>
                <span class="text-white ml-2">{{ $empresa->responsable_dni ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400">Estado:</span>
                <span class="ml-2">
                    @if($empresa->is_active)
                        <span class="px-2 py-1 bg-green-900/50 text-green-400 rounded text-xs">Activa</span>
                    @else
                        <span class="px-2 py-1 bg-red-900/50 text-red-400 rounded text-xs">Inactiva</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Tutores laborales -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-white mb-4">Tutores laborales ({{ $empresa->tutoresLaborales->count() }})</h2>
        @if($empresa->tutoresLaborales->count() > 0)
            <div class="divide-y divide-gray-700">
                @foreach($empresa->tutoresLaborales as $tutor)
                <div class="py-3 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">Nombre:</span>
                        <span class="text-white ml-2">{{ $tutor->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Email:</span>
                        <span class="text-white ml-2">{{ $tutor->email ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Teléfono:</span>
                        <span class="text-white ml-2">{{ $tutor->telefono ?? '—' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-sm">No hay tutores laborales asignados.</p>
        @endif
    </div>

    <!-- Ofertas y Prácticas por curso académico -->
    @forelse($bloques as $bloque)
        <div class="bg-gray-800 rounded-lg p-6 mb-6" x-data="{ abierto: {{ $bloque->es_actual ? 'true' : 'false' }} }">
            <button type="button" @click="abierto = !abierto" class="w-full flex items-center justify-between mb-2 text-left group">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-medium text-white group-hover:text-blue-400 transition-colors">
                        {{ $bloque->es_actual ? 'Ofertas y Prácticas — Curso actual' : 'Ofertas y Prácticas — Curso anterior' }}
                        @if($bloque->curso)
                            <span class="text-gray-400 font-normal">({{ $bloque->curso->nombre }})</span>
                        @endif
                    </h2>
                    @if($bloque->es_actual)
                        <span class="px-2 py-0.5 bg-blue-600/20 text-blue-400 rounded-full text-xs">Curso actual</span>
                    @endif
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="abierto" @if(!$bloque->es_actual) style="display: none;" @endif class="pt-4 border-t border-gray-700">
                <!-- Ofertas -->
                <h3 class="text-sm font-semibold text-white mb-2">Ofertas de Prácticas ({{ $bloque->ofertas->count() }})</h3>
                @if($bloque->ofertas->count() > 0)
                    <div class="divide-y divide-gray-700 mb-5">
                        @foreach($bloque->ofertas as $oferta)
                        <div class="py-3 grid grid-cols-1 md:grid-cols-5 gap-3 text-sm items-center">
                            <div class="md:col-span-2">
                                <span class="text-gray-400 block mb-1">Especialidad:</span>
                                <span class="text-white font-medium">{{ $oferta->especialidad_requerida }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block mb-1">Nº alumnos:</span>
                                <span class="text-white">{{ $oferta->num_alumnos }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block mb-1">Estado:</span>
                                @if($oferta->estado === 'activa')
                                    <span class="px-2 py-0.5 bg-green-900/50 text-green-400 rounded text-xs">Activa</span>
                                @elseif($oferta->estado === 'pendiente')
                                    <span class="px-2 py-0.5 bg-yellow-900/50 text-yellow-400 rounded text-xs">Pendiente</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-700 text-gray-300 rounded text-xs">Cerrada</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('ofertas.show', $oferta) }}" class="text-blue-400 hover:text-blue-300">Ver</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm mb-5">No hay ofertas de prácticas este curso.</p>
                @endif

                <!-- Prácticas -->
                <h3 class="text-sm font-semibold text-white mb-2">Prácticas ({{ $bloque->practicas->count() }})</h3>
                @if($bloque->practicas->count() > 0)
                    <div class="divide-y divide-gray-700">
                        @foreach($bloque->practicas as $practica)
                        <div class="py-3 grid grid-cols-1 md:grid-cols-5 gap-3 text-sm items-center">
                            <div class="md:col-span-2">
                                <span class="text-gray-400 block mb-1">Alumno:</span>
                                <span class="text-white font-medium">{{ $practica->alumno->user->name ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block mb-1">Periodo:</span>
                                <span class="text-white text-xs">{{ $practica->fecha_inicio->format('d/m/Y') }} al {{ $practica->fecha_fin?->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block mb-1">Horas:</span>
                                <span class="text-white">{{ $practica->horas_acumuladas }}h</span>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                @if($practica->convenio_firmado)
                                    <span class="px-2 py-0.5 bg-green-900/50 text-green-400 rounded text-xs">Convenio firmado</span>
                                @else
                                    <span class="px-2 py-0.5 bg-yellow-900/50 text-yellow-400 rounded text-xs">Convenio no firmado</span>
                                @endif
                                <a href="{{ route('practicas.show', $practica) }}" class="text-blue-400 hover:text-blue-300">Ver</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm">No hay prácticas registradas con esta empresa este curso.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <p class="text-gray-400 text-sm">Esta empresa no tiene ofertas ni prácticas registradas.</p>
        </div>
    @endforelse
</div>
@endsection
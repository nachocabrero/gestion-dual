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

    <!-- Convenios -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-white">Convenios y Acuerdos Formativos ({{ $empresa->convenios->count() }})</h2>
            <a href="{{ route('empresas.convenios.create', $empresa) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded text-sm">
                + Nuevo Convenio
            </a>
        </div>
        
        @if($empresa->convenios->count() > 0)
            <div class="divide-y divide-gray-700">
                @foreach($empresa->convenios as $convenio)
                <div class="py-4 grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
                    <div class="md:col-span-2">
                        <span class="text-gray-400 block mb-1">Alumno / Grupo:</span>
                        <span class="text-white font-medium">{{ $convenio->alumno->user->name ?? '—' }}</span>
                        <br><span class="text-gray-400 text-xs">{{ $convenio->grupo->linea->ciclo->nombre ?? '' }} - {{ $convenio->grupo->nombre ?? '' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-1">Tutores:</span>
                        <span class="text-white text-xs block">Laboral: {{ $convenio->tutorLaboral->nombre ?? '—' }}</span>
                        <span class="text-white text-xs block">Docente: {{ $convenio->tutorDocente->user->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-1">Periodo ({{ $convenio->numero_horas }}h):</span>
                        <span class="text-white text-xs block">{{ $convenio->fecha_inicio?->format('d/m/Y') }} al {{ $convenio->fecha_fin?->format('d/m/Y') }}</span>
                        <div class="mt-1">
                            @if($convenio->estaFirmado())
                                <span class="px-2 py-0.5 bg-green-900/50 text-green-400 rounded text-xs">Firmado {{ $convenio->fecha_firma?->format('d/m/Y') }}</span>
                            @else
                                <span class="px-2 py-0.5 bg-yellow-900/50 text-yellow-400 rounded text-xs">No firmado</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('empresas.convenios.edit', [$empresa, $convenio]) }}" class="text-blue-400 hover:text-blue-300">
                            Editar
                        </a>
                        <form action="{{ route('empresas.convenios.destroy', [$empresa, $convenio]) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este convenio?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Eliminar</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-sm">No hay convenios registrados para esta empresa.</p>
        @endif
    </div>
</div>
@endsection
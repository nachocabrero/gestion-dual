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
        <h2 class="text-lg font-medium text-white mb-4">Convenios ({{ $empresa->convenios->count() }})</h2>
        @if($empresa->convenios->count() > 0)
            <div class="divide-y divide-gray-700">
                @foreach($empresa->convenios as $convenio)
                <div class="py-3 grid grid-cols-1 md:grid-cols-4 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">Ciclo:</span>
                        <span class="text-white ml-2">{{ $convenio->ciclo->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Curso:</span>
                        <span class="text-white ml-2">{{ $convenio->curso_academico }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Estado:</span>
                        <span class="ml-2">
                            @if($convenio->estaFirmado())
                                <span class="px-2 py-1 bg-green-900/50 text-green-400 rounded text-xs">Firmado</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-900/50 text-yellow-400 rounded text-xs">No firmado</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Fecha firma:</span>
                        <span class="text-white ml-2">{{ $convenio->fecha_firma?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-sm">No hay convenios registrados.</p>
        @endif
    </div>
</div>
@endsection
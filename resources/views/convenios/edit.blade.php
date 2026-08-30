@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Editar Convenio / Acuerdo Formativo</h1>
    <h2 class="text-xl text-gray-300 mb-6">Empresa: {{ $empresa->nombre }}</h2>

    @if($errors->any())
        <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('empresas.convenios.update', [$empresa, $convenio]) }}">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Detalles del Convenio</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-gray-400 text-sm mb-1">Alumno *</label>
                    <select name="alumno_id" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        <option value="">Seleccionar alumno...</option>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" {{ old('alumno_id', $convenio->alumno_id) == $alumno->id ? 'selected' : '' }}>
                                {{ $alumno->user->name ?? 'Sin nombre' }} ({{ $alumno->user->email ?? 'Sin email' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-400 text-sm mb-1">Grupo / Clase *</label>
                    <select name="grupo_id" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        <option value="">Seleccionar grupo...</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ old('grupo_id', $convenio->grupo_id) == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->ciclo->nombre ?? 'Sin ciclo' }} - {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Tutor Docente (Profesor) *</label>
                    <select name="tutor_docente_id" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        <option value="">Seleccionar profesor...</option>
                        @foreach($profesores as $profesor)
                            <option value="{{ $profesor->id }}" {{ old('tutor_docente_id', $convenio->tutor_docente_id) == $profesor->id ? 'selected' : '' }}>
                                {{ $profesor->user->name ?? 'Sin nombre' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Tutor Laboral (Empresa) *</label>
                    <select name="tutor_laboral_id" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        <option value="">Seleccionar tutor...</option>
                        @foreach($tutores as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_laboral_id', $convenio->tutor_laboral_id) == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Fecha Inicio *</label>
                    <input type="date" name="fecha_inicio" required value="{{ old('fecha_inicio', $convenio->fecha_inicio?->format('Y-m-d')) }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Fecha Fin *</label>
                    <input type="date" name="fecha_fin" required value="{{ old('fecha_fin', $convenio->fecha_fin?->format('Y-m-d')) }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Número de horas *</label>
                    <input type="number" name="numero_horas" required min="1" value="{{ old('numero_horas', $convenio->numero_horas) }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Estado</label>
                    <select name="estado" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                        <option value="no_firmado" {{ old('estado', $convenio->estado) == 'no_firmado' ? 'selected' : '' }}>No firmado</option>
                        <option value="firmado" {{ old('estado', $convenio->estado) == 'firmado' ? 'selected' : '' }}>Firmado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Fecha firma</label>
                    <input type="date" name="fecha_firma" value="{{ old('fecha_firma', $convenio->fecha_firma?->format('Y-m-d')) }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
            Guardar Cambios
        </button>
        <a href="{{ route('empresas.show', $empresa) }}" class="text-gray-400 hover:text-white ml-4">Cancelar</a>
    </form>
</div>
@endsection

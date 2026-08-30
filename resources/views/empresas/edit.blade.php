@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Editar empresa</h1>

    <form method="POST" action="{{ route('empresas.update', $empresa) }}"
          onsubmit="document.getElementById('submit-btn').disabled=true; document.getElementById('submit-btn').textContent='Guardando...';">
        @csrf
        @method('PUT')

        <!-- Datos fiscales -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Datos fiscales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Nombre *</label>
                    <input type="text" name="nombre" required value="{{ old('nombre', $empresa->nombre) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    @error('nombre') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">CIF *</label>
                    <input type="text" name="cif" required value="{{ old('cif', $empresa->cif) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    @error('cif') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-400 text-sm mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Responsable</label>
                    <input type="text" name="responsable_nombre" value="{{ old('responsable_nombre', $empresa->responsable_nombre) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">DNI Responsable</label>
                    <input type="text" name="responsable_dni" value="{{ old('responsable_dni', $empresa->responsable_dni) }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
            </div>
        </div>

        <!-- Tutores laborales -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Tutores laborales</h2>
            <div id="tutores-container">
                @foreach($empresa->tutoresLaborales as $idx => $tutor)
                <div class="tutor-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Nombre</label>
                        <input type="text" name="tutores[{{ $idx }}][nombre]" value="{{ $tutor->nombre }}"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Email</label>
                        <input type="email" name="tutores[{{ $idx }}][email]" value="{{ $tutor->email }}"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Teléfono</label>
                        <input type="text" name="tutores[{{ $idx }}][telefono]" value="{{ $tutor->telefono }}"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <input type="hidden" name="tutores[{{ $idx }}][id]" value="{{ $tutor->id }}">
                        <button type="button" onclick="removeTutor(this)" class="text-red-400 hover:text-red-300 text-sm">
                            Eliminar
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addTutor()" class="text-blue-400 hover:text-blue-300 text-sm">
                + Añadir tutor
            </button>
        </div>

        <button type="submit" id="submit-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
            Guardar cambios
        </button>
        <a href="{{ route('empresas.show', $empresa) }}" class="text-gray-400 hover:text-white ml-4">Cancelar</a>
    </form>
</div>

<script>
let tutorIndex = {{ $empresa->tutoresLaborales->count() }};

function addTutor() {
    const container = document.getElementById('tutores-container');
    const row = document.createElement('div');
    row.className = 'tutor-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3';
    row.innerHTML = `
        <div>
            <input type="text" name="tutores[${tutorIndex}][nombre]"
                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <input type="email" name="tutores[${tutorIndex}][email]"
                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div>
            <input type="text" name="tutores[${tutorIndex}][telefono]"
                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
    `;
    container.appendChild(row);
    tutorIndex++;
}

function removeTutor(btn) {
    btn.closest('.tutor-row').remove();
}
</script>
@endsection
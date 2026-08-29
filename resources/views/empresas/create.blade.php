@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Nueva empresa</h1>

    <form method="POST" action="{{ route('empresas.store') }}"
          onsubmit="document.getElementById('submit-btn').disabled=true; document.getElementById('submit-btn').textContent='Creando...';">
        @csrf

        <!-- Datos fiscales -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Datos fiscales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Nombre *</label>
                    <input type="text" name="nombre" required value="{{ old('nombre') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    @error('nombre') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">CIF *</label>
                    <input type="text" name="cif" required value="{{ old('cif') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    @error('cif') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-400 text-sm mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Responsable</label>
                    <input type="text" name="responsable_nombre" value="{{ old('responsable_nombre') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">DNI Responsable</label>
                    <input type="text" name="responsable_dni" value="{{ old('responsable_dni') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                </div>
            </div>
        </div>

        <!-- Tutores laborales -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Tutores laborales</h2>
            <div id="tutores-container">
                <div class="tutor-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Nombre</label>
                        <input type="text" name="tutores[0][nombre]"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Email</label>
                        <input type="email" name="tutores[0][email]"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Teléfono</label>
                        <input type="text" name="tutores[0][telefono]"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                </div>
            </div>
            <button type="button" onclick="addTutor()" class="text-blue-400 hover:text-blue-300 text-sm">
                + Añadir tutor
            </button>
        </div>

        <!-- Convenios -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-white mb-4">Convenios</h2>
            <div id="convenios-container">
                <div class="convenio-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Ciclo</label>
                        <select name="convenios[0][ciclo_id]"
                                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach($ciclos as $c)
                                <option value="{{ $c->id }}">{{ $c->familia->nombre }} — {{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Curso académico</label>
                        <input type="text" name="convenios[0][curso_academico]" placeholder="26/27"
                               class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeConvenio(this)" class="text-red-400 hover:text-red-300 text-sm">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addConvenio()" class="text-blue-400 hover:text-blue-300 text-sm">
                + Añadir convenio
            </button>
        </div>

        <button type="submit" id="submit-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
            Crear empresa
        </button>
        <a href="{{ route('empresas.index') }}" class="text-gray-400 hover:text-white ml-4">Cancelar</a>
    </form>
</div>

<script>
let tutorIndex = 1;
let convenioIndex = 1;

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

function addConvenio() {
    const container = document.getElementById('convenios-container');
    const row = document.createElement('div');
    row.className = 'convenio-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3';
    let options = '<option value="">Seleccionar...</option>';
    @foreach($ciclos as $c)
        options += '<option value="{{ $c->id }}">{{ $c->familia->nombre }} — {{ $c->nombre }}</option>';
    @endforeach
    row.innerHTML = `
        <div>
            <select name="convenios[${convenioIndex}][ciclo_id]"
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
                ${options}
            </select>
        </div>
        <div>
            <input type="text" name="convenios[${convenioIndex}][curso_academico]" placeholder="26/27"
                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white text-sm">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="removeConvenio(this)" class="text-red-400 hover:text-red-300 text-sm">
                Eliminar
            </button>
        </div>
    `;
    container.appendChild(row);
    convenioIndex++;
}

function removeConvenio(btn) {
    btn.closest('.convenio-row').remove();
}
</script>
@endsection
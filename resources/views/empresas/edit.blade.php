<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('empresas.show', $empresa) }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Editar Empresa: {{ $empresa->nombre }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Modificación de datos fiscales y tutores laborales</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Se encontraron errores en el formulario
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Datos Fiscales Card -->
            <div class="stitch-card p-6 sm:p-8 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Datos Fiscales y de Contacto
                    </h3>
                    <p class="text-xs text-slate-500">Información principal de la empresa</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="nombre" class="stitch-label">Nombre o Razón Social *</label>
                        <input id="nombre" type="text" name="nombre" required value="{{ old('nombre', $empresa->nombre) }}"
                               placeholder="Ej. Acme Ibérica S.L."
                               class="stitch-input @error('nombre') border-rose-300 ring-rose-200 @enderror">
                        @error('nombre') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="cif" class="stitch-label">CIF / NIF *</label>
                        <input id="cif" type="text" name="cif" required value="{{ old('cif', $empresa->cif) }}"
                               placeholder="Ej. B12345678"
                               class="stitch-input uppercase @error('cif') border-rose-300 ring-rose-200 @enderror">
                        @error('cif') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="direccion" class="stitch-label">Dirección Completa</label>
                        <input id="direccion" type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                               placeholder="Calle, número, polígono, código postal, localidad..."
                               class="stitch-input @error('direccion') border-rose-300 ring-rose-200 @enderror">
                        @error('direccion') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="telefono" class="stitch-label">Teléfono</label>
                        <input id="telefono" type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                               placeholder="958 000 000"
                               class="stitch-input @error('telefono') border-rose-300 ring-rose-200 @enderror">
                        @error('telefono') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="stitch-label">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $empresa->email) }}"
                               placeholder="contacto@empresa.com"
                               class="stitch-input @error('email') border-rose-300 ring-rose-200 @enderror">
                        @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="responsable_nombre" class="stitch-label">Representante / Responsable Legal</label>
                        <input id="responsable_nombre" type="text" name="responsable_nombre" value="{{ old('responsable_nombre', $empresa->responsable_nombre) }}"
                               placeholder="Nombre y apellidos"
                               class="stitch-input @error('responsable_nombre') border-rose-300 ring-rose-200 @enderror">
                        @error('responsable_nombre') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="responsable_dni" class="stitch-label">DNI / NIE del Representante</label>
                        <input id="responsable_dni" type="text" name="responsable_dni" value="{{ old('responsable_dni', $empresa->responsable_dni) }}"
                               placeholder="12345678Z"
                               class="stitch-input uppercase @error('responsable_dni') border-rose-300 ring-rose-200 @enderror">
                        @error('responsable_dni') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Tutores Laborales Card -->
            <div class="stitch-card p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold font-display text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Tutores Laborales
                        </h3>
                        <p class="text-xs text-slate-500">Gestión de tutores de empresa vinculados</p>
                    </div>
                    <button type="button" onclick="addTutor()" class="stitch-btn-secondary text-xs py-2 px-3">
                        <svg class="w-4 h-4 mr-1 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Añadir Tutor
                    </button>
                </div>

                <div id="tutores-container" class="space-y-4">
                    @forelse($empresa->tutoresLaborales as $idx => $tutor)
                    <div class="tutor-row p-4 rounded-xl border border-slate-200 bg-slate-50/70 relative space-y-3">
                        <input type="hidden" name="tutores[{{ $idx }}][id]" value="{{ $tutor->id }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 tutor-number">Tutor #{{ $idx + 1 }}</span>
                            <button type="button" onclick="removeTutor(this)" class="text-rose-500 hover:text-rose-700 text-xs font-medium">
                                Eliminar
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="stitch-label">Nombre Completo *</label>
                                <input type="text" name="tutores[{{ $idx }}][nombre]" value="{{ old("tutores.$idx.nombre", $tutor->nombre) }}" placeholder="Nombre del tutor" class="stitch-input">
                            </div>
                            <div>
                                <label class="stitch-label">DNI / NIE *</label>
                                <input type="text" name="tutores[{{ $idx }}][dni]" value="{{ old("tutores.$idx.dni", $tutor->dni) }}" placeholder="12345678Z" class="stitch-input uppercase">
                            </div>
                            <div>
                                <label class="stitch-label">Email</label>
                                <input type="email" name="tutores[{{ $idx }}][email]" value="{{ old("tutores.$idx.email", $tutor->email) }}" placeholder="tutor@empresa.com" class="stitch-input">
                            </div>
                            <div>
                                <label class="stitch-label">Teléfono</label>
                                <input type="text" name="tutores[{{ $idx }}][telefono]" value="{{ old("tutores.$idx.telefono", $tutor->telefono) }}" placeholder="600 000 000" class="stitch-input">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="tutor-row p-4 rounded-xl border border-slate-200 bg-slate-50/70 relative space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 tutor-number">Tutor #1</span>
                            <button type="button" onclick="removeTutor(this)" class="text-rose-500 hover:text-rose-700 text-xs font-medium">
                                Eliminar
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="stitch-label">Nombre Completo *</label>
                                <input type="text" name="tutores[0][nombre]" placeholder="Nombre del tutor" class="stitch-input">
                            </div>
                            <div>
                                <label class="stitch-label">DNI / NIE *</label>
                                <input type="text" name="tutores[0][dni]" placeholder="12345678Z" class="stitch-input uppercase">
                            </div>
                            <div>
                                <label class="stitch-label">Email</label>
                                <input type="email" name="tutores[0][email]" placeholder="tutor@empresa.com" class="stitch-input">
                            </div>
                            <div>
                                <label class="stitch-label">Teléfono</label>
                                <input type="text" name="tutores[0][telefono]" placeholder="600 000 000" class="stitch-input">
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('empresas.show', $empresa) }}" class="stitch-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" id="submit-btn" class="stitch-btn-primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Actualizar Empresa
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    let tutorIndex = {{ max($empresa->tutoresLaborales->count(), 1) }};

    function addTutor() {
        const container = document.getElementById('tutores-container');
        const count = container.querySelectorAll('.tutor-row').length + 1;
        const row = document.createElement('div');
        row.className = 'tutor-row p-4 rounded-xl border border-slate-200 bg-slate-50/70 relative space-y-3';
        row.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 tutor-number">Tutor #${count}</span>
                <button type="button" onclick="removeTutor(this)" class="text-rose-500 hover:text-rose-700 text-xs font-medium">
                    Eliminar
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="stitch-label">Nombre Completo *</label>
                    <input type="text" name="tutores[${tutorIndex}][nombre]" placeholder="Nombre del tutor" class="stitch-input">
                </div>
                <div>
                    <label class="stitch-label">DNI / NIE *</label>
                    <input type="text" name="tutores[${tutorIndex}][dni]" placeholder="12345678Z" class="stitch-input uppercase">
                </div>
                <div>
                    <label class="stitch-label">Email</label>
                    <input type="email" name="tutores[${tutorIndex}][email]" placeholder="tutor@empresa.com" class="stitch-input">
                </div>
                <div>
                    <label class="stitch-label">Teléfono</label>
                    <input type="text" name="tutores[${tutorIndex}][telefono]" placeholder="600 000 000" class="stitch-input">
                </div>
            </div>
        `;
        container.appendChild(row);
        tutorIndex++;
    }

    function removeTutor(btn) {
        const container = document.getElementById('tutores-container');
        const rows = container.querySelectorAll('.tutor-row');
        if (rows.length > 1) {
            btn.closest('.tutor-row').remove();
            container.querySelectorAll('.tutor-row').forEach((row, i) => {
                const num = row.querySelector('.tutor-number');
                if (num) num.textContent = 'Tutor #' + (i + 1);
            });
        } else {
            const row = btn.closest('.tutor-row');
            const inputs = row.querySelectorAll('input:not([type="hidden"])');
            inputs.forEach(input => input.value = '');
        }
    }
    </script>
    @endpush
</x-app-layout>
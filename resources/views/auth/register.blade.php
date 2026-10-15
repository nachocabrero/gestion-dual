<x-guest-layout :centered="true" maxWidth="max-w-2xl">
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0048FE] border border-blue-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Portal del Alumnado
            </span>
            <span class="text-xs text-slate-400 font-medium">IES Hermenegildo Lanz</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-slate-900 tracking-tight">Registro de Alumnos</h2>
        <p class="text-sm text-slate-500 mt-1.5">
            Introduce tus datos y selecciona los grupos en los que estás matriculado para solicitar tu cuenta.
        </p>
    </div>

    <!-- Mensaje explicativo de rol exclusivo -->
    <div class="mb-6 p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs text-slate-600">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Registro para <strong>alumnado</strong>. ¿Eres profesor/a? <a href="{{ route('teacher.register') }}" class="font-bold underline text-[#0048FE] hover:text-blue-800">Accede al registro docente aquí</a>.</span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Rol fijo para Alumno -->
        <input type="hidden" name="role" value="alumno">

        <!-- Grid: Nombre y Correo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nombre completo')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Ej. Juan Pérez García" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Correo electrónico')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="tu.correo@ejemplo.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
        </div>

        <!-- Grid: Contraseñas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <!-- Grupos en los que está matriculado y Prácticas de 1º (si 2º) -->
        <div x-data="{
            selectedGrupos: {{ json_encode(array_map('strval', old('grupos', []))) }},
            gruposSegundo: {{ json_encode($grupos->filter(fn($g) => (int)$g->numero === 2 || str_contains($g->nombre, '2º') || str_contains($g->nombre, '2'))->pluck('id')->map(fn($id) => (string)$id)->values()) }},
            get tieneSegundo() {
                return this.selectedGrupos.some(id => this.gruposSegundo.includes(String(id)));
            }
        }" class="space-y-4">
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <x-input-label for="grupos" :value="__('Grupos en los que estás matriculado')" class="font-bold text-slate-900" />
                    </div>
                    <span class="text-xs text-indigo-600 font-semibold bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-full">
                        Selección múltiple permitida
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-3">
                    Selecciona la clase o grupos a los que perteneces en el curso académico actual:
                </p>

                @if(isset($grupos) && count($grupos) > 0)
                    <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100 bg-white p-2.5 rounded-xl border border-slate-200 shadow-inner">
                        @foreach($grupos as $grupo)
                            <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer text-sm">
                                <input type="checkbox" name="grupos[]" value="{{ $grupo->id }}"
                                    x-model="selectedGrupos"
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">{{ $grupo->nombre }}</span>
                                        @if((int)$grupo->numero === 2 || str_contains($grupo->nombre, '2º') || str_contains($grupo->nombre, '2'))
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded">2º Curso</span>
                                        @endif
                                    </div>
                                    @if($grupo->linea?->ciclo)
                                        <span class="text-xs text-slate-500 block truncate">
                                            {{ $grupo->linea->ciclo->nombre }} — Turno {{ ucfirst($grupo->linea->turno ?? '') }}
                                        </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200">
                        No hay grupos disponibles en este momento. El administrador podrá asignártelos tras la validación.
                    </p>
                @endif
                <x-input-error :messages="$errors->get('grupos')" class="mt-2" />
                <x-input-error :messages="$errors->get('grupos.*')" class="mt-2" />

                <!-- Aviso informativo de Estado de Espera -->
                <div class="mt-3.5 flex items-start gap-2.5 p-3.5 bg-amber-50 border border-amber-200/90 rounded-xl text-xs text-amber-900">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="leading-relaxed">
                        <strong class="font-bold">Estado de espera tras el registro:</strong> Tu cuenta quedará temporalmente inactiva hasta que un administrador verifique tu matrícula y acepte tu registro. Te habilitará el acceso una vez comprobada la información.
                    </div>
                </div>
            </div>

            <!-- Prácticas de 1º curso para alumnos matriculados en 2º -->
            <div x-show="tieneSegundo" x-transition class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-blue-50/70 to-indigo-50/40 border border-blue-200 space-y-3.5" style="display: none;">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-[#0048FE] text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">¿Hiciste prácticas en 1º curso? (Opcional)</h3>
                        <p class="text-xs text-slate-500">Al matricularte en 2º curso, puedes indicar la empresa donde hiciste las prácticas de 1º y cuántas horas completaste.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <x-input-label for="reg_empresa_primero" :value="__('Empresa de prácticas (1º curso)')" class="font-bold text-slate-800 text-xs" />
                        <x-text-input id="reg_empresa_primero" class="block mt-1 w-full text-sm" type="text" name="empresa_practicas_primero" :value="old('empresa_practicas_primero')" placeholder="Ej. NTT Data, Everis..." />
                        <x-input-error :messages="$errors->get('empresa_practicas_primero')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="reg_horas_primero" :value="__('Horas de prácticas en 1º')" class="font-bold text-slate-800 text-xs" />
                        <x-text-input id="reg_horas_primero" class="block mt-1 w-full text-sm" type="number" min="0" max="2000" name="horas_practicas_primero" :value="old('horas_practicas_primero')" placeholder="Ej. 100" />
                        <x-input-error :messages="$errors->get('horas_practicas_primero')" class="mt-1" />
                    </div>
                </div>
            </div>
        </div>

        <!-- RGPD Consent & Privacy Policy -->
        <div class="space-y-2.5 pt-1">
            <div class="flex items-start">
                <input type="checkbox" id="consent_rgpd" name="consent_rgpd" required class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="consent_rgpd" class="ml-2.5 text-xs text-slate-700 font-medium">
                    Consiento el tratamiento de mis datos personales para la gestión académica del centro conforme al RGPD.
                </label>
            </div>
            <x-input-error :messages="$errors->get('consent_rgpd')" class="mt-1" />

            <div class="flex items-start">
                <input type="checkbox" id="privacy_policy" name="privacy_policy" required class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="privacy_policy" class="ml-2.5 text-xs text-slate-700 font-medium">
                    He leído y acepto el <a href="{{ route('privacy') }}" target="_blank" class="underline text-indigo-600 hover:text-indigo-800 font-bold">Aviso de Privacidad</a> del IES Hermenegildo Lanz.
                </label>
            </div>
            <x-input-error :messages="$errors->get('privacy_policy')" class="mt-1" />
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a class="text-xs font-medium text-slate-600 hover:text-indigo-600 transition-colors" href="{{ route('login') }}">
                ← {{ __('¿Ya tienes cuenta? Iniciar sesión') }}
            </a>

            <button type="submit" class="px-5 py-2.5 bg-[#0048FE] hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-600/30 transition-all duration-200">
                {{ __('Enviar solicitud de registro') }}
            </button>
        </div>
    </form>
</x-guest-layout>
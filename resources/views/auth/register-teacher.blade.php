<x-guest-layout :centered="true" maxWidth="max-w-2xl">
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Portal del Profesorado
            </span>
            <span class="text-xs text-slate-400 font-medium">IES Hermenegildo Lanz</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-slate-900 tracking-tight">Registro de Profesorado</h2>
        <p class="text-sm text-slate-500 mt-1.5">
            Introduce tus datos para solicitar tu alta docente en la plataforma de gestión académica y FCT/Dual del centro.
        </p>
    </div>

    <!-- Mensaje explicativo de validación de cuentas docentes -->
    <div class="mb-6 p-4 rounded-xl bg-amber-50/80 border border-amber-200/90 text-xs text-amber-900 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="leading-relaxed">
            <strong class="font-bold">Verificación docente requerida:</strong> Por motivos de seguridad y protección de datos del alumnado, las cuentas del profesorado quedan en <span class="underline decoration-amber-400 font-semibold">estado de espera</span> hasta ser verificadas y activadas manualmente por la dirección o administración del centro.
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.register.store') }}" class="space-y-5">
        @csrf

        <!-- Grid: Nombre y Correo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nombre y apellidos')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Ej. Francisco García Romero" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Correo electrónico')" class="font-bold text-slate-800 text-sm" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="fgarcia@ieshlanz.es" />
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

        <!-- Sección de Datos Docentes -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <h3 class="font-bold text-slate-900 text-sm">Información Docente y Departamento</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Familia Profesional -->
                <div>
                    <x-input-label for="familia_id" :value="__('Familia Profesional')" class="font-bold text-slate-800 text-sm" />
                    <select id="familia_id" name="familia_id" class="block mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800">
                        <option value="">-- Selecciona familia profesional (opcional) --</option>
                        @foreach($familias as $familia)
                            <option value="{{ $familia->id }}" {{ old('familia_id') == $familia->id ? 'selected' : '' }}>
                                {{ $familia->nombre }} ({{ $familia->codigo }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('familia_id')" class="mt-1" />
                </div>

                <!-- Especialidad -->
                <div>
                    <x-input-label for="especialidad" :value="__('Especialidad docente')" class="font-bold text-slate-800 text-sm" />
                    <x-text-input id="especialidad" class="block mt-1 w-full" type="text" name="especialidad" :value="old('especialidad')" placeholder="Ej. Informática, FOL, Automoción..." />
                    <x-input-error :messages="$errors->get('especialidad')" class="mt-1" />
                </div>
            </div>

            <!-- Tutor de Grupo (Optativo) -->
            <div class="pt-2 border-t border-slate-200">
                <x-input-label for="tutor_grupo_id" :value="__('Grupo del que eres tutor (Optativo)')" class="font-bold text-slate-800 text-sm" />
                <p class="text-xs text-slate-500 mt-0.5 mb-1.5">Si eres tutor/a lectivo o principal de algún grupo, selecciónalo a continuación.</p>
                <select id="tutor_grupo_id" name="tutor_grupo_id" class="block mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-800">
                    <option value="">-- Ninguno (No soy tutor de aula) --</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ old('tutor_grupo_id') == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->nombre }} @if($grupo->linea?->ciclo) ({{ $grupo->linea->ciclo->nombre }}) @endif
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tutor_grupo_id')" class="mt-1" />
            </div>

            <!-- Grupos a los que imparte clase (Optativo) -->
            <div class="pt-2 border-t border-slate-200">
                <x-input-label :value="__('Grupos a los que impartes clase (Optativo)')" class="font-bold text-slate-800 text-sm" />
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Marca los grupos en los que tienes docencia para habilitar el seguimiento de sus alumnos y prácticas.</p>
                
                <div class="max-h-52 overflow-y-auto p-3 bg-white rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-2 shadow-inner">
                    @forelse($grupos as $grupo)
                        <label class="flex items-start gap-2.5 p-2 rounded-lg border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 cursor-pointer transition-colors text-xs text-slate-700">
                            <input type="checkbox" name="grupos[]" value="{{ $grupo->id }}"
                                {{ in_array($grupo->id, (array) old('grupos', [])) ? 'checked' : '' }}
                                class="mt-0.5 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-slate-900 block truncate">{{ $grupo->nombre }}</span>
                                @if($grupo->linea?->ciclo)
                                    <span class="text-[11px] text-slate-500 block truncate">{{ $grupo->linea->ciclo->nombre }}</span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 py-2 col-span-2">No hay grupos activos disponibles en este momento.</p>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('grupos')" class="mt-1" />
                <x-input-error :messages="$errors->get('grupos.*')" class="mt-1" />
            </div>
        </div>

        <!-- RGPD Consent & Privacy Policy -->
        <div class="space-y-2.5 pt-1">
            <div class="flex items-start">
                <input type="checkbox" id="consent_rgpd" name="consent_rgpd" required class="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="consent_rgpd" class="ml-2.5 text-xs text-slate-700 font-medium">
                    Consiento el tratamiento de mis datos personales para la función docente y gestión académica del centro conforme al RGPD.
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
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-4 text-xs">
                <a class="font-medium text-slate-600 hover:text-indigo-600 transition-colors" href="{{ route('login') }}">
                    ← {{ __('¿Ya tienes cuenta? Iniciar sesión') }}
                </a>
                <span class="text-slate-300">|</span>
                <a class="font-medium text-[#0048FE] hover:underline" href="{{ route('register') }}">
                    {{ __('Portal Alumnado') }}
                </a>
            </div>

            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-600/30 transition-all duration-200">
                {{ __('Solicitar alta docente') }}
            </button>
        </div>
    </form>
</x-guest-layout>

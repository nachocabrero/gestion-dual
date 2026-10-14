<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold font-display text-slate-900">Crear cuenta</h2>
        <p class="text-sm text-slate-500 mt-1">Completa el formulario para registrarte en el sistema de gestión académica</p>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ role: '{{ old('role', 'alumno') }}' }">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Ej. Juan Pérez García" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="tu.correo@ejemplo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Tipo de usuario')" />
            <select id="role" name="role" x-model="role" required class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="alumno" {{ old('role', 'alumno') == 'alumno' ? 'selected' : '' }}>Alumno/a</option>
                <option value="profesor" {{ old('role') == 'profesor' ? 'selected' : '' }}>Profesor/a</option>
                <option value="empresa" {{ old('role') == 'empresa' ? 'selected' : '' }}>Empresa</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
            <p class="text-xs text-slate-500 mt-1">Coordinador Dual y Administrador se gestionan internamente.</p>
        </div>

        <!-- Grupos (Solo visible cuando se selecciona Alumno/a) -->
        <div x-show="role === 'alumno'" x-transition class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div class="flex items-center justify-between mb-1">
                <x-input-label for="grupos" :value="__('Grupos en los que estás matriculado')" class="font-semibold text-slate-800" />
                <span class="text-xs text-indigo-600 font-medium bg-indigo-50 px-2 py-0.5 rounded-full">Selección múltiple</span>
            </div>
            <p class="text-xs text-slate-500 mb-3">
                Marca la casilla del grupo o grupos en los que estés matriculado para este curso:
            </p>

            @if(isset($grupos) && count($grupos) > 0)
                <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100 bg-white p-2 rounded-lg border border-slate-200">
                    @foreach($grupos as $grupo)
                        <label class="flex items-start gap-3 p-2 rounded-md hover:bg-slate-50 transition-colors cursor-pointer text-sm">
                            <input type="checkbox" name="grupos[]" value="{{ $grupo->id }}"
                                {{ (is_array(old('grupos')) && in_array($grupo->id, old('grupos'))) ? 'checked' : '' }}
                                class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-slate-900">{{ $grupo->nombre }}</span>
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
                <p class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    No hay grupos disponibles en este momento. El administrador te asignará al validar tu cuenta.
                </p>
            @endif
            <x-input-error :messages="$errors->get('grupos')" class="mt-2" />
            <x-input-error :messages="$errors->get('grupos.*')" class="mt-2" />

            <!-- Aviso estado de espera -->
            <div class="mt-3 flex items-start gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold">Estado de espera:</span> Al registrarte, tu cuenta quedará en estado de espera hasta que un administrador verifique tu matrícula y la apruebe para habilitar el acceso.
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- RGPD Consent & Privacy Policy -->
        <div class="mt-4 space-y-2">
            <div class="flex items-start">
                <input type="checkbox" id="consent_rgpd" name="consent_rgpd" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="consent_rgpd" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                    Consiento el tratamiento de mis datos personales para la gestión académica del IES Hermenegildo Lanz conforme al RGPD.
                </label>
            </div>
            <x-input-error :messages="$errors->get('consent_rgpd')" class="mt-1" />

            <div class="flex items-start">
                <input type="checkbox" id="privacy_policy" name="privacy_policy" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="privacy_policy" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                    He leído y acepto el <a href="{{ route('privacy') }}" target="_blank" class="underline text-indigo-600 hover:text-indigo-800 font-medium">Aviso de Privacidad</a> del centro.
                </label>
            </div>
            <x-input-error :messages="$errors->get('privacy_policy')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-indigo-600 hover:text-indigo-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('¿Ya estás registrado?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
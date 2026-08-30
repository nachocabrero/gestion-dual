<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Crear cuenta</h2>
        <p class="text-sm text-gray-500 mt-1">Completa el formulario para registrarte en el sistema</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Tipo de usuario')" />
            <select id="role" name="role" required class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Selecciona tu tipo</option>
                <option value="alumno" {{ old('role') == 'alumno' ? 'selected' : '' }}>Alumno/a</option>
                <option value="profesor" {{ old('role') == 'profesor' ? 'selected' : '' }}>Profesor/a</option>
                <option value="empresa" {{ old('role') == 'empresa' ? 'selected' : '' }}>Empresa</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
            <p class="text-xs text-gray-500 mt-1">Coordinador Dual se asigna por el administrador.</p>
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

        <!-- RGPD Consent -->
        <div class="mt-4">
            <div class="flex items-start">
                <input type="checkbox" id="consent_rgpd" name="consent_rgpd" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="consent_rgpd" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                    Acepto el <a href="{{ route('privacy') }}" target="_blank" class="underline">Aviso de Privacidad</a> y consiento el tratamiento de mis datos personales conforme al RGPD.
                </label>
            </div>
            <x-input-error :messages="$errors->get('consent_rgpd')" class="mt-2" />
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
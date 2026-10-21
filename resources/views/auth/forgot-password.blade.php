<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">¿Olvidaste tu contraseña?</h2>
        <p class="text-sm text-gray-600 mt-2">
            Indica tu dirección de correo electrónico y te enviaremos un enlace seguro para que puedas restablecerla y acceder de nuevo al sistema.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="usuario@ieshlanz.es" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm font-medium text-slate-600 hover:text-indigo-600 hover:underline flex items-center gap-1" href="{{ route('login') }}">
                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a Iniciar sesión
            </a>

            <x-primary-button>
                Enviar enlace
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

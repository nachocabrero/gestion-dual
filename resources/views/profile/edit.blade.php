<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- RGPD: Derecho de supresión -->
            <div class="p-4 sm:p-8 bg-red-50 dark:bg-red-900/20 shadow sm:rounded-lg border border-red-200 dark:border-red-800">
                <div class="max-w-xl">
                    <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">
                        ⚠️ Datos Personales (RGPD)
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Conforme al Reglamento General de Protección de Datos (RGPD), tienes derecho a solicitar la supresión de tus datos personales.
                        Al solicitarlo, tu cuenta será desactivada y se iniciará el proceso de eliminación en un plazo de 30 días.
                    </p>
                    <form method="post" action="{{ route('profile.deletion-request') }}" class="inline">
                        @csrf
                        <x-primary-button class="bg-red-600 hover:bg-red-700 text-white">
                            Solicitar eliminación de datos
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
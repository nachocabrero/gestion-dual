<div x-data="cookieBanner()" x-show="show" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     class="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl border border-gray-200">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Utilizamos cookies</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Este sitio web utiliza cookies técnicas necesarias para su funcionamiento.
                        También utiliza cookies de preferencias para recordar tus elecciones.
                        <a href="{{ route('cookies') }}" class="text-indigo-600 hover:underline font-medium">Más información</a>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button @click="accept()"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Aceptar todas
                        </button>
                        <button @click="reject()"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Solo necesarias
                        </button>
                        <button @click="showConfig = true"
                                class="inline-flex items-center px-4 py-2 text-indigo-600 text-sm font-medium hover:bg-indigo-50 rounded-lg transition-colors">
                            Configurar
                        </button>
                    </div>
                </div>
                <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Cookie Configuration Panel -->
        <div x-show="showConfig" x-transition class="border-t border-gray-200 bg-gray-50">
            <div class="p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Configuración de cookies</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Cookies técnicas</p>
                            <p class="text-xs text-gray-500">Necesarias para el funcionamiento del sistema</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Siempre activas
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Cookies de preferencias</p>
                            <p class="text-xs text-gray-500">Recordar tus elecciones de cookies</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="preferences" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button @click="showConfig = false; accept()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        Guardar configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cookieBanner() {
    return {
        show: false,
        showConfig: false,
        preferences: false,
        init() {
            const consent = document.cookie.split(';').find(c => c.trim().startsWith('cookie_consent='));
            if (!consent) {
                this.show = true;
            }
        },
        async accept() {
            this.show = false;
            document.cookie = "cookie_consent=true; max-age=" + (90 * 24 * 60 * 60) + "; path=/; SameSite=Lax";
            try {
                await fetch('{{ route("cookies.accept") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch (e) {
                // Ignore, we already set the local cookie
            }
        },
        async reject() {
            this.show = false;
            document.cookie = "cookie_consent=true; max-age=" + (90 * 24 * 60 * 60) + "; path=/; SameSite=Lax";
            try {
                await fetch('{{ route("cookies.reject") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch (e) {
                // Ignore
            }
        }
    }
}
</script>
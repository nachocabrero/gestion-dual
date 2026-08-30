<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold font-display text-slate-900">Privacidad y Protección de Datos</h2>
        <p class="text-sm text-slate-500 mt-2">Antes de continuar, necesitamos tu consentimiento</p>
    </div>

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-8 text-sm text-blue-800 space-y-3">
        <p>De acuerdo con el Reglamento General de Protección de Datos (RGPD) y la LOPDGDD, te informamos que el IES Hermenegildo Lanz tratará tus datos para la <strong>gestión académica, evaluación y prácticas</strong>.</p>
        <p>Tus datos no serán cedidos a terceros salvo obligación legal o necesidades de gestión de prácticas FCT/Dual con empresas colaboradoras.</p>
        <p>Puedes ejercer tus derechos (acceso, rectificación, supresión) contactando con el centro.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('rgpd.accept') }}" class="space-y-5">
        @csrf
        
        <div class="flex items-start gap-3">
            <div class="flex items-center h-5">
                <input type="checkbox" id="accept_privacy" name="privacy_policy" required class="w-4 h-4 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
            </div>
            <label for="accept_privacy" class="text-sm text-slate-600">
                He leído y acepto la <a href="{{ route('privacy') }}" target="_blank" class="text-[#0048FE] font-medium hover:underline">Política de Privacidad y Aviso Legal</a>.
            </label>
        </div>

        <div class="flex items-start gap-3">
            <div class="flex items-center h-5">
                <input type="checkbox" id="accept_newsletter" name="accept_newsletter" class="w-4 h-4 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
            </div>
            <label for="accept_newsletter" class="text-sm text-slate-600">
                Acepto recibir comunicaciones informativas del centro (opcional).
            </label>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-[#0048FE] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0048FE] transition-colors">
                Aceptar y Continuar a mi Panel
            </button>
        </div>
    </form>
</x-guest-layout>
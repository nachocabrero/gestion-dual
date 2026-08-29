<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <p class="font-bold text-lg mb-2">Aviso de Privacidad — IES Hermenegildo Lanz</p>

        <p class="mb-2">De acuerdo con el Reglamento General de Protección de Datos (RGPD) (UE) 2016/679 y la Ley Orgánica 3/2018 (LOPDGDD), te informamos de lo siguiente:</p>

        <h3 class="font-semibold mt-4 mb-1">1. Responsable del tratamiento</h3>
        <p>IES Hermenegildo Lanz, con domicilio en Granada.</p>

        <h3 class="font-semibold mt-4 mb-1">2. Finalidad del tratamiento</h3>
        <p>Gestión académica del alumnado y profesorado: matrícula, calificaciones, prácticas con empresas, proyectos, comunicaciones internas.</p>

        <h3 class="font-semibold mt-4 mb-1">3. Base legal</h3>
        <p>Consentimiento del interesado (Art. 6.1.a RGPD) y cumplimiento de obligaciones legales (Art. 6.1.c RGPD).</p>

        <h3 class="font-semibold mt-4 mb-1">4. Destinatarios</h3>
        <p>No se cederán datos a terceros salvo obligación legal. Las empresas donde se realicen prácticas tendrán acceso limitado a datos necesarios para la gestión.</p>

        <h3 class="font-semibold mt-4 mb-1">5. Derechos</h3>
        <p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad contactando con el centro.</p>

        <h3 class="font-semibold mt-4 mb-1">6. Conservación</h3>
        <p>Los datos se conservarán mientras dure la relación académica y los plazos legales aplicables.</p>

        <h3 class="font-semibold mt-4 mb-1">7. Medidas de seguridad</h3>
        <p>Se aplican medidas técnicas y organizativas adecuadas para proteger los datos personales.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('rgpd.accept') }}">
        @csrf
        <div class="flex items-center justify-start gap-2">
            <x-input-label for="accept_privacy" class="sr-only">Acepto privacidad</x-input-label>
            <input type="checkbox" id="accept_privacy" name="privacy_policy" required class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="accept_privacy" class="text-sm text-gray-600 dark:text-gray-400">
                He leído y acepto el <a href="{{ route('privacy') }}" target="_blank" class="underline">Aviso de Privacidad</a>
            </label>
        </div>

        <div class="flex items-center justify-start gap-2 mt-2">
            <input type="checkbox" id="accept_newsletter" name="accept_newsletter" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="accept_newsletter" class="text-sm text-gray-600 dark:text-gray-400">
                Acepto recibir comunicaciones del centro (opcional)
            </label>
        </div>

        <div class="flex items-center justify-center mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Aceptar y Continuar
            </button>
        </div>
    </form>
</x-guest-layout>
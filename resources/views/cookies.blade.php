@extends('layouts.public')

@section('content')
<div class="bg-white py-16 sm:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-3xl font-extrabold font-display text-slate-900 sm:text-4xl">Política de Cookies</h1>
            <p class="mt-4 text-lg text-slate-500">Última actualización: Agosto 2026</p>
        </div>

        <div class="prose prose-slate prose-lg max-w-none text-slate-600">
            <p>En cumplimiento de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y Comercio Electrónico (LSSI-CE), le informamos de que el IES Hermenegildo Lanz utiliza cookies en esta aplicación web para asegurar su correcto funcionamiento técnico y mejorar la experiencia del usuario.</p>

            <h2>¿Qué es una cookie?</h2>
            <p>Una cookie es un pequeño fichero de texto que se almacena en su navegador cuando visita casi cualquier página web. Su utilidad es que la web sea capaz de recordar su visita cuando vuelva a navegar por esa página. Las cookies suelen almacenar información de carácter técnico, preferencias del sitio, estado de la sesión, etc.</p>

            <h2>Cookies que utiliza esta aplicación</h2>
            <p>Esta plataforma es una herramienta de gestión interna y utiliza exclusivamente cookies técnicas y de sesión que son <strong>estrictamente necesarias</strong> para permitir la navegación por el sitio, mantener la seguridad de las peticiones y gestionar la sesión del usuario.</p>
            
            <div class="overflow-x-auto mt-6 mb-8">
                <table class="min-w-full divide-y divide-slate-200 border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre de la Cookie</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Duración</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Finalidad</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><code>ieshlanz_session</code></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Propia / Técnica</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Sesión (2 horas)</td>
                            <td class="px-6 py-4 text-sm text-slate-500">Mantiene la sesión del usuario activa para permitir la navegación por áreas restringidas.</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><code>XSRF-TOKEN</code></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Propia / Seguridad</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Sesión (2 horas)</td>
                            <td class="px-6 py-4 text-sm text-slate-500">Protege a la aplicación de ataques de falsificación de peticiones en sitios cruzados (CSRF).</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><code>cookie_consent</code></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Propia / Preferencias</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">90 días</td>
                            <td class="px-6 py-4 text-sm text-slate-500">Recuerda si el usuario ha aceptado o configurado el aviso de cookies para no volver a mostrarlo.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2>Consentimiento y Gestión de Cookies</h2>
            <p>Dado que las cookies utilizadas por esta plataforma (<code>ieshlanz_session</code> y <code>XSRF-TOKEN</code>) son estrictamente necesarias para la prestación de un servicio expresamente solicitado por el usuario (acceso a la plataforma académica), <strong>no requieren del consentimiento previo</strong> del usuario según el artículo 22.2 de la LSSI. No obstante, le ofrecemos la información por razones de total transparencia.</p>

            <p>Puede usted permitir, bloquear o eliminar las cookies instaladas en su equipo mediante la configuración de las opciones del navegador instalado en su ordenador. Sin embargo, advertimos que la desactivación de estas cookies impedirá el correcto funcionamiento de la plataforma y no le permitirá iniciar sesión.</p>
        </div>
    </div>
</div>
@endsection
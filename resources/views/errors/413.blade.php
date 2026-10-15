<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error 413 — Archivo Demasiado Grande | IES Hermenegildo Lanz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 py-4 px-6 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 p-1 bg-white rounded-xl shadow-md border border-slate-200 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/icon avatar hlanz.webp') }}" alt="IES Hermenegildo Lanz" class="h-8 w-8 object-contain">
                </div>
                <div>
                    <h1 class="text-base font-bold font-display text-slate-900 leading-tight">IES Hermenegildo Lanz</h1>
                    <p class="text-xs text-slate-500">Gestión Académica y FCT</p>
                </div>
            </a>
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('profile.edit') }}" class="stitch-btn-secondary text-xs py-2 px-3.5">
                    Mi Perfil
                </a>
                <a href="{{ route('dashboard') }}" class="stitch-btn-primary text-xs py-2 px-3.5">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="stitch-btn-primary text-xs py-2 px-3.5">
                    Iniciar Sesión
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-8">
        <div class="max-w-2xl w-full stitch-card p-6 sm:p-10 text-center space-y-6 shadow-xl border border-slate-200">
            <!-- Icon Badge -->
            <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <!-- Error Code & Title -->
            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    HTTP Error 413 • Payload Too Large
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-slate-900 tracking-tight">
                    El archivo es demasiado grande o su formato no es compatible
                </h2>
                <p class="text-sm sm:text-base text-slate-600 max-w-lg mx-auto">
                    El servidor ha rechazado la subida porque la carga recibida supera el tamaño máximo autorizado o el formato no es el adecuado.
                </p>
            </div>

            <!-- Information Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left pt-2">
                <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-[#0048FE] font-bold text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Foto de Perfil (Avatar)</span>
                    </div>
                    <ul class="text-xs text-slate-600 space-y-1.5">
                        <li>• <strong>Tamaño máximo:</strong> 2 MB (2.048 KB).</li>
                        <li>• <strong>Formatos permitidos:</strong> JPG, PNG o WebP.</li>
                        <li>• La imagen se optimiza y redimensiona automáticamente al procesarse.</li>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-indigo-700 font-bold text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Curriculum Vitae / Documentos</span>
                    </div>
                    <ul class="text-xs text-slate-600 space-y-1.5">
                        <li>• <strong>Tamaño máximo:</strong> 5 MB (5.120 KB).</li>
                        <li>• <strong>Formatos admitidos:</strong> PDF, JPG, PNG o WebP.</li>
                        <li>• Asegúrate de que el documento no esté dañado o bloqueado.</li>
                    </ul>
                </div>
            </div>

            <!-- Suggestions -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs text-slate-600 space-y-2">
                <p class="font-bold text-slate-800 flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ¿Cómo puedo solucionarlo?
                </p>
                <p>1. <strong>Comprimir el archivo:</strong> Si tu imagen o PDF supera el límite de peso, utiliza un compresor en línea o reduce la resolución antes de adjuntarlo.</p>
                <p>2. <strong>Verificar el formato:</strong> Convierte el archivo a uno de los formatos estándar soportados (JPG, PNG, WebP para fotos o PDF para documentos).</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('profile.edit') }}" class="stitch-btn-primary w-full sm:w-auto py-2.5 px-6 text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Volver a Editar Mi Perfil</span>
                </a>
                <a href="{{ route('dashboard') }}" class="stitch-btn-secondary w-full sm:w-auto py-2.5 px-6 text-sm">
                    Ir al Dashboard
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        IES Hermenegildo Lanz &bull; Sistema de Gestión DUAL
    </footer>
</body>
</html>

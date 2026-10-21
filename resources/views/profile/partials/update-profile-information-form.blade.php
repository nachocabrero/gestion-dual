<section>
    <header>
        <h2 class="text-xl font-bold font-display text-slate-900 dark:text-white">
            Información del Perfil
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Actualiza la información de tu cuenta, datos de contacto, enlaces y archivos adjuntos.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <div x-data="profileEditForm()">
        <!-- Alerta de Error 413 o de Subida de Archivos del Servidor -->
        @if (session('error_413') || $errors->has('file_upload_error') || $errors->has('avatar') || $errors->has('cv'))
            @php
                $is413 = session('error_413') || $errors->has('file_upload_error') || Str::contains($errors->first('avatar'), '413') || Str::contains($errors->first('cv'), '413');
            @endphp
            @if($is413)
            <div class="mb-6 p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200 shadow-sm space-y-3">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-100 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-extrabold text-base text-amber-950 dark:text-amber-100 flex items-center gap-2">
                            <span>Aviso de Subida: Error 413 (Payload Too Large)</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200 font-bold">Archivo no procesado</span>
                        </h4>
                        <p class="text-xs sm:text-sm text-amber-900 dark:text-amber-200 leading-relaxed">
                            {{ session('error_413') ?? $errors->first('file_upload_error') ?? 'El servidor ha rechazado el archivo porque es demasiado grande o su formato no es el adecuado.' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs border-t border-amber-200 dark:border-amber-800/60">
                    <div class="p-3 bg-white/80 dark:bg-slate-900/60 rounded-xl border border-amber-200/80 dark:border-amber-800/40">
                        <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mb-1">
                            <span>📷 Foto de Perfil (Avatar)</span>
                        </p>
                        <p class="text-slate-600 dark:text-slate-300">• Límite máximo: <strong>2 MB</strong> (2048 KB)</p>
                        <p class="text-slate-600 dark:text-slate-300">• Formatos admitidos: <strong>JPG, PNG, WebP</strong></p>
                    </div>
                    <div class="p-3 bg-white/80 dark:bg-slate-900/60 rounded-xl border border-amber-200/80 dark:border-amber-800/40">
                        <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mb-1">
                            <span>📄 Curriculum / Documento</span>
                        </p>
                        <p class="text-slate-600 dark:text-slate-300">• Límite máximo: <strong>5 MB</strong> (5120 KB)</p>
                        <p class="text-slate-600 dark:text-slate-300">• Formatos admitidos: <strong>PDF, JPG, PNG, WebP</strong></p>
                    </div>
                </div>

                <p class="text-xs text-amber-800 dark:text-amber-300 font-medium">
                    💡 <strong>Sugerencia:</strong> Si tu imagen o documento supera el tamaño, por favor redimensiónala o utiliza un compresor antes de adjuntarla.
                </p>
            </div>
            @endif
        @endif

        <!-- Alerta Dinámica Capturada en Cliente (Error 413 / Formatos / Tamaños) -->
        <div x-show="generalError" x-transition class="mb-6 p-5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-300 dark:border-rose-700 text-rose-900 dark:text-rose-200 shadow-sm space-y-3" style="display: none;">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-rose-200 dark:bg-rose-800 text-rose-800 dark:text-rose-100 rounded-xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <h4 class="font-extrabold text-base text-rose-950 dark:text-rose-100 flex items-center gap-2">
                        <span x-text="isUploadError ? 'No se pudo guardar: Archivo no admitido' : 'Revisa los datos del formulario'"></span>
                        <span x-show="isUploadError" class="text-xs px-2 py-0.5 rounded-full bg-rose-200 dark:bg-rose-900 text-rose-900 dark:text-rose-200 font-bold">Error 413 / Archivo</span>
                    </h4>
                    <p x-text="generalError" class="text-xs sm:text-sm text-rose-900 dark:text-rose-200 leading-relaxed"></p>
                </div>
            </div>

            <div x-show="isUploadError" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs border-t border-rose-200 dark:border-rose-800/60">
                <div class="p-3 bg-white/80 dark:bg-slate-900/60 rounded-xl border border-rose-200/80 dark:border-rose-800/40">
                    <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mb-1">
                        <span>📷 Límite Avatar</span>
                    </p>
                    <p class="text-slate-600 dark:text-slate-300">Máximo: <strong>2 MB</strong> • Formato: <strong>JPG, PNG, WebP</strong></p>
                </div>
                <div class="p-3 bg-white/80 dark:bg-slate-900/60 rounded-xl border border-rose-200/80 dark:border-rose-800/40">
                    <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mb-1">
                        <span>📄 Límite Documento/CV</span>
                    </p>
                    <p class="text-slate-600 dark:text-slate-300">Máximo: <strong>5 MB</strong> • Formato: <strong>PDF, JPG, PNG, WebP</strong></p>
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data" @submit="handleSubmit($event)">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="name" value="Nombre completo" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="Correo electrónico" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-slate-700 dark:text-slate-300">
                            Tu dirección de correo electrónico no está verificada.

                            <button form="send-verification" class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Haz clic aquí para reenviar el correo de verificación.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-emerald-600 dark:text-emerald-400">
                                Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <x-input-label for="telefono" value="Teléfono de contacto" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono', $user->telefono)" placeholder="Ej. 612 345 678" />
                <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
            </div>

            <div>
                <x-input-label for="linkedin_url" value="Perfil de LinkedIn (Opcional)" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $user->linkedin_url)" placeholder="https://linkedin.com/in/tu-perfil" />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Opcional. Introduce la URL completa comenzando por https://</p>
                <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
            </div>

            <div>
                <x-input-label for="github_url" value="Perfil de GitHub (Opcional)" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full" :value="old('github_url', $user->github_url)" placeholder="https://github.com/tu-usuario" />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Opcional. Introduce la URL completa comenzando por https://</p>
                <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
            </div>

            <div>
                <x-input-label for="portfolio_url" value="Portfolio Externo / Web Personal (Opcional)" class="font-bold text-slate-800 dark:text-slate-200" />
                <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1 block w-full" :value="old('portfolio_url', $user->portfolio_url)" placeholder="https://tu-portfolio.com" />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Opcional. Si se deja en blanco no se mostrará en tu ficha de usuario.</p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_url')" />
            </div>

            @if($user->alumno)
                <div x-data="{ 
                    sinPracticas: {{ old('sin_practicas_primero', $user->alumno->sin_practicas_primero ? 'true' : 'false') }},
                    repitiendoModulos: {{ old('repitiendo_modulos_primero', $user->alumno->repitiendo_modulos_primero ? 'true' : 'false') }}
                }" class="p-6 rounded-2xl bg-gradient-to-br from-blue-50/80 via-indigo-50/50 to-slate-50 dark:bg-slate-800/60 border border-blue-200/80 dark:border-slate-700 space-y-5">
                    
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#0048FE] text-white flex items-center justify-center shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold font-display text-slate-900 dark:text-white">Prácticas Realizadas en 1º Curso y Situación Académica</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Configura tu estado respecto a las prácticas de 1º curso, convalidaciones y módulos pendientes.</p>
                            </div>
                        </div>
                        @if($user->alumno->esSegundoCurso())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                Matriculado en 2º
                            </span>
                        @endif
                    </div>

                    <!-- Anotación Informativa Importante -->
                    <div class="p-4 rounded-xl bg-white/90 dark:bg-slate-900/70 border border-indigo-100 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 space-y-2 shadow-sm">
                        <div class="flex items-center gap-2 font-bold text-indigo-900 dark:text-indigo-300">
                            <svg class="w-4 h-4 text-[#0048FE] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Información importante sobre la asignación de prácticas:</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-600 dark:text-slate-300">
                            <li><strong>Alumnos en 2º curso repitiendo:</strong> Puedes registrar la empresa y horas que completaste en 1º curso, aunque las realizaras hace 2 años (o en cursos anteriores).</li>
                            <li><strong>Repetición con módulos de 1º:</strong> Si estás repitiendo con módulos pendientes de 1º curso, según normativa académica <strong>este año no puedes realizar prácticas</strong>. Marca la casilla inferior para registrarlo.</li>
                            <li><strong>Convalidaciones o acceso sin prácticas:</strong> Si convalidaste el primer curso completo o no has realizado ninguna hora de prácticas en este ciclo formativo anteriormente, marca la casilla correspondiente.</li>
                        </ul>
                    </div>

                    <!-- Opciones de Checks -->
                    <div class="space-y-3 pt-1">
                        <!-- Check 1: Sin prácticas en primero / ciclo -->
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-900 transition-colors cursor-pointer">
                            <input type="checkbox" name="sin_practicas_primero" value="1" x-model="sinPracticas" class="mt-0.5 rounded border-slate-300 text-[#0048FE] focus:ring-[#0048FE]">
                            <div class="text-xs">
                                <span class="font-bold text-slate-900 dark:text-white block text-sm">No he realizado ninguna hora de prácticas en este ciclo formativo anteriormente</span>
                                <span class="text-slate-500 dark:text-slate-400 mt-0.5 block">
                                    Marca esta casilla si has convalidado el 1er curso completo o accedes directamente sin haber realizado prácticas de 1º en este ciclo.
                                </span>
                            </div>
                        </label>
                        <x-input-error class="mt-1" :messages="$errors->get('sin_practicas_primero')" />

                        <!-- Check 2: Repitiendo módulos de 1º -->
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-900 transition-colors cursor-pointer">
                            <input type="checkbox" name="repitiendo_modulos_primero" value="1" x-model="repitiendoModulos" class="mt-0.5 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-900 dark:text-white block text-sm flex items-center gap-1.5">
                                    <span>Estoy repitiendo con módulos de 1º curso (este año no realizo prácticas)</span>
                                    <span x-show="repitiendoModulos" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">Sin prácticas este año</span>
                                </span>
                                <span class="text-slate-500 dark:text-slate-400 mt-0.5 block">
                                    Por normativa oficial, al tener pendiente algún módulo de 1º curso no es posible incorporarse a prácticas durante este curso académico.
                                </span>
                            </div>
                        </label>
                        <x-input-error class="mt-1" :messages="$errors->get('repitiendo_modulos_primero')" />
                    </div>

                    <!-- Campos de Empresa y Horas en 1º curso (se deshabilitan / ocultan si marcó sin_practicas_primero) -->
                    <div x-show="!sinPracticas" x-transition class="pt-2 border-t border-blue-200/60 dark:border-slate-700">
                        <div class="mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Datos de la FCT / Prácticas de 1º curso</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Introduce la empresa y el número de horas realizadas (válido también si las cursaste hace 2 años).</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="empresa_practicas_primero" value="Empresa de prácticas en 1º" class="font-bold text-slate-800 dark:text-slate-200" />
                                <x-text-input id="empresa_practicas_primero" name="empresa_practicas_primero" type="text" class="mt-1 block w-full" :value="old('empresa_practicas_primero', $user->alumno->empresa_practicas_primero)" placeholder="Ej. NTT Data, Everis, Innova..." ::disabled="sinPracticas" />
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Empresa donde hiciste la formación práctica de primer año.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('empresa_practicas_primero')" />
                            </div>

                            <div>
                                <x-input-label for="horas_practicas_primero" value="Horas realizadas en 1º" class="font-bold text-slate-800 dark:text-slate-200" />
                                <x-text-input id="horas_practicas_primero" name="horas_practicas_primero" type="number" min="0" max="2000" class="mt-1 block w-full" :value="old('horas_practicas_primero', $user->alumno->horas_practicas_primero)" placeholder="Ej. 100" ::disabled="sinPracticas" />
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cómputo acumulable para las 500h totales del ciclo.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('horas_practicas_primero')" />
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje si sinPracticas está marcado -->
                    <div x-show="sinPracticas" x-transition class="p-3.5 rounded-xl bg-blue-100/60 dark:bg-blue-950/40 border border-blue-200 text-xs text-blue-900 dark:text-blue-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0048FE] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Se registrará que <strong>no tienes horas previas de prácticas en 1º curso</strong> (convalidado / 0 horas).</span>
                    </div>
                </div>
            @endif

            <!-- Avatar Upload Section -->
            <div>
                <x-input-label for="avatar" value="Foto de Perfil (Avatar)" class="font-bold text-slate-800 dark:text-slate-200" />
                @if($user->avatar_url)
                    <div class="flex items-center gap-4 my-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="Avatar actual" class="h-16 w-16 object-cover rounded-2xl border border-slate-300 dark:border-slate-600 shadow-md">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Foto de perfil actual</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Puedes subir una nueva imagen para reemplazarla.</p>
                        </div>
                    </div>
                @endif
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
                    @change="handleAvatarChange($event)"
                    class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0048FE] hover:file:bg-blue-100 transition-colors" />
                
                <p x-show="avatarError" x-text="avatarError" class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400" style="display: none;"></p>

                <!-- Aviso de límite de tamaño y formato para avatar -->
                <div class="mt-2 flex items-start gap-2 p-3 rounded-xl bg-blue-50/60 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 text-xs text-slate-600 dark:text-slate-300">
                    <svg class="w-4 h-4 text-[#0048FE] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Formatos permitidos: <strong>JPG, PNG, WebP</strong>. <strong>Tamaño máximo: 2 MB</strong> (2048 KB). La imagen se redimensiona y optimiza automáticamente al guardarse.</span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>

            <!-- CV Upload Section -->
            <div>
                <x-input-label for="cv" value="Curriculum Vitae / Documento Acreditativo (PDF o Imagen)" class="font-bold text-slate-800 dark:text-slate-200" />
                @if($user->cv_url)
                    <div class="my-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if(\Illuminate\Support\Str::endsWith(strtolower($user->cv_url), ['.jpg', '.jpeg', '.png', '.webp']))
                                <img src="{{ asset('storage/' . $user->cv_url) }}" alt="Documento actual" class="h-12 w-12 object-cover rounded-xl border border-slate-300 dark:border-slate-600 shadow-sm">
                            @else
                                <span class="p-2.5 bg-red-100 text-red-600 rounded-xl font-bold text-xs">PDF</span>
                            @endif
                            <div>
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">Documento adjunto actual</span>
                                <span class="text-xs text-slate-400">Guardado en tu perfil</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $user->cv_url) }}" target="_blank" class="text-xs font-bold text-[#0048FE] hover:underline flex items-center gap-1">
                            Ver archivo actual ↗
                        </a>
                    </div>
                @endif
                <input type="file" id="cv" name="cv" accept=".pdf,image/jpeg,image/png,image/webp"
                    @change="handleCvChange($event)"
                    class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0048FE] hover:file:bg-blue-100 transition-colors" />
                
                <p x-show="cvError" x-text="cvError" class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400" style="display: none;"></p>

                <!-- Aviso de límite de tamaño y formato para documentos -->
                <div class="mt-2 flex items-start gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
                    <svg class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Formatos admitidos: <strong>PDF, JPG, PNG, WebP</strong>. <strong>Tamaño máximo: 5 MB</strong> (5120 KB). Puedes subir imágenes y avatar simultáneamente sin conflicto.</span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('cv')" />
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit"
                    :disabled="isSubmitting"
                    class="stitch-btn-primary bg-[#0048FE] hover:bg-blue-700 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-blue-600/30 inline-flex items-center gap-2">
                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSubmitting ? 'Guardando...' : 'Guardar Cambios'">Guardar Cambios</span>
                </button>

                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                        class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Perfil actualizado correctamente.
                    </p>
                @endif
            </div>
        </form>
    </div>

    <script>
    function profileEditForm() {
        return {
            avatarError: '',
            cvError: '',
            generalError: '',
            isUploadError: false,
            isSubmitting: false,

            handleAvatarChange(e) {
                const file = e.target.files[0];
                if (!file) {
                    this.avatarError = '';
                    return;
                }
                const ext = file.name.split('.').pop().toLowerCase();
                const validExts = ['jpg', 'jpeg', 'png', 'webp'];
                const validMimes = ['image/jpeg', 'image/png', 'image/webp'];

                if (!validExts.includes(ext) && (file.type && !validMimes.includes(file.type))) {
                    this.avatarError = 'El formato de "' + file.name + '" no es adecuado. Sólo se admiten fotos en formato JPG, PNG o WebP.';
                } else if (file.size > 2097152) { // 2MB
                    this.avatarError = 'El archivo seleccionado (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB) es demasiado grande. El límite del servidor para el avatar es de 2 MB.';
                } else {
                    this.avatarError = '';
                }
            },

            handleCvChange(e) {
                const file = e.target.files[0];
                if (!file) {
                    this.cvError = '';
                    return;
                }
                const ext = file.name.split('.').pop().toLowerCase();
                const validExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                const validMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

                if (!validExts.includes(ext) && (file.type && !validMimes.includes(file.type))) {
                    this.cvError = 'El formato de "' + file.name + '" no es adecuado. Sólo se admiten archivos PDF o imágenes JPG, PNG, WebP.';
                } else if (file.size > 5242880) { // 5MB
                    this.cvError = 'El archivo seleccionado (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB) es demasiado grande. El límite del servidor para documentos es de 5 MB.';
                } else {
                    this.cvError = '';
                }
            },

            handleSubmit(e) {
                this.generalError = '';
                this.isUploadError = false;
                const avatarInput = document.getElementById('avatar');
                const cvInput = document.getElementById('cv');
                const hasAvatar = !!(avatarInput && avatarInput.files && avatarInput.files.length > 0);
                const hasCv = !!(cvInput && cvInput.files && cvInput.files.length > 0);

                // Validar avatar si se adjuntó
                if (hasAvatar) {
                    const file = avatarInput.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext) && (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                        this.avatarError = 'El formato de la foto de perfil no es el adecuado (sólo JPG, PNG, WebP).';
                    } else if (file.size > 2097152) {
                        this.avatarError = 'La foto de perfil supera el tamaño máximo permitido de 2 MB (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB).';
                    }
                }

                // Validar CV si se adjuntó
                if (hasCv) {
                    const file = cvInput.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['pdf', 'jpg', 'jpeg', 'png', 'webp'].includes(ext) && (file.type && !['application/pdf', 'image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                        this.cvError = 'El formato del documento no es el adecuado (sólo PDF, JPG, PNG, WebP).';
                    } else if (file.size > 5242880) {
                        this.cvError = 'El documento supera el tamaño máximo permitido de 5 MB (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB).';
                    }
                }

                if (this.avatarError || this.cvError) {
                    e.preventDefault();
                    this.isUploadError = true;
                    this.generalError = 'Error 413 prevenido: Uno o más archivos superan el tamaño máximo permitido por el servidor o su formato no es el adecuado. Corrige los archivos marcados abajo antes de continuar.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                // Si NO se están subiendo archivos (ej: solo texto como empresa/horas de prácticas, teléfono, etc.)
                // permitir el envío nativo del formulario de forma limpia y transparente sin intercepción fetch
                if (!hasAvatar && !hasCv) {
                    this.isSubmitting = true;
                    return; // Permite el submit estándar HTML
                }

                // Si se están subiendo archivos, interceptar para capturar posibles errores 413 del servidor/proxy
                e.preventDefault();
                this.isSubmitting = true;
                const form = e.target;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(async response => {
                    if (response.status === 413) {
                        this.isUploadError = true;
                        let msg = 'Error 413 (Payload Too Large): El servidor ha rechazado el archivo porque es demasiado grande o su formato no es el adecuado. Asegúrate de que el avatar no supere 2 MB y el documento no supere 5 MB (formatos válidos: JPG, PNG, WebP o PDF).';
                        try {
                            const data = await response.json();
                            if (data && data.message) msg = data.message;
                        } catch(jsonErr) {}
                        this.generalError = msg;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        this.isSubmitting = false;
                    } else if (response.status === 422) {
                        const data = await response.json();
                        if (data && data.errors) {
                            if (data.errors.avatar) {
                                this.avatarError = data.errors.avatar[0];
                                this.isUploadError = true;
                            }
                            if (data.errors.cv) {
                                this.cvError = data.errors.cv[0];
                                this.isUploadError = true;
                            }
                            const errorList = Object.values(data.errors).flat();
                            this.generalError = 'Revisa los campos del formulario: ' + errorList.join(' ');
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        this.isSubmitting = false;
                    } else if (response.ok || response.status === 200 || response.status === 302) {
                        window.location.href = "{{ route('profile.edit') }}?status=profile-updated";
                    } else {
                        // Reenviar de forma estándar ante cualquier otra respuesta
                        form.submit();
                    }
                }).catch(err => {
                    // Si ocurre cualquier error en fetch (conexión, redirección, CORS), realizar el envío nativo
                    form.submit();
                });
            }
        };
    }
    </script>
</section>

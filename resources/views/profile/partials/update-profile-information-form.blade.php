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

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
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
            <x-input-label for="linkedin_url" value="Perfil de LinkedIn" class="font-bold text-slate-800 dark:text-slate-200" />
            <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $user->linkedin_url ?: 'https://')" placeholder="https://linkedin.com/in/tu-perfil" />
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Introduce la URL completa comenzando por https://</p>
            <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
        </div>

        <div>
            <x-input-label for="github_url" value="Perfil de GitHub" class="font-bold text-slate-800 dark:text-slate-200" />
            <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full" :value="old('github_url', $user->github_url ?: 'https://')" placeholder="https://github.com/tu-usuario" />
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Introduce la URL completa comenzando por https://</p>
            <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
        </div>

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
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0048FE] hover:file:bg-blue-100 transition-colors" />
            
            <!-- Aviso de límite de tamaño para imágenes -->
            <div class="mt-2 flex items-start gap-2 p-3 rounded-xl bg-blue-50/60 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 text-xs text-slate-600 dark:text-slate-300">
                <svg class="w-4 h-4 text-[#0048FE] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Formatos permitidos: <strong>JPG, PNG, WebP</strong>. <strong>Tamaño máximo: 2 MB</strong> (2048 KB). Las imágenes que superen este límite no podrán ser procesadas. La imagen se redimensiona y optimiza automáticamente.</span>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="cv" value="Curriculum Vitae (PDF)" class="font-bold text-slate-800 dark:text-slate-200" />
            @if($user->cv_url)
                <div class="my-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-red-100 text-red-600 rounded-xl font-bold text-xs">PDF</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Curriculum Vitae adjunto</span>
                    </div>
                    <a href="{{ asset('storage/' . $user->cv_url) }}" target="_blank" class="text-xs font-bold text-[#0048FE] hover:underline flex items-center gap-1">
                        Ver documento actual ↗
                    </a>
                </div>
            @endif
            <input type="file" id="cv" name="cv" accept=".pdf" class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0048FE] hover:file:bg-blue-100 transition-colors" />
            
            <!-- Aviso de límite de tamaño para documentos -->
            <div class="mt-2 flex items-start gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
                <svg class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Formato permitido: <strong>PDF</strong>. <strong>Tamaño máximo: 5 MB</strong> (5120 KB).</span>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('cv')" />
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <x-primary-button class="bg-[#0048FE] hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-blue-600/30">
                Guardar Cambios
            </x-primary-button>

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
</section>

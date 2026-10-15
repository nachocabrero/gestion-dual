<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Perfil de Alumno</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('alumnos.edit', $alumno) }}" class="hlanz-btn-secondary">Editar</a>
                <a href="{{ route('alumnos.index') }}" class="hlanz-btn-secondary">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            <!-- Datos personales -->
            <div class="hlanz-card p-6">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    @if($alumno->user->avatar_url)
                        <img src="{{ asset('storage/' . $alumno->user->avatar_url) }}" alt="{{ $alumno->user->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-md shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#0048FE] to-cyan-500 text-white flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                            {{ strtoupper(substr($alumno->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold font-display text-slate-900 truncate">{{ $alumno->user->name }}</h3>
                        <p class="text-sm text-slate-500 truncate">{{ $alumno->user->email }}</p>
                        @if($alumno->user->cv_url)
                            <a href="{{ asset('storage/' . $alumno->user->cv_url) }}" target="_blank" class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-[#0048FE] hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Descargar / Ver CV adjunto</span>
                            </a>
                        @endif
                    </div>
                </div>

                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Información de Contacto y Enlaces</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Teléfono</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->telefono ?? $alumno->user->telefono ?? 'No indicado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Fecha Nacimiento</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? 'No indicada' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Domicilio</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->domicilio ?? 'No indicado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">LinkedIn</span>
                        <p class="font-semibold text-slate-900 mt-0.5 truncate">
                            @php $linkedin = $alumno->linkedin_url ?? $alumno->user->linkedin_url; @endphp
                            @if($linkedin)
                            <a href="{{ $linkedin }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                <span>{{ $linkedin }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @else
                            No indicado
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">GitHub</span>
                        <p class="font-semibold text-slate-900 mt-0.5 truncate">
                            @if($alumno->user->github_url)
                            <a href="{{ $alumno->user->github_url }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                <span>{{ $alumno->user->github_url }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @else
                            No indicado
                            @endif
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Portfolio Externo / Web Personal</span>
                        <p class="font-semibold text-slate-900 mt-0.5">
                            @php $portfolio = $alumno->portfolio_url ?? $alumno->user->portfolio_url; @endphp
                            @if($portfolio)
                            <a href="{{ $portfolio }}" target="_blank" class="text-[#0048FE] hover:underline inline-flex items-center gap-1.5 font-bold">
                                <span>🌐 {{ $portfolio }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @else
                            No indicado
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Datos académicos -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Datos Académicos</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Grupos a los que pertenece</span>
                        @if($alumno->grupos->count() > 0)
                            <ul class="list-disc pl-5 mt-2 font-semibold text-slate-900">
                                @foreach($alumno->grupos as $grupo)
                                    <li>{{ $grupo->nombre }}
                                        @if($grupo->cursoAcademico)<span class="text-slate-500">({{ $grupo->cursoAcademico->nombre }})</span>@endif
                                        <span class="text-slate-500">(Tutor/a: {{ $grupo->tutor->name ?? 'No asignado' }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="font-semibold text-slate-900 mt-1">Sin grupo</p>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tutor de prácticas</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->tutorPracticas->name ?? 'No asignado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado</span>
                        <p class="font-semibold mt-0.5">
                            @if($alumno->user->is_active)
                            <span class="hlanz-badge-success">Activo</span>
                            @else
                            <span class="hlanz-badge-danger">Desactivado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

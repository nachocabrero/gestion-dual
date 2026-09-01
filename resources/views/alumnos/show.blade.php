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
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Datos Personales</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nombre</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->user->email }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Teléfono</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->telefono ?? 'No indicado' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Fecha Nacimiento</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? 'No indicada' }}</p>
                    </div>
                    <div class="col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Domicilio</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $alumno->domicilio ?? 'No indicado' }}</p>
                    </div>
                    <div class="col-span-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">LinkedIn</span>
                        <p class="font-semibold text-slate-900 mt-0.5">
                            @if($alumno->linkedin_url)
                            <a href="{{ $alumno->linkedin_url }}" target="_blank" class="text-[#0048FE] underline">{{ $alumno->linkedin_url }}</a>
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

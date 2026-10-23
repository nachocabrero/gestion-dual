<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Perfil del Profesor</h2>
            <div class="flex items-center gap-2">
                @can('update-profesor', $profesor)
                <a href="{{ route('profesores.edit', $profesor) }}" class="hlanz-btn-secondary">Editar</a>
                @endcan
                @if(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
                <a href="{{ route('profesores.index') }}" class="hlanz-btn-secondary">Volver</a>
                @else
                <a href="{{ route('dashboard') }}" class="hlanz-btn-secondary">Volver al panel</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Datos personales -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Datos Personales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nombre</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->user->email }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Especialidad</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->especialidad ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Familia profesional</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->familia?->nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado</span>
                        <p class="mt-0.5">
                            @if($profesor->user->isPendingApproval())
                            <span class="stitch-badge-warning inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                En espera (Aprobación pendiente)
                            </span>
                            @elseif($profesor->user->isActive())
                            <span class="hlanz-badge-success">Activo</span>
                            @else
                            <span class="hlanz-badge-danger">Desactivado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Roles</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="hlanz-badge-primary">Profesor</span>
                    @if($profesor->es_tutor)
                    <span class="hlanz-badge-secondary">Tutor</span>
                    @endif
                    @if($profesor->es_coordinador_dual)
                    <span class="hlanz-badge-primary">Coordinador Dual</span>
                    @endif
                </div>
            </div>

            <!-- Empresas de las que lleva el seguimiento -->
            <div class="hlanz-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0048FE] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-display text-slate-900">Empresas en Seguimiento</h3>
                            <p class="text-xs text-slate-500">Empresas asignadas a este profesor para su tutorización y seguimiento</p>
                        </div>
                    </div>
                    <span class="stitch-badge-neutral text-xs font-semibold px-2.5 py-1">
                        {{ $empresas->count() }} {{ $empresas->count() === 1 ? 'empresa' : 'empresas' }}
                    </span>
                </div>

                @if($empresas->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($empresas as $empresa)
                    <div class="p-3.5 rounded-xl border border-slate-200/80 hover:border-blue-300 hover:shadow-sm bg-slate-50/60 transition-all flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('empresas.show', $empresa) }}" class="font-bold text-sm text-slate-900 hover:text-[#0048FE] transition-colors flex items-center gap-1.5">
                                    {{ $empresa->nombre }}
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <p class="text-xs text-slate-500 mt-0.5">CIF: <span class="font-medium text-slate-700">{{ $empresa->cif }}</span></p>
                            </div>
                            @if($empresa->is_active)
                                <span class="hlanz-badge-success text-[11px]">Activa</span>
                            @else
                                <span class="hlanz-badge-danger text-[11px]">Inactiva</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 space-y-0.5 border-t border-slate-200/60 pt-2">
                            @if($empresa->email)
                            <p class="truncate"><span class="font-medium text-slate-600">Email:</span> {{ $empresa->email }}</p>
                            @endif
                            @if($empresa->telefono)
                            <p><span class="font-medium text-slate-600">Tel:</span> {{ $empresa->telefono }}</p>
                            @endif
                            @if($empresa->direccion)
                            <p class="truncate"><span class="font-medium text-slate-600">Dir:</span> {{ $empresa->direccion }}</p>
                            @endif
                        </div>
                        <div class="pt-1 flex justify-end">
                            <a href="{{ route('empresas.show', $empresa) }}" class="text-xs font-bold text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                Ver ficha de empresa &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 px-4 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                    <p class="text-sm text-slate-500">Este profesor no tiene actualmente empresas asignadas para seguimiento.</p>
                </div>
                @endif
            </div>

            <!-- Prácticas de las que es tutor docente en el curso actual -->
            <div class="hlanz-card p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2H7a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-display text-slate-900">
                                Prácticas Tutorizadas — Curso Actual
                                @if($cursoActual)
                                    <span class="text-sm font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60 ml-1.5">{{ $cursoActual->nombre }}</span>
                                @endif
                            </h3>
                            <p class="text-xs text-slate-500">Alumnos y empresas con prácticas activas bajo la tutela docente de este profesor</p>
                        </div>
                    </div>
                    <span class="stitch-badge-neutral text-xs font-semibold px-2.5 py-1 self-start sm:self-auto">
                        {{ $practicasCursoActual->count() }} {{ $practicasCursoActual->count() === 1 ? 'práctica' : 'prácticas' }}
                    </span>
                </div>

                @if($practicasCursoActual->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Alumno</th>
                                <th class="p-3">Empresa</th>
                                <th class="p-3">Tutor Laboral</th>
                                <th class="p-3">Periodo</th>
                                <th class="p-3 text-center">Horas</th>
                                <th class="p-3 text-center">Convenio</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-900">
                            @foreach($practicasCursoActual as $practica)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 font-semibold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($practica->alumno?->user?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <span>{{ $practica->alumno?->user?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <a href="{{ route('empresas.show', $practica->empresa) }}" class="font-medium text-[#0048FE] hover:underline">
                                        {{ $practica->empresa?->nombre ?? '—' }}
                                    </a>
                                </td>
                                <td class="p-3 text-slate-600 text-xs">
                                    {{ $practica->tutorLaboral?->nombre ?? '—' }}
                                </td>
                                <td class="p-3 text-xs text-slate-600 whitespace-nowrap">
                                    {{ $practica->fecha_inicio?->format('d/m/Y') }} — {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : 'Actualidad' }}
                                </td>
                                <td class="p-3 text-center font-bold text-slate-800 text-xs whitespace-nowrap">
                                    {{ $practica->horas_acumuladas }} h
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    @if($practica->convenio_firmado)
                                        <span class="hlanz-badge-success text-[10px]">Firmado</span>
                                    @else
                                        <span class="stitch-badge-warning text-[10px]">Pendiente</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('practicas.show', $practica) }}" class="stitch-btn-secondary text-xs py-1 px-2.5">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-6 px-4 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                    <p class="text-sm text-slate-500">Este profesor no es tutor docente de ninguna práctica en el curso actual.</p>
                </div>
                @endif
            </div>

            <!-- Desplegable de cursos anteriores (si fue tutor docente en cursos anteriores) -->
            @if($cursosAnterioresConPracticas->isNotEmpty())
            <div class="hlanz-card p-6" x-data="{ cursoActivoId: '{{ $cursosAnterioresConPracticas->keys()->first() }}' }">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-display text-slate-900">Historial de Prácticas — Cursos Anteriores</h3>
                            <p class="text-xs text-slate-500">Cursos académicos anteriores en los que ejerció como tutor docente de prácticas</p>
                        </div>
                    </div>

                    <!-- Desplegable selector de cursos anteriores -->
                    <div class="flex items-center gap-2">
                        <label for="select-curso-anterior" class="text-xs font-bold text-slate-600 whitespace-nowrap">Curso anterior:</label>
                        <select id="select-curso-anterior" x-model="cursoActivoId" class="stitch-input text-xs font-semibold py-1.5 px-3 rounded-lg border-slate-300 bg-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @foreach($cursosAnterioresConPracticas as $cId => $item)
                                <option value="{{ $cId }}">
                                    {{ $item['curso']?->nombre ?? 'Curso ' . $cId }} ({{ $item['practicas']->count() }} {{ $item['practicas']->count() === 1 ? 'práctica' : 'prácticas' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @foreach($cursosAnterioresConPracticas as $cId => $item)
                <div x-show="cursoActivoId == '{{ $cId }}'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Prácticas tuteladas en {{ $item['curso']?->nombre ?? 'Curso anterior' }}
                        </span>
                        <span class="stitch-badge-neutral text-xs">
                            Total: {{ $item['practicas']->count() }} {{ $item['practicas']->count() === 1 ? 'alumno' : 'alumnos' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="p-3">Alumno</th>
                                    <th class="p-3">Empresa</th>
                                    <th class="p-3">Tutor Laboral</th>
                                    <th class="p-3">Periodo</th>
                                    <th class="p-3 text-center">Horas</th>
                                    <th class="p-3 text-center">Convenio</th>
                                    <th class="p-3 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-900">
                                @foreach($item['practicas'] as $practica)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center">
                                                {{ strtoupper(substr($practica->alumno?->user?->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <span>{{ $practica->alumno?->user?->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ route('empresas.show', $practica->empresa) }}" class="font-medium text-[#0048FE] hover:underline">
                                            {{ $practica->empresa?->nombre ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="p-3 text-slate-600 text-xs">
                                        {{ $practica->tutorLaboral?->nombre ?? '—' }}
                                    </td>
                                    <td class="p-3 text-xs text-slate-600 whitespace-nowrap">
                                        {{ $practica->fecha_inicio?->format('d/m/Y') }} — {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : 'Finalizada' }}
                                    </td>
                                    <td class="p-3 text-center font-bold text-slate-800 text-xs whitespace-nowrap">
                                        {{ $practica->horas_acumuladas }} h
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        @if($practica->convenio_firmado)
                                            <span class="hlanz-badge-success text-[10px]">Firmado</span>
                                        @else
                                            <span class="stitch-badge-warning text-[10px]">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right whitespace-nowrap">
                                        <a href="{{ route('practicas.show', $practica) }}" class="stitch-btn-secondary text-xs py-1 px-2.5">
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Grupos tutor -->
            @if($profesor->gruposTutor->count() > 0)
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Grupos Tutor</h3>
                <ul class="list-disc pl-5 font-semibold text-slate-900">
                    @foreach($profesor->gruposTutor as $grupo)
                    <li>{{ $grupo->nombre }} <span class="text-slate-500 font-normal">({{ $grupo->linea->ciclo->nombre ?? '—' }})</span></li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Grupos que imparte (grupo + asignatura) -->
            @if($profesor->gruposImpartidos->count() > 0)
            @php $asigs = $profesor->asignaturas->keyBy('id'); @endphp
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Grupos que imparte</h3>
                <ul class="list-disc pl-5 font-semibold text-slate-900">
                    @foreach($profesor->gruposImpartidos as $grupo)
                    <li>
                        {{ $grupo->nombre }}
                        @if($grupo->pivot->asignatura_id && isset($asigs[$grupo->pivot->asignatura_id]))
                            <span class="text-slate-500 font-normal">— {{ $asigs[$grupo->pivot->asignatura_id]->nombre }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Sustituciones -->
            @if($profesor->sustituciones->count() > 0)
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Sustituciones</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Sustituto</th>
                                <th class="p-3">Asignatura</th>
                                <th class="p-3">Grupo</th>
                                <th class="p-3">Desde</th>
                                <th class="p-3">Hasta</th>
                                <th class="p-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-900">
                            @foreach($profesor->sustituciones as $sus)
                            <tr>
                                <td class="p-3">{{ $sus->profesorSustituto->user->name }}</td>
                                <td class="p-3">{{ $sus->asignatura?->nombre ?? '—' }}</td>
                                <td class="p-3">{{ $sus->grupo?->nombre ?? '—' }}</td>
                                <td class="p-3">{{ $sus->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="p-3">{{ $sus->fecha_fin->format('d/m/Y') }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('profesores.sustituciones.destroy', $sus) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 text-xs font-bold hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

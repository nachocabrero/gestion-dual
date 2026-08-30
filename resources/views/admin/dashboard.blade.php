<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">
                Panel de Administración General
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="hlanz-btn-secondary">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Gestión Usuarios</span>
                </a>
                <a href="{{ route('admin.cambios.index') }}" class="hlanz-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Historial Cambios</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- User Summary Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Alumnos Card -->
            <div class="hlanz-card p-6 border-l-4 border-l-[#0048FE] relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Alumnado</p>
                        <h3 class="text-3xl font-extrabold font-display text-slate-900 mt-1">{{ $totalAlumnos }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0048FE] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100 text-xs font-semibold">
                    <span class="hlanz-badge-success">{{ $totalAlumnosActivos }} activos</span>
                    <span class="hlanz-badge-danger">{{ $totalAlumnosInactivos }} inactivos</span>
                </div>
            </div>

            <!-- Profesores Card -->
            <div class="hlanz-card p-6 border-l-4 border-l-purple-600 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Profesorado</p>
                        <h3 class="text-3xl font-extrabold font-display text-slate-900 mt-1">{{ $totalProfesores }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100 text-xs font-semibold">
                    <span class="hlanz-badge-success">{{ $totalProfesoresActivos }} activos</span>
                    <span class="hlanz-badge-danger">{{ $totalProfesoresInactivos }} inactivos</span>
                </div>
            </div>

            <!-- Empresas Card -->
            <div class="hlanz-card p-6 border-l-4 border-l-emerald-600 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Empresas Colaboradoras</p>
                        <h3 class="text-3xl font-extrabold font-display text-slate-900 mt-1">{{ $totalEmpresas }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100 text-xs font-semibold">
                    <span class="hlanz-badge-success">{{ $totalEmpresas }} activas</span>
                    <span class="hlanz-badge-danger">{{ $totalEmpresasInactivas }} inactivas</span>
                </div>
            </div>

        </div>

        <!-- Prácticas State Summary Cards -->
        <div class="hlanz-card p-6">
            <h3 class="text-lg font-bold font-display text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0048FE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Estado Global de Prácticas / FCT
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-center">
                    <p class="text-3xl font-extrabold font-display text-amber-700">{{ $practicasPendientes }}</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800 mt-1">Pendientes de Asignar</p>
                </div>
                <div class="p-5 rounded-2xl bg-blue-50/80 border border-blue-200/80 text-center">
                    <p class="text-3xl font-extrabold font-display text-[#0048FE]">{{ $practicasEnCurso }}</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-800 mt-1">Prácticas En Curso</p>
                </div>
                <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 text-center">
                    <p class="text-3xl font-extrabold font-display text-emerald-700">{{ $practicasFinalizadas }}</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-800 mt-1">Finalizadas con Éxito</p>
                </div>
            </div>
        </div>

        <!-- Convenios & Filters Section -->
        <div class="hlanz-card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold font-display text-slate-900">Gestión de Convenios Séneca</h3>
                    <p class="text-xs text-slate-500">Filtrado de convenios marco de prácticas con empresas.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        Firmados: {{ $conveniosFirmados }}
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                        Pendientes: {{ $conveniosNoFirmados }}
                    </div>
                </div>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Familia Profesional</label>
                    <select name="convenio_familia" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                        <option value="">Todas las familias</option>
                        @foreach(\App\Models\Familia::all() as $fam)
                            <option value="{{ $fam->id }}" {{ request('convenio_familia') == $fam->id ? 'selected' : '' }}>{{ $fam->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Curso Académico</label>
                    <input type="text" name="convenio_curso" placeholder="Ej. 26/27" value="{{ request('convenio_curso') }}" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE]">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="hlanz-btn-primary flex-1">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['convenio_familia', 'convenio_curso']))
                    <a href="{{ route('admin.dashboard') }}" class="hlanz-btn-secondary">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <th class="p-3.5">Empresa</th>
                            <th class="p-3.5">Ciclo Formativo</th>
                            <th class="p-3.5">Curso</th>
                            <th class="p-3.5 text-center">Estado Firma</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($convenios as $convenio)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 font-semibold text-slate-900">{{ $convenio->empresa->nombre ?? 'N/A' }}</td>
                            <td class="p-3.5 text-slate-600">{{ $convenio->ciclo->nombre ?? 'N/A' }}</td>
                            <td class="p-3.5 text-slate-500 font-mono text-xs">{{ $convenio->curso_academico }}</td>
                            <td class="p-3.5 text-center">
                                @if($convenio->estado === 'firmado')
                                <span class="hlanz-badge-success">Firmado Séneca</span>
                                @else
                                <span class="hlanz-badge-danger">Pendiente Firma</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 text-sm">No se encontraron convenios registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $convenios->links() }}
            </div>
        </div>

        <!-- Proyectos Destacados Grid -->
        <div class="hlanz-card p-6">
            <h3 class="text-lg font-bold font-display text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Proyectos Destacados de 2º Curso por Ciclo
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                @foreach($destacadosPorCiclo as $cicloNombre => $count)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <p class="text-xs font-bold text-slate-500 uppercase">{{ $cicloNombre }}</p>
                    <p class="text-2xl font-extrabold font-display text-slate-900 mt-1">{{ $count }}</p>
                    <p class="text-[10px] text-slate-400">Proyectos destacados</p>
                </div>
                @endforeach
            </div>

            @if($proyectosDestacados->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($proyectosDestacados as $cicloNombre => $proyectos)
                    @foreach($proyectos->take(3) as $proyecto)
                    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#0048FE] transition-colors flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold">★</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm text-slate-900 truncate">{{ $proyecto->titulo }}</h4>
                            <p class="text-xs text-slate-500 truncate">{{ $proyecto->alumno->user->name ?? 'Alumno' }}</p>
                            <span class="mt-2 inline-block px-2 py-0.5 rounded-full bg-blue-50 text-[#0048FE] font-bold text-[10px]">Nota: {{ $proyecto->calificacion }}/10</span>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Mis Prácticas y Horas
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Seguimiento de tu empresa asignada, convenio y cómputo de horas</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @php
            $alumnoAuth = auth()->user()->alumno;
            $horas1 = (int) ($alumnoAuth?->horas_practicas_primero ?? 0);
            $horas2 = (int) $practicas->sum('horas_acumuladas');
            $totalHoras = $horas1 + $horas2;
        @endphp

        @if($alumnoAuth && ($alumnoAuth->empresa_practicas_primero || $alumnoAuth->horas_practicas_primero))
        <div class="stitch-card p-5 bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-white border border-blue-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0048FE] text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                        1º
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Prácticas realizadas en 1º Curso</h3>
                            <span class="stitch-badge-neutral text-[10px]">Cómputo Dual</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Empresa: <strong class="text-slate-900">{{ $alumnoAuth->empresa_practicas_primero ?: 'No especificada' }}</strong>
                            @if($alumnoAuth->horas_practicas_primero)
                            · <strong class="text-[#0048FE]">{{ $alumnoAuth->horas_practicas_primero }}h</strong> realizadas
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">Total global (1º + 2º):</span>
                    <span class="font-extrabold text-sm {{ $totalHoras >= 500 ? 'text-emerald-600' : 'text-[#0048FE]' }}">
                        {{ $totalHoras }}h / 500h
                    </span>
                    @if($totalHoras >= 500)
                        <span class="stitch-badge-success text-[10px]">Completado</span>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Desktop Table -->
        <div class="stitch-table-container hidden sm:block">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Curso Académico</th>
                        <th>Periodo</th>
                        <th class="text-center">Horas Acumuladas</th>
                        <th class="text-center">Convenio</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($practicas as $practica)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($practica->empresa->nombre ?? 'E', 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 text-sm block">{{ $practica->empresa->nombre }}</span>
                                    @if($practica->empresa->telefono || $practica->empresa->email)
                                    <span class="text-xs text-slate-400 block">{{ $practica->empresa->telefono ?? $practica->empresa->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="stitch-badge-neutral">{{ $practica->cursoAcademico->nombre ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-slate-700 font-medium">
                                {{ $practica->fecha_inicio->format('d/m/Y') }} → {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : 'En curso' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-blue-50 text-[#0048FE] font-bold text-sm border border-blue-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $practica->horas_acumuladas }}h
                            </span>
                        </td>
                        <td class="text-center">
                            @if($practica->convenio_firmado)
                            <span class="stitch-badge-success">Firmado</span>
                            @else
                            <span class="stitch-badge-danger">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($practica->estaEnCurso())
                            <span class="stitch-badge-info">En curso</span>
                            @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                            <span class="stitch-badge-success">Finalizada</span>
                            @else
                            <span class="stitch-badge-warning">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-800">No tienes prácticas registradas</h3>
                            <p class="text-xs text-slate-500 mt-1">Cuando se te asigne una empresa o comiences la FCT, verás aquí el registro.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="sm:hidden space-y-4">
            @forelse($practicas as $practica)
            <div class="stitch-card p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                            {{ strtoupper(substr($practica->empresa->nombre ?? 'E', 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $practica->empresa->nombre }}</h4>
                            <p class="text-xs text-slate-500">{{ $practica->cursoAcademico->nombre ?? '—' }}</p>
                        </div>
                    </div>
                    @if($practica->estaEnCurso())
                    <span class="stitch-badge-info">En curso</span>
                    @elseif($practica->fecha_fin && $practica->fecha_fin < now())
                    <span class="stitch-badge-success">Finalizada</span>
                    @else
                    <span class="stitch-badge-warning">Pendiente</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Horas Acumuladas</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $practica->horas_acumuladas }}h</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Convenio</span>
                        @if($practica->convenio_firmado)
                        <span class="text-emerald-600 font-bold">Firmado</span>
                        @else
                        <span class="text-rose-600 font-bold">Pendiente</span>
                        @endif
                    </div>
                </div>

                <div class="pt-2 text-xs text-slate-500 flex items-center gap-1.5 border-t border-slate-100">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $practica->fecha_inicio->format('d/m/Y') }} → {{ $practica->fecha_fin ? $practica->fecha_fin->format('d/m/Y') : 'En curso' }}</span>
                </div>
            </div>
            @empty
            <div class="stitch-card p-8 text-center">
                <p class="text-sm font-bold text-slate-700">No tienes prácticas registradas</p>
                <p class="text-xs text-slate-400 mt-1">Tus asignaciones de FCT aparecerán en este panel.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
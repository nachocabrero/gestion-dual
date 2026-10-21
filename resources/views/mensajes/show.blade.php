<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    @if($esRemitenteOGestor)
                    <a href="{{ route('mensajes.index') }}" class="hover:text-[#0048FE] transition-colors">Mensajería</a>
                    @else
                    <a href="{{ route('notificaciones.index') }}" class="hover:text-[#0048FE] transition-colors">Notificaciones</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Comunicado</span>
                </div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    {{ $mensaje->asunto }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    {{ $mensaje->resumen_destinatarios }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($esRemitenteOGestor)
                <a href="{{ route('mensajes.index') }}" class="stitch-btn-secondary text-xs font-semibold">
                    ← Historial
                </a>
                <a href="{{ route('mensajes.create') }}" class="stitch-btn-primary inline-flex items-center gap-1.5 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo mensaje</span>
                </a>
                @else
                <a href="{{ route('notificaciones.index') }}" class="stitch-btn-secondary text-xs font-semibold">
                    ← Volver a Notificaciones
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Card Principal con el Mensaje -->
        <div class="stitch-card p-6 sm:p-8">
            <!-- Encabezado del Comunicado -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    @if($mensaje->remitente?->avatar_url)
                        <img src="{{ asset('storage/' . $mensaje->remitente->avatar_url) }}" alt="{{ $mensaje->remitente->name }}" class="w-12 h-12 rounded-2xl object-cover shadow-sm border border-slate-200 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0048FE] to-cyan-500 text-white flex items-center justify-center font-bold text-base shadow-md shrink-0">
                            {{ strtoupper(substr($mensaje->remitente->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ $mensaje->remitente->name ?? 'Usuario del Centro' }}</div>
                        <div class="text-xs text-slate-400">
                            {{ $mensaje->remitente->email ?? '' }} · {{ $mensaje->created_at->format('d/m/Y \a \l\a\s H:i') }} ({{ $mensaje->created_at->diffForHumans() }})
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                        @if($mensaje->tipo_destinatario === 'grupos') bg-blue-50 text-blue-700 border border-blue-200
                        @elseif($mensaje->tipo_destinatario === 'alumnos_grupo') bg-amber-50 text-amber-700 border border-amber-200
                        @else bg-purple-50 text-purple-700 border border-purple-200 @endif">
                        <span>Audiencia:</span>
                        <strong>{{ $mensaje->resumen_destinatarios }}</strong>
                    </span>
                </div>
            </div>

            <!-- Cuerpo del Mensaje -->
            <div class="py-6 sm:py-8">
                <div class="prose max-w-none text-slate-800 text-base leading-relaxed whitespace-pre-line bg-slate-50/60 rounded-2xl p-6 border border-slate-100">
                    {!! nl2br(e($mensaje->cuerpo)) !!}
                </div>
            </div>

            <!-- Pie del comunicado con nota RGPD -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Comunicado oficial del IES Hermenegildo Lanz · Conforme a la normativa de protección de datos.</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    ID Referencia: #COM-{{ str_pad($mensaje->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>

        <!-- Panel de Seguimiento de Lecturas (visible para remitente, coordinador dual y admin) -->
        @if($esRemitenteOGestor)
        @php
            $total = $mensaje->destinatarios->count();
            $leidos = $mensaje->destinatarios->filter(fn($u) => $u->pivot->leido_at !== null)->count();
            $pct = $total > 0 ? round(($leidos / $total) * 100) : 0;
        @endphp

        <div class="stitch-card p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Seguimiento de Entrega y Lecturas</h3>
                    <p class="text-xs text-slate-500">Comprueba qué personas han accedido al comunicado y en qué momento</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-sm font-bold text-slate-900">{{ $leidos }} de {{ $total }} leídos</div>
                        <div class="text-xs font-semibold text-emerald-600">{{ $pct }}% de tasa de lectura</div>
                    </div>
                    <div class="w-14 h-14 relative flex items-center justify-center">
                        <svg class="w-14 h-14 transform -rotate-90">
                            <circle cx="28" cy="28" r="22" stroke="currentColor" stroke-width="4" class="text-slate-100" fill="transparent"/>
                            <circle cx="28" cy="28" r="22" stroke="currentColor" stroke-width="4" class="text-emerald-500" fill="transparent"
                                    stroke-dasharray="138" stroke-dashoffset="{{ 138 - (138 * $pct / 100) }}"/>
                        </svg>
                        <span class="absolute text-xs font-bold text-slate-800">{{ $pct }}%</span>
                    </div>
                </div>
            </div>

            <!-- Tabla de Destinatarios y Estado -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Destinatario</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4 text-center">Estado de lectura</th>
                            <th class="py-3 px-4 text-right">Fecha de lectura</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($mensaje->destinatarios as $destinatario)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 flex items-center gap-3">
                                @if($destinatario->avatar_url)
                                    <img src="{{ asset('storage/' . $destinatario->avatar_url) }}" alt="{{ $destinatario->name }}" class="w-7 h-7 rounded-lg object-cover">
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($destinatario->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="font-semibold text-slate-900 text-xs">{{ $destinatario->name }}</span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ $destinatario->email }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($destinatario->pivot->leido_at)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Leído</span>
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Pendiente</span>
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-xs text-slate-500">
                                @if($destinatario->pivot->leido_at)
                                    <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($destinatario->pivot->leido_at)->format('d/m/Y H:i') }}</span>
                                    <span class="text-[10px] text-slate-400">({{ \Carbon\Carbon::parse($destinatario->pivot->leido_at)->diffForHumans() }})</span>
                                @else
                                    <span class="text-slate-300 italic">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

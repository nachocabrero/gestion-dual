<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Mensajería y Comunicados
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    Envío de comunicados oficiales a grupos, alumnos seleccionados y profesorado de ciclos
                </p>
            </div>
            <div>
                <a href="{{ route('mensajes.create') }}" class="stitch-btn-primary inline-flex items-center gap-2 shadow-md shadow-blue-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo comunicado</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Card con tabla de comunicados enviados -->
        <div class="stitch-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Historial de Comunicados Enviados</h3>
                <span class="text-xs text-slate-400 font-medium">{{ $mensajes->total() }} comunicado(s) en total</span>
            </div>

            @if($mensajes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Asunto / Comunicado</th>
                            <th class="py-3 px-4">Destinatarios</th>
                            <th class="py-3 px-4">Remitente</th>
                            <th class="py-3 px-4 text-center">Lecturas</th>
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($mensajes as $msg)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('mensajes.show', $msg) }}" class="font-bold text-slate-900 hover:text-[#0048FE] block transition-colors">
                                    {{ $msg->asunto }}
                                </a>
                                <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                                    {{ Str::limit($msg->cuerpo, 80) }}
                                </p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold
                                    @if($msg->tipo_destinatario === 'grupos') bg-blue-50 text-blue-700 border border-blue-200/60
                                    @elseif($msg->tipo_destinatario === 'alumnos_grupo') bg-amber-50 text-amber-700 border border-amber-200/60
                                    @else bg-purple-50 text-purple-700 border border-purple-200/60 @endif">
                                    @if($msg->tipo_destinatario === 'grupos')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @elseif($msg->tipo_destinatario === 'alumnos_grupo')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    @endif
                                    <span>{{ $msg->resumen_destinatarios }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-700">
                                <div class="font-medium text-slate-900">{{ $msg->remitente->name ?? 'Usuario' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $msg->remitente->email ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $total = $msg->total_destinatarios_count ?: $msg->total_destinatarios;
                                    $leidos = $msg->leidos_count;
                                    $pct = $total > 0 ? round(($leidos / $total) * 100) : 0;
                                @endphp
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-xs font-bold {{ $leidos > 0 ? 'text-emerald-600' : 'text-slate-500' }}">
                                        {{ $leidos }} / {{ $total }} ({{ $pct }}%)
                                    </span>
                                    <div class="w-20 bg-slate-200 h-1.5 rounded-full overflow-hidden mt-1">
                                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap">
                                <div>{{ $msg->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $msg->created_at->format('H:i') }} ({{ $msg->created_at->diffForHumans() }})</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('mensajes.show', $msg) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0048FE] bg-blue-50 hover:bg-blue-100 transition-colors">
                                    <span>Ver detalle</span>
                                    <span>→</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($mensajes->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $mensajes->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-16">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0048FE] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <h4 class="text-base font-bold text-slate-800">No hay comunicados enviados aún</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    Redacta tu primer comunicado para enviar notificaciones in-app y correos electrónicos a grupos, alumnos o profesorado.
                </p>
                <div class="mt-4">
                    <a href="{{ route('mensajes.create') }}" class="stitch-btn-primary inline-flex items-center gap-2 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Redactar comunicado ahora</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

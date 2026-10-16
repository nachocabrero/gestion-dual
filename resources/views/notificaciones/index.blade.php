<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Notificaciones
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Avisos del sistema, convenios, actividad académica y proyectos</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="stitch-card p-6">
            @forelse($notificaciones as $notif)
            <div class="mb-4 pb-4 border-b border-slate-100 last:border-b-0 last:mb-0 last:pb-0 {{ $notif->es_leida ? 'opacity-60' : '' }}">
                <div class="flex items-start gap-4">
                    <!-- Icono según tipo con colores Stitch UI -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-sm
                        @if($notif->tipo === 'empresa_asignada') bg-emerald-500
                        @elseif($notif->tipo === 'estado_acuerdo') bg-amber-500
                        @elseif($notif->tipo === 'proyecto_calificado') bg-[#0048FE]
                        @elseif($notif->tipo === 'alumno_asignado') bg-indigo-500
                        @else bg-slate-600 @endif">
                        {{ strtoupper(substr($notif->tipo, 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-slate-900">{{ $notif->titulo }}</h4>
                            @if(!$notif->es_leida)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0048FE] border border-blue-100">Nueva</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $notif->mensaje }}</p>

                        <div class="flex items-center gap-4 mt-3">
                            @if($notif->enlace)
                            <a href="{{ $notif->enlace }}" class="text-xs font-bold text-[#0048FE] hover:underline inline-flex items-center gap-1">
                                <span>Ver detalle</span>
                                <span>→</span>
                            </a>
                            @endif

                            <span class="text-xs text-slate-400">
                                {{ $notif->created_at->diffForHumans() }}
                                @if($notif->expira_en)
                                · Expira: {{ $notif->expira_en->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No tienes notificaciones</h3>
                <p class="text-xs text-slate-500 mt-1">Aquí aparecerán los avisos sobre tus prácticas, ofertas y proyectos.</p>
            </div>
            @endforelse

            @if($notificaciones->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-100">
                {{ $notificaciones->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
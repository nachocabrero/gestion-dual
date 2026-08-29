<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notificaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse($notificaciones as $notif)
                    <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 {{ $notif->es_leida ? 'opacity-60' : '' }}">
                        <div class="flex items-start gap-3">
                            <!-- Icono según tipo -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold
                                @if($notif->tipo === 'empresa_asignada') bg-green-500
                                @elseif($notif->tipo === 'estado_acuerdo') bg-yellow-500
                                @elseif($notif->tipo === 'proyecto_calificado') bg-blue-500
                                @elseif($notif->tipo === 'alumno_asignado') bg-purple-500
                                @else bg-gray-500 @endif">
                                {{ strtoupper(substr($notif->tipo, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notif->titulo }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $notif->mensaje }}</p>

                                @if($notif->enlace)
                                <a href="{{ $notif->enlace }}" class="text-blue-600 hover:text-blue-800 text-xs mt-2 inline-block">Ver detalle →</a>
                                @endif

                                <div class="text-xs text-gray-400 mt-2">
                                    {{ $notif->created_at->diffForHumans() }}
                                    @if($notif->expira_en)
                                    · Expira: {{ $notif->expira_en->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        No tienes notificaciones.
                    </div>
                    @endforelse

                    <div class="mt-4">
                        {{ $notificaciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
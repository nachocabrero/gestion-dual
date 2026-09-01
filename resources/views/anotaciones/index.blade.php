<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Anotaciones / Tutorías') }}
            </h2>
            @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
            <a href="{{ route('anotaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                + Nueva Anotación
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('anotaciones.index') }}" class="mb-4 flex gap-2 flex-wrap">
                        <input type="text" name="alumno" value="{{ request('alumno') }}" placeholder="Buscar alumno..." class="border rounded px-3 py-1 text-sm flex-1 min-w-[200px]">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded text-sm hover:bg-blue-700">Filtrar</button>
                    </form>

                    <div class="space-y-4">
                        @forelse($anotaciones as $a)
                        <div class="border rounded-lg p-4 bg-white dark:bg-gray-800 border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-sm text-slate-900 dark:text-slate-100">{{ $a->titulo }}</h4>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $a->contenido }}</p>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                        Alumno: {{ $a->alumno->user->name }} ({{ $a->alumno->grupos->pluck("nombre")->join(", ") ?? '—' }})
                                        · Por: {{ $a->profesor?->user?->name ?? '—' }}
                                        · {{ $a->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="space-x-2 text-right min-w-[100px]">
                                    <a href="{{ route('anotaciones.show', $a->alumno_id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-block" title="Ver alumno">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('anotaciones.edit', $a) }}" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 inline-block" title="Editar">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                                    <form action="{{ route('anotaciones.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-800 hover:text-red-900 dark:text-red-600 dark:hover:text-red-500" title="Eliminar">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">No hay anotaciones.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $anotaciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
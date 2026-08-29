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
                        <div class="border rounded-lg p-4 {{ $a->es_publica ? 'bg-green-50 dark:bg-green-900/20 border-green-200' : 'bg-white dark:bg-gray-800 border-gray-200' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-sm">{{ $a->titulo }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $a->contenido }}</p>
                                    <div class="text-xs text-gray-400 mt-2">
                                        Alumno: {{ $a->alumno->user->name }} ({{ $a->alumno->grupo?->nombre ?? '—' }})
                                        · Por: {{ $a->profesor->user->name }}
                                        · {{ $a->created_at->diffForHumans() }}
                                        @if($a->es_publica)
                                        · <span class="text-green-600">Pública</span>
                                        @else
                                        · <span class="text-yellow-600">Privada</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-x-2">
                                    <a href="{{ route('anotaciones.show', $a->alumno_id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver alumno</a>
                                    <a href="{{ route('anotaciones.edit', $a) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Editar</a>
                                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                                    <form action="{{ route('anotaciones.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Eliminar</button>
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
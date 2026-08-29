<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Anotaciones de ' . $alumno->user->name) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold">{{ $alumno->user->name }}</h3>
                        <p class="text-sm text-gray-500">Grupo: {{ $alumno->grupo?->nombre ?? '—' }}</p>
                    </div>

                    <div class="space-y-4">
                        @forelse($anotaciones as $a)
                        <div class="border rounded-lg p-4 {{ $a->es_publica ? 'bg-green-50 dark:bg-green-900/20 border-green-200' : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-sm">{{ $a->titulo }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $a->contenido }}</p>
                                    <div class="text-xs text-gray-400 mt-2">
                                        Por: {{ $a->profesor->user->name }}
                                        · {{ $a->created_at->format('d/m/Y H:i') }}
                                        @if($a->es_publica)
                                        · <span class="text-green-600">Pública</span>
                                        @else
                                        · <span class="text-yellow-600">Privada</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-x-2">
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
                        <div class="text-center py-8 text-gray-500">No hay anotaciones para este alumno.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('anotaciones.index') }}" class="text-sm text-blue-600 hover:text-blue-800">← Volver a anotaciones</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
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
                        <p class="text-sm text-slate-600 dark:text-slate-400">Grupo: {{ $alumno->grupos->pluck("nombre")->join(", ") ?? '—' }}</p>
                        @php $media = $alumno->anotaciones()->whereNotNull('puesto')->avg('puesto'); @endphp
                        @if($media)
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Media de puesto: {{ number_format($media, 1) }}</p>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @forelse($anotaciones as $a)
                        <div class="border rounded-lg p-4 bg-white dark:bg-gray-800 border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-sm text-slate-900 dark:text-slate-100">{{ $a->titulo }}</h4>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $a->contenido }}</p>
                                    <div class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                        Por: {{ $a->profesor?->user?->name ?? '—' }}
                                        · {{ $a->created_at->format('d/m/Y H:i') }}
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
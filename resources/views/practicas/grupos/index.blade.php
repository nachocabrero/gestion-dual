<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Prácticas por Grupo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('practicas.grupos.show', $selectedGroupId) }}" class="mb-6">
                        <label for="grupo" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                            Seleccionar grupo:
                        </label>
                        <select id="grupo" name="grupo" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1" onchange="this.form.submit()">
                            <option value="">-- Elegir grupo --</option>
                            @foreach($grupos as $g)
                            <option value="{{ $g->id }}" {{ $selectedGroupId == $g->id ? 'selected' : '' }}>
                                {{ $g->nombre ?? ('Grupo ' . $g->numero) }} — {{ $g->linea?->nombre ?? '—' }}
                            </option>
                            @endforeach
                        </select>
                    </form>

                    @if($selectedGroupId)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Mostrando datos del grupo seleccionado.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

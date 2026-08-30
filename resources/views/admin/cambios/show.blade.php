<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                Detalle del Cambio
            </h2>
            <a href="{{ route('admin.cambios.index') }}" class="btn-secondary">
                ← Volver al historial
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-800 dark:text-gray-200">
                    <!-- Info general -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha</h3>
                            <p class="mt-1">{{ $cambio->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Acción</h3>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    {{ $cambio->accion === 'created' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $cambio->accion === 'updated' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $cambio->accion === 'deleted' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                    {{ $cambio->accion === 'estado_cambiado' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $cambio->accion === 'asignado' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                    {{ $cambio->accion === 'anotado' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}
                                ">
                                    {{ $cambio->accion }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuario</h3>
                            <p class="mt-1">
                                @if($cambio->usuario)
                                    {{ $cambio->usuario->nombre }} ({{ $cambio->usuario->email }})
                                @else
                                    Sistema
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo</h3>
                            <p class="mt-1">{{ class_basename($cambio->registrable_type) }} #{{ $cambio->registrable_id }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Descripción</h3>
                        <p class="text-gray-800 dark:text-gray-200">{{ $cambio->descripcion }}</p>
                    </div>

                    @if($cambio->campo)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Campo modificado</h3>
                        <p class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded">{{ $cambio->campo }}</p>
                    </div>
                    @endif

                    <!-- Antes / Después -->
                    @if($cambio->antes || $cambio->despues)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">Antes</h3>
                            <pre class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 rounded text-sm overflow-x-auto">{{ $cambio->antes ? json_encode($cambio->antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-green-600 dark:text-green-400 mb-2">Después</h3>
                            <pre class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 rounded text-sm overflow-x-auto">{{ $cambio->despues ? json_encode($cambio->despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                        </div>
                    </div>
                    @endif

                    <!-- Registrable actual -->
                    @if($cambio->registrable && $cambio->accion !== 'deleted')
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Registro actual</h3>
                        <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto">{{ json_encode($cambio->registrable, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
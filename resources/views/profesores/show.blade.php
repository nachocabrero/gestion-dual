<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">Perfil del Profesor</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('profesores.edit', $profesor) }}" class="hlanz-btn-secondary">Editar</a>
                <a href="{{ route('profesores.index') }}" class="hlanz-btn-secondary">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Datos personales -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Datos Personales</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nombre</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->user->email }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Especialidad</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->especialidad ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Familia profesional</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $profesor->familia?->nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado</span>
                        <p class="mt-0.5">
                            @if($profesor->user->is_active)
                            <span class="hlanz-badge-success">Activo</span>
                            @else
                            <span class="hlanz-badge-danger">Desactivado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Roles</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="hlanz-badge-primary">Profesor</span>
                    @if($profesor->es_tutor)
                    <span class="hlanz-badge-secondary">Tutor</span>
                    @endif
                    @if($profesor->es_coordinador_dual)
                    <span class="hlanz-badge-primary">Coordinador Dual</span>
                    @endif
                </div>
            </div>

            <!-- Grupos tutor -->
            @if($profesor->gruposTutor->count() > 0)
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Grupos Tutor</h3>
                <ul class="list-disc pl-5 font-semibold text-slate-900">
                    @foreach($profesor->gruposTutor as $grupo)
                    <li>{{ $grupo->nombre }} <span class="text-slate-500 font-normal">({{ $grupo->linea->ciclo->nombre ?? '—' }})</span></li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Grupos que imparte (grupo + asignatura) -->
            @if($profesor->gruposImpartidos->count() > 0)
            @php $asigs = $profesor->asignaturas->keyBy('id'); @endphp
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Grupos que imparte</h3>
                <ul class="list-disc pl-5 font-semibold text-slate-900">
                    @foreach($profesor->gruposImpartidos as $grupo)
                    <li>
                        {{ $grupo->nombre }}
                        @if($grupo->pivot->asignatura_id && isset($asigs[$grupo->pivot->asignatura_id]))
                            <span class="text-slate-500 font-normal">— {{ $asigs[$grupo->pivot->asignatura_id]->nombre }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Sustituciones -->
            @if($profesor->sustituciones->count() > 0)
            <div class="hlanz-card p-6">
                <h3 class="text-lg font-bold font-display text-slate-900 mb-4">Sustituciones</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Sustituto</th>
                                <th class="p-3">Asignatura</th>
                                <th class="p-3">Grupo</th>
                                <th class="p-3">Desde</th>
                                <th class="p-3">Hasta</th>
                                <th class="p-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-900">
                            @foreach($profesor->sustituciones as $sus)
                            <tr>
                                <td class="p-3">{{ $sus->profesorSustituto->user->name }}</td>
                                <td class="p-3">{{ $sus->asignatura?->nombre ?? '—' }}</td>
                                <td class="p-3">{{ $sus->grupo?->nombre ?? '—' }}</td>
                                <td class="p-3">{{ $sus->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="p-3">{{ $sus->fecha_fin->format('d/m/Y') }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('profesores.sustituciones.destroy', $sus) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 text-xs font-bold hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

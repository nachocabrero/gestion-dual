<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-bold text-xl font-display text-slate-900 leading-tight">
                    {{ __('Gestión de Usuarios') }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">Control de cuentas, roles, permisos y aprobaciones de registro</p>
            </div>
            @if(isset($pendingCount) && $pendingCount > 0)
                <a href="{{ route('admin.users.index', ['registration_status' => 'pending']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $pendingCount }} en espera de aprobación</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(isset($pendingCount) && $pendingCount > 0 && request('registration_status') !== 'pending')
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Solicitudes de registro pendientes</h4>
                        <p class="text-xs text-amber-700">Hay {{ $pendingCount }} alumno(s) esperando validación de matrícula y habilitación de cuenta.</p>
                    </div>
                </div>
                <a href="{{ route('admin.users.index', ['registration_status' => 'pending']) }}"
                   class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shrink-0 transition-colors">
                    Ver solicitudes
                </a>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <!-- Filtros -->
                <form method="GET" class="mb-6 flex gap-3 flex-wrap items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email..." class="border border-slate-300 rounded-xl px-3.5 py-2 text-sm flex-1 min-w-[220px] focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                    <select name="role" class="border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-700 bg-white">
                        <option value="">Todos los roles</option>
                        <option value="alumno" {{ request('role') == 'alumno' ? 'selected' : '' }}>Alumno</option>
                        <option value="profesor" {{ request('role') == 'profesor' ? 'selected' : '' }}>Profesor</option>
                        <option value="coordinador_dual" {{ request('role') == 'coordinador_dual' ? 'selected' : '' }}>Coordinador Dual</option>
                        <option value="empresa" {{ request('role') == 'empresa' ? 'selected' : '' }}>Empresa</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>

                    <select name="registration_status" class="border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-700 bg-white">
                        <option value="">Estado de registro</option>
                        <option value="pending" {{ request('registration_status') == 'pending' ? 'selected' : '' }}>⏳ En espera de aprobación</option>
                        <option value="approved" {{ request('registration_status') == 'approved' ? 'selected' : '' }}>✅ Aprobados</option>
                        <option value="rejected" {{ request('registration_status') == 'rejected' ? 'selected' : '' }}>❌ Rechazados</option>
                    </select>

                    <select name="active" class="border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-700 bg-white">
                        <option value="">Actividad</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivos / Desactivados</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition-colors">
                        Filtrar
                    </button>

                    @if(request()->hasAny(['search', 'role', 'active', 'registration_status']))
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
                            Limpiar
                        </a>
                    @endif
                </form>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Roles</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Grupos / Matrícula</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">RGPD</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($users as $user)
                            <tr class="{{ $user->isPendingApproval() ? 'bg-amber-50/40 hover:bg-amber-50/70' : 'hover:bg-slate-50/60' }} transition-colors">
                                <!-- Usuario -->
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Registrado: {{ $user->created_at->format('d/m/Y H:i') }}</div>
                                </td>

                                <!-- Roles -->
                                <td class="px-4 py-3.5 text-sm">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-block bg-slate-100 text-slate-700 rounded-md px-2 py-0.5 text-xs font-medium capitalize">
                                                {{ str_replace('_', ' ', $role) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Grupos / Matrícula (para alumnos) -->
                                <td class="px-4 py-3.5 text-sm">
                                    @if($user->hasRole('alumno') && $user->alumno)
                                        @if($user->alumno->grupos && $user->alumno->grupos->count() > 0)
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @foreach($user->alumno->grupos as $g)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200" title="{{ $g->linea?->ciclo?->nombre ?? '' }}">
                                                        {{ $g->nombre }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Sin grupos asignados</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-3.5 text-sm text-center whitespace-nowrap">
                                    @if($user->isPendingApproval())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <svg class="w-3.5 h-3.5 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            En espera
                                        </span>
                                    @elseif($user->isRegistrationRejected())
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                            Rechazado
                                        </span>
                                    @elseif($user->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            Desactivado
                                        </span>
                                    @endif
                                </td>

                                <!-- RGPD -->
                                <td class="px-4 py-3.5 text-sm text-center whitespace-nowrap">
                                    @if($user->hasConsentedRgpd())
                                        <span title="Consentimiento RGPD aceptado" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @else
                                        <span title="Pendiente RGPD" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-3.5 text-sm text-center whitespace-nowrap space-x-1.5">
                                    @if($user->isPendingApproval())
                                        <!-- Botón Aceptar -->
                                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors" title="Aceptar registro y habilitar cuenta">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Aceptar</span>
                                            </button>
                                        </form>

                                        <!-- Botón Rechazar -->
                                        <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas rechazar este registro?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition-colors" title="Rechazar solicitud">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Rechazar</span>
                                            </button>
                                        </form>
                                    @else
                                        @if($user->is_active)
                                            <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 transition-colors" title="Desactivar">
                                                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition-colors" title="Reactivar">
                                                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar definitivamente este usuario? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-700 rounded-lg hover:bg-slate-100 transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    No se encontraron usuarios con los criterios de búsqueda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
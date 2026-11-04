<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight">
                    Empresas Colaboradoras
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Directorio de entidades para prácticas FCT y Dual</p>
            </div>
            <a href="{{ route('empresas.create') }}" class="stitch-btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Empresa</span>
            </a>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{
        modalComOpen: false,
        empresaId: null,
        empresaNombre: '',
        comFormAction: '',
        abrirNuevaCom(id, nombre) {
            this.empresaId = id;
            this.empresaNombre = nombre;
            this.comFormAction = '{{ url('empresas') }}/' + id + '/comunicaciones';
            this.modalComOpen = true;
        }
    }">
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="stitch-toolbar">
            <form method="GET" action="{{ route('empresas.index') }}" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, CIF, email..." class="stitch-input !pl-10">
                </div>

                <div class="w-full md:w-48">
                    <select name="active" class="stitch-input text-xs">
                        <option value="1" {{ request('active', '1') === '1' ? 'selected' : '' }}>Solo Activas (por defecto)</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Solo Inactivas</option>
                        <option value="todas" {{ request('active') === 'todas' ? 'selected' : '' }}>Todas las empresas</option>
                    </select>
                </div>

                <div class="w-full md:w-56">
                    <select name="profesor_id" class="stitch-input text-xs">
                        <option value="">Seguimiento (Todos)</option>
                        <option value="sin_asignar" {{ request('profesor_id') === 'sin_asignar' ? 'selected' : '' }}>— Sin profesor asignado —</option>
                        @foreach($profesores as $prof)
                        <option value="{{ $prof->id }}" {{ request('profesor_id') == $prof->id ? 'selected' : '' }}>
                            {{ $prof->user->name ?? 'Profesor #' . $prof->id }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'profesor_id', 'familia']) || (request()->has('active') && request('active') !== '1'))
                    <a href="{{ route('empresas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>

        @if($empresas->total() > 0)
        <!-- Pagination Top -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-2 py-1 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-300 shadow-sm text-slate-800 text-xs font-semibold">
                    <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Página</span>
                    <span class="inline-flex items-center justify-center min-w-[1.75rem] px-1.5 py-0.5 rounded bg-white border border-slate-400 text-black font-black text-xs shadow-xs">{{ $empresas->currentPage() }}</span>
                    <span>de</span>
                    <span class="inline-flex items-center justify-center min-w-[1.75rem] px-1.5 py-0.5 rounded bg-white border border-slate-400 text-black font-black text-xs shadow-xs">{{ $empresas->lastPage() }}</span>
                </span>
                <span class="text-slate-500 font-medium">({{ $empresas->total() }} {{ $empresas->total() === 1 ? 'empresa' : 'empresas' }})</span>
            </div>
            @if($empresas->hasPages())
            <div>
                {{ $empresas->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- Table Container for Desktop -->
        <div class="stitch-table-container hidden sm:block">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Contacto</th>
                        <th>Seguimiento</th>
                        <th class="text-center">Tutores</th>
                        <th class="text-center">Ofertas</th>
                        <th class="text-center">Prácticas</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($empresa->is_active)
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-sm shrink-0" title="Empresa activa">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                @else
                                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center shadow-sm shrink-0" title="Empresa inactiva">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                @endif
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">{{ $empresa->nombre }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-xs">{{ $empresa->direccion }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($empresa->email)
                                <p class="text-sm font-medium">
                                    <a href="mailto:{{ $empresa->email }}" class="text-[#0048FE] hover:underline inline-flex items-center gap-1.5" title="Enviar correo a {{ $empresa->email }}">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="truncate max-w-[170px]">{{ $empresa->email }}</span>
                                    </a>
                                </p>
                            @else
                                <p class="text-slate-400 text-sm">—</p>
                            @endif

                            @if($empresa->telefono || $empresa->telefono_2)
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 flex-wrap">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    @if($empresa->telefono && $empresa->telefono_2)
                                        <a href="tel:{{ $empresa->telefono }}" class="hover:text-slate-900" title="Llamar al {{ $empresa->telefono }}">{{ $empresa->telefono }}</a>
                                        <span class="text-slate-400">-</span>
                                        <a href="tel:{{ $empresa->telefono_2 }}" class="hover:text-slate-900" title="Llamar al {{ $empresa->telefono_2 }}">{{ $empresa->telefono_2 }}</a>
                                    @elseif($empresa->telefono)
                                        <a href="tel:{{ $empresa->telefono }}" class="hover:text-slate-900" title="Llamar al {{ $empresa->telefono }}">{{ $empresa->telefono }}</a>
                                    @else
                                        <a href="tel:{{ $empresa->telefono_2 }}" class="hover:text-slate-900" title="Llamar al {{ $empresa->telefono_2 }}">{{ $empresa->telefono_2 }}</a>
                                    @endif
                                </p>
                            @else
                                <p class="text-xs text-slate-400 mt-1">—</p>
                            @endif
                        </td>
                        <td>
                            @if($empresa->profesor && $empresa->profesor->user)
                            <div class="flex items-center gap-1.5 text-slate-800 text-xs font-semibold">
                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="truncate max-w-[150px]" title="{{ $empresa->profesor->user->name }}">{{ $empresa->profesor->user->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-xs italic block">Sin asignar</span>
                            @endif

                            @if($empresa->ultimaComunicacion)
                            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1" title="Fecha de la última comunicación">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Última com: <strong class="text-slate-700 font-semibold">{{ $empresa->ultimaComunicacion->fecha?->format('d/m/Y') }}</strong></span>
                            </div>
                            @else
                            <span class="text-[11px] text-slate-400 italic block mt-0.5">Sin comunicaciones</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="stitch-badge-neutral">{{ $empresa->tutoresLaborales->count() }}</span>
                        </td>
                        <td class="text-center">
                            <span class="stitch-badge-info">{{ $empresa->ofertasPracticas->count() }}</span>
                        </td>
                        <td class="text-center">
                            <span class="stitch-badge-success">{{ $empresa->practicas->count() }}</span>
                        </td>
                        <td class="text-right space-x-1">
                            @php
                                $esSeguimiento = (auth()->user()->profesor && $empresa->profesor_id === auth()->user()->profesor->id);
                                $puedeRegistrar = $esSeguimiento || auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);
                            @endphp
                            @if($puedeRegistrar)
                            <button type="button"
                                    @click="abrirNuevaCom({{ $empresa->id }}, {{ json_encode($empresa->nombre) }})"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors inline-flex"
                                    title="Añadir registro de comunicación">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </button>
                            @endif
                            <a href="{{ route('empresas.show', $empresa) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0048FE] hover:bg-slate-100 transition-colors inline-flex" title="Ver Detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('empresas.edit', $empresa) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors inline-flex" title="Editar Empresa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($empresa->is_active)
                            <form action="{{ route('empresas.deactivate', $empresa) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors inline-flex" title="Desactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('empresas.reactivate', $empresa) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 transition-colors inline-flex" title="Reactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar permanentemente esta empresa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-700 hover:bg-slate-100 transition-colors inline-flex" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <p class="text-sm font-medium">No hay empresas que coincidan con la búsqueda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:hidden">
            @forelse($empresas as $empresa)
            <div class="stitch-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($empresa->is_active)
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-sm shrink-0" title="Empresa activa">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center shadow-sm shrink-0" title="Empresa inactiva">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $empresa->nombre }}</h4>
                            <p class="text-xs text-slate-500 font-mono">{{ $empresa->cif }}</p>
                        </div>
                    </div>
                    @if($empresa->is_active)
                    <span class="stitch-badge-success">Activa</span>
                    @else
                    <span class="stitch-badge-danger">Inactiva</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100 space-y-1.5">
                    <p>
                        <strong>Seguimiento:</strong> {{ $empresa->profesor?->user?->name ?? 'Sin asignar' }}
                        @if($empresa->ultimaComunicacion)
                            <span class="block text-[11px] text-slate-500 mt-0.5">
                                Última com: <strong>{{ $empresa->ultimaComunicacion->fecha?->format('d/m/Y') }}</strong>
                            </span>
                        @endif
                    </p>
                    <p>
                        <strong>Email:</strong>
                        @if($empresa->email)
                            <a href="mailto:{{ $empresa->email }}" class="text-[#0048FE] hover:underline">{{ $empresa->email }}</a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </p>
                    <p>
                        <strong>Teléfono:</strong>
                        @if($empresa->telefono && $empresa->telefono_2)
                            <a href="tel:{{ $empresa->telefono }}" class="text-slate-800 hover:underline">{{ $empresa->telefono }}</a>
                            <span class="text-slate-400">-</span>
                            <a href="tel:{{ $empresa->telefono_2 }}" class="text-slate-800 hover:underline">{{ $empresa->telefono_2 }}</a>
                        @elseif($empresa->telefono)
                            <a href="tel:{{ $empresa->telefono }}" class="text-slate-800 hover:underline">{{ $empresa->telefono }}</a>
                        @elseif($empresa->telefono_2)
                            <a href="tel:{{ $empresa->telefono_2 }}" class="text-slate-800 hover:underline">{{ $empresa->telefono_2 }}</a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 flex-wrap">
                    @php
                        $esSeguimientoMobile = (auth()->user()->profesor && $empresa->profesor_id === auth()->user()->profesor->id);
                        $puedeRegistrarMobile = $esSeguimientoMobile || auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);
                    @endphp
                    @if($puedeRegistrarMobile)
                    <button type="button"
                            @click="abrirNuevaCom({{ $empresa->id }}, {{ json_encode($empresa->nombre) }})"
                            class="stitch-btn-secondary text-xs py-1.5 px-3 flex items-center gap-1 text-blue-700 bg-blue-50 border-blue-200 hover:bg-blue-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Comunicación</span>
                    </button>
                    @endif
                    <a href="{{ route('empresas.show', $empresa) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Ver Detalle</a>
                    <a href="{{ route('empresas.edit', $empresa) }}" class="stitch-btn-secondary text-xs py-1.5 px-3">Editar</a>
                </div>
            </div>
            @empty
            <div class="stitch-card p-8 text-center text-slate-400">
                <p class="text-sm">No hay empresas.</p>
            </div>
            @endforelse
        </div>

        @if($empresas->total() > 0)
        <!-- Pagination Bottom -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 px-2 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-300 shadow-sm text-slate-800 text-xs font-semibold">
                    <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Página</span>
                    <span class="inline-flex items-center justify-center min-w-[1.75rem] px-1.5 py-0.5 rounded bg-white border border-slate-400 text-black font-black text-xs shadow-xs">{{ $empresas->currentPage() }}</span>
                    <span>de</span>
                    <span class="inline-flex items-center justify-center min-w-[1.75rem] px-1.5 py-0.5 rounded bg-white border border-slate-400 text-black font-black text-xs shadow-xs">{{ $empresas->lastPage() }}</span>
                </span>
                <span class="text-slate-500 font-medium">({{ $empresas->total() }} {{ $empresas->total() === 1 ? 'empresa' : 'empresas' }})</span>
            </div>
            @if($empresas->hasPages())
            <div>
                {{ $empresas->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- Modal para Añadir Comunicación -->
        <div x-show="modalComOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
             x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-100"
                 @click.away="modalComOpen = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="text-base font-bold font-display text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Nueva Comunicación
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">Empresa: <strong class="text-slate-800" x-text="empresaNombre"></strong></p>
                    </div>
                    <button type="button" @click="modalComOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="comFormAction" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="stitch-label block mb-1.5 font-bold text-xs text-slate-700">Canal de Contacto *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                <input type="radio" name="tipo" value="telefono" checked class="text-blue-600 focus:ring-blue-500">
                                <span>Teléfono</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                <input type="radio" name="tipo" value="email" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Email</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer text-xs font-medium text-slate-800">
                                <input type="radio" name="tipo" value="presencial" class="text-emerald-600 focus:ring-emerald-500">
                                <span>Presencial</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Motivo de la Comunicación *</label>
                        <textarea name="motivo" required rows="3" placeholder="Ej. Llamada para consultar disponibilidad de plazas..." class="stitch-input bg-white text-xs w-full"></textarea>
                    </div>

                    <div>
                        <label class="stitch-label block mb-1 font-bold text-xs text-slate-700">Respuesta Obtenida (opcional)</label>
                        <textarea name="respuesta" rows="3" placeholder="Ej. Indican que recibirán 2 alumnos en el segundo trimestre..." class="stitch-input bg-white text-xs w-full"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="modalComOpen = false" class="stitch-btn-secondary text-xs py-2 px-3">Cancelar</button>
                        <button type="submit" class="stitch-btn-primary text-xs py-2 px-4">Guardar Comunicación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
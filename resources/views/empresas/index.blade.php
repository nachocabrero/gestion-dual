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

    <div class="space-y-6">
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, CIF, email..." class="stitch-input pl-10">
                </div>

                <div class="w-full md:w-48">
                    <select name="active" class="stitch-input text-xs">
                        <option value="">Estado (Todas)</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivas</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="stitch-btn-primary text-xs py-2 px-4 w-full md:w-auto">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'active']))
                    <a href="{{ route('empresas.index') }}" class="stitch-btn-secondary text-xs py-2 px-3">
                        Limpiar
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Container for Desktop -->
        <div class="stitch-table-container hidden sm:block">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>CIF</th>
                        <th>Contacto</th>
                        <th class="text-center">Tutores</th>
                        <th class="text-center">Ofertas</th>
                        <th class="text-center">Prácticas</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($empresa->nombre, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">{{ $empresa->nombre }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-xs">{{ $empresa->direccion }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-800 font-mono text-xs font-semibold px-2 py-1 rounded-md bg-slate-100 border border-slate-200">{{ $empresa->cif }}</span>
                        </td>
                        <td>
                            <p class="text-slate-800 font-medium text-sm">{{ $empresa->email ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $empresa->telefono ?? '—' }}</p>
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
                        <td class="text-center">
                            @if($empresa->is_active)
                            <span class="stitch-badge-success">Activa</span>
                            @else
                            <span class="stitch-badge-danger">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-right space-x-1">
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
                        <td colspan="8" class="py-12 text-center text-slate-400">
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
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            {{ strtoupper(substr($empresa->nombre, 0, 1)) }}
                        </div>
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

                <div class="text-xs text-slate-600 pt-2 border-t border-slate-100 space-y-1">
                    <p><strong>Email:</strong> {{ $empresa->email ?? '—' }}</p>
                    <p><strong>Teléfono:</strong> {{ $empresa->telefono ?? '—' }}</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
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

        <div class="pt-2">
            {{ $empresas->links() }}
        </div>
    </div>
</x-app-layout>
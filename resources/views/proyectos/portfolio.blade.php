@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0F172A] via-slate-900 to-[#0048FE] text-white p-8 sm:p-12 shadow-2xl">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#0048FE]/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold backdrop-blur-md border border-blue-400/30">
                ⭐ Proyectos Destacados del Alumnado
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold font-display tracking-tight text-white leading-tight">
                Innovación Técnica & Portfolio Tecnológico
            </h1>
            <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                Descubre los proyectos finales desarrollados por los estudiantes de los Ciclos Formativos de Grado Superior de Informática, Grado Medio de SMR y Grado Básico de Informática del IES Hermenegildo Lanz.
            </p>
        </div>
        <!-- Public Stats Banner -->
    <div class="flex justify-center mb-8">
        <div class="hlanz-card p-6 text-center max-w-sm w-full bg-emerald-50/50 border border-emerald-100 shadow-md">
            <p class="text-5xl font-extrabold font-display text-emerald-600 mb-2">{{ $totalEmpresas ?? 0 }}</p>
            <p class="text-sm font-bold uppercase tracking-wider text-emerald-800">Empresas colaboradoras</p>
            <p class="text-xs text-emerald-600/70 mt-1">Acogiendo a nuestro alumnado en FCT y Formación Dual</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="hlanz-card p-5">
        <form method="GET" action="{{ route('portfolio') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por título o descripción..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] px-3.5 py-2.5">
            </div>
            <div>
                <select name="ciclo" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] px-3.5 py-2.5">
                    <option value="">Todos los ciclos</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}" {{ request('ciclo') == $ciclo->id ? 'selected' : '' }}>
                            {{ $ciclo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="curso" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] px-3.5 py-2.5">
                    <option value="">Todos los cursos</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="hlanz-btn-primary flex-1">
                    Filtrar Proyectos
                </button>
                @if(request()->hasAny(['search', 'ciclo', 'curso']))
                <a href="{{ route('portfolio') }}" class="hlanz-btn-secondary">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Projects List Grouped by Cycle -->
    @foreach($proyectosAgrupados as $cicloNombre => $cicloProyectos)
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#0048FE]"></span>
                <h3 class="text-xl font-bold font-display text-slate-900">{{ $cicloNombre }}</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">{{ $cicloProyectos->count() }} proyectos</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($cicloProyectos as $proyecto)
            <div class="hlanz-card hlanz-card-hover overflow-hidden flex flex-col justify-between group">
                <div>
                    @if($proyecto->imagenes->count() > 0)
                    <div class="relative overflow-hidden h-48 bg-slate-100">
                        <img src="{{ Storage::url($proyecto->imagenes->first()->url) }}"
                             alt="{{ $proyecto->titulo }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if($proyecto->es_destacado)
                        <div class="absolute top-3 right-3 bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <span>★</span> Destacado
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="h-32 bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center p-4 text-white relative">
                        <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        @if($proyecto->es_destacado)
                        <div class="absolute top-3 right-3 bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <span>★</span> Destacado
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="p-5 space-y-3">
                        <h4 class="text-lg font-bold font-display text-slate-900 group-hover:text-[#0048FE] transition-colors leading-snug">
                            {{ $proyecto->titulo }}
                        </h4>
                        <p class="text-xs font-semibold text-[#0048FE]">Alumno: {{ $proyecto->alumno->user->name ?? 'Estudiante' }}</p>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                            {{ Str::limit($proyecto->descripcion, 160) }}
                        </p>
                    </div>
                </div>

                <div class="p-5 pt-0 space-y-3">
                    <div class="flex items-center gap-2">
                        @if($proyecto->enlace_repositorio)
                        <a href="{{ $proyecto->enlace_repositorio }}" target="_blank"
                           class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 text-center transition-colors">
                            📦 Repositorio
                        </a>
                        @endif
                        @if($proyecto->enlace_despliegue)
                        <a href="{{ $proyecto->enlace_despliegue }}" target="_blank"
                           class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-white bg-[#0048FE] hover:bg-[#003CD5] text-center transition-colors">
                            🚀 Despliegue
                        </a>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">{{ $proyecto->cursoAcademico->nombre ?? '2026/2027' }}</span>
                        <span class="font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            {{ $proyecto->calificacion }}/10
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @if($proyectosAgrupados->isEmpty())
    <div class="hlanz-card p-12 text-center text-slate-400 space-y-3">
        <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p class="text-base font-bold text-slate-700">No se encontraron proyectos destacados</p>
        <p class="text-xs">Prueba a modificar los términos de búsqueda o filtros seleccionados.</p>
    </div>
    @endif

    <!-- Company Contact Section -->
    <div id="empresas" class="mt-16 bg-white rounded-3xl p-8 sm:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

        <div class="max-w-3xl mx-auto">
            <div class="text-center space-y-4 mb-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-100">
                    🤝 Empresas
                </span>
                <h2 class="text-3xl font-extrabold font-display text-slate-900">¿Quieres colaborar con nosotros?</h2>
                <p class="text-slate-500 text-base">Si eres una empresa y estás interesada en acoger alumnado en prácticas (FCT) o formación Dual, déjanos tus datos y contactaremos contigo.</p>
            </div>

            @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium text-center">
                ✅ {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('contacto.empresa') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-semibold text-slate-700">Nombre de la Empresa *</label>
                        <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('nombre') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="contacto" class="block text-sm font-semibold text-slate-700">Persona de Contacto *</label>
                        <input type="text" name="contacto" id="contacto" required value="{{ old('contacto') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('contacto') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2 sm:col-span-2">
                        <label for="direccion" class="block text-sm font-semibold text-slate-700">Dirección Física *</label>
                        <input type="text" name="direccion" id="direccion" required value="{{ old('direccion') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('direccion') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email de Contacto *</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('email') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="telefono" class="block text-sm font-semibold text-slate-700">Teléfono *</label>
                        <input type="text" name="telefono" id="telefono" required value="{{ old('telefono') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('telefono') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2 sm:col-span-2">
                        <label for="web" class="block text-sm font-semibold text-slate-700">Página Web</label>
                        <input type="url" name="web" id="web" value="{{ old('web') }}" placeholder="https://..."
                               class="w-full rounded-xl border-slate-300 focus:border-[#0048FE] focus:ring-[#0048FE] text-sm">
                        @error('web') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <button type="submit" class="hlanz-btn-primary px-8 py-3 text-base">
                        Enviar Solicitud de Colaboración
                    </button>
                    <p class="text-xs text-slate-400 mt-4 max-w-lg mx-auto">
                        Al enviar este formulario, aceptas que los datos facilitados sean tratados por el Departamento de Informática del IES Hermenegildo Lanz para gestionar posibles acuerdos de colaboración.
                    </p>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
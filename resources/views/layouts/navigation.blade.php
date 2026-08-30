<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Lanz" class="h-9 w-auto">
                        <span class="font-bold font-display text-slate-900 hidden md:inline">IES H. Lanz</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Notificaciones -->
                    <x-nav-link :href="route('notificaciones.index')" :active="request()->routeIs('notificaciones.*')">
                        {{ __('Notificaciones') }}
                        @php $notifCount = \App\Models\Notificacion::contarNoLeidas(auth()->id()); @endphp
                        @if($notifCount > 0)
                        <span class="ms-2 bg-rose-500 text-white text-xs font-bold rounded-full px-2 py-0.5">{{ $notifCount }}</span>
                        @endif
                    </x-nav-link>

                    @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]))
                    <x-nav-link :href="route('alumnos.index')" :active="request()->routeIs('alumnos.*')">
                        {{ __('Alumnado') }}
                    </x-nav-link>
                    <x-nav-link :href="route('profesores.index')" :active="request()->routeIs('profesores.*')">
                        {{ __('Profesorado') }}
                    </x-nav-link>
                    <x-nav-link :href="route('calificaciones.index')" :active="request()->routeIs('calificaciones.*')">
                        {{ __('Calificaciones') }}
                    </x-nav-link>
                    <x-nav-link :href="route('anotaciones.index')" :active="request()->routeIs('anotaciones.*')">
                        {{ __('Anotaciones') }}
                    </x-nav-link>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]))
                    <x-nav-link :href="route('empresas.index')" :active="request()->routeIs('empresas.*')">
                        {{ __('Empresas') }}
                    </x-nav-link>
                    @endif

                    <x-nav-link :href="route('ofertas.index')" :active="request()->routeIs('ofertas.*')">
                        {{ __('Ofertas FCT') }}
                    </x-nav-link>

                    <x-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')">
                        {{ __('Proyectos') }}
                    </x-nav-link>

                    @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Admin') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-xl text-slate-700 bg-slate-50 hover:bg-slate-100 focus:outline-none transition ease-in-out duration-150">
                            <div class="w-7 h-7 rounded-lg bg-[#0048FE] text-white flex items-center justify-center font-bold text-xs me-2">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('portfolio')">
                            {{ __('Portfolio Público') }}
                        </x-dropdown-link>

                        @if(auth()->check() && auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                        <x-dropdown-link :href="route('admin.dashboard')">
                            {{ __('Panel Admin') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('admin.cambios.index')">
                            {{ __('Historial Auditoría') }}
                        </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-50 border-b border-slate-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('notificaciones.index')" :active="request()->routeIs('notificaciones.*')">
                {{ __('Notificaciones') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ofertas.index')" :active="request()->routeIs('ofertas.*')">
                {{ __('Ofertas FCT') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')">
                {{ __('Proyectos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portfolio')">
                {{ __('Portfolio Público') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-slate-200 px-4">
            <div class="font-bold text-sm text-slate-800">{{ Auth::user()->name }}</div>
            <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
<!doctype html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Zahara')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0B2A5B">
    
    <!-- Fuente Inter (Esencial para el look moderno) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/icono.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- FONDO BASE --}}
<body class="h-full antialiased bg-[#0B1121] text-slate-800 font-sans selection:bg-indigo-500 selection:text-white overflow-hidden">
    
    <div x-data="{ sidebarOpen: false }" class="flex h-full w-full relative">

        {{-- FONDO DECORATIVO --}}
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-indigo-600/20 blur-[120px] rounded-full mix-blend-screen opacity-40"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-blue-600/10 blur-[120px] rounded-full mix-blend-screen opacity-40"></div>
        </div>

        {{-- BACKDROP MÓVIL --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- ================= SIDEBAR (Oscuro / Glass) ================= -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed lg:static inset-y-0 left-0 z-50 w-[260px] flex flex-col transition-transform duration-300 ease-out lg:translate-x-0 sidebar-glass"
        >
            <!-- Logo -->
            <div class="h-20 flex items-center px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.svg') }}" alt="Zahara" class="h-8 w-8 object-contain brightness-0 invert">
                    <span class="text-xl font-bold text-white tracking-tight">Zahara</span>
                </a>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-hide">
                @php
                    $navItem = function($route, $label, $icon) {
                        $active = request()->routeIs($route.'*'); // Asterisco para sub-rutas
                        
                        $classes = $active 
                            ? 'bg-white/10 text-white shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1)]' 
                            : 'text-slate-400 hover:text-white hover:bg-white/5';
                        
                        return '
                        <a href="'.route($route).'" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 '.$classes.'">
                            <span class="'.($active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300').' transition-colors">
                                '.$icon.'
                            </span>
                            '.$label.'
                        </a>';
                    };

                    // Iconos SVG (Phosphor style / Outline)
                    $iHome    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
                    $iEds     = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><path d="M7 7h10"></path><path d="M7 12h10"></path><path d="M7 17h10"></path></svg>'; 
                    $iClients = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
                @endphp

                <div class="px-3 mb-2 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Principal</div>
                
                {!! $navItem('dashboard', 'Inicio', $iHome) !!}
                {!! $navItem('eds.index', 'Estaciones EDS', $iEds) !!}
                {!! $navItem('clientes.index', 'Clientes', $iClients) !!}

            </nav>

            <!-- Perfil Usuario (Bottom) -->
            <div class="p-4 border-t border-white/5">
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
                    <div class="h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xs ring-2 ring-indigo-500/30">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="text-xs text-slate-400 truncate">Administrador</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="text-slate-500 hover:text-red-400 transition-colors p-1" title="Cerrar Sesión">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ================= MAIN CONTENT (Tarjeta Flotante) ================= -->
        <main class="flex-1 flex flex-col h-full relative z-10 lg:py-3 lg:pr-3 transition-all duration-300">
            
            <div class="flex-1 bg-[#F8FAFC] lg:rounded-3xl shadow-2xl shadow-black/50 overflow-hidden flex flex-col border border-white/10 theme-light relative isolate">
                
                <!-- Header Sticky -->
                <header class="h-16 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200/60 supports-[backdrop-filter]:bg-white/60">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-indigo-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        
                        <!-- Breadcrumb Area -->
                        <nav class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                            @hasSection('breadcrumb')
                                @yield('breadcrumb')
                            @else
                                <span class="font-medium text-slate-800">Panel Principal</span>
                            @endif
                        </nav>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-3">
                         <!-- Input de búsqueda global (solo visual) -->
                        <div class="hidden md:flex items-center w-64">
                            <input type="text" placeholder="Buscar en Zahara..." class="input-pill bg-slate-50 border-transparent focus:bg-white h-9 text-xs">
                        </div>
                        
                        <button class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors rounded-full hover:bg-slate-100">
                            <span class="absolute top-2.5 right-2.5 h-1.5 w-1.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </button>
                    </div>
                </header>

                <!-- Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-8 scroll-smooth scrollbar-hide">
                    <div class="max-w-7xl mx-auto animate-enter">
                        <!-- Título de Página y Acciones -->
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">@yield('page_title')</h1>
                                @hasSection('page_subtitle')
                                    <p class="text-sm text-slate-500 mt-1">@yield('page_subtitle')</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                @yield('page_actions')
                            </div>
                        </div>

                        <!-- Flash Messages -->
                        @if(session('ok'))
                            <div x-data="{show: true}" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                                 class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 border border-emerald-100 shadow-sm">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ session('ok') }}
                            </div>
                        @endif

                        <!-- Contenido -->
                        @yield('content')
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ========================================== -->
    <!-- GLOBAL CONFIRMATION MODAL (ZAHARA STYLE)   -->
    <!-- ========================================== -->
    <div x-data="{ 
            open: false, 
            title: '', 
            message: '', 
            targetForm: null,
            confirmAction() {
                this.targetForm.submit();
                this.open = false;
            }
         }"
         @confirm-action.window="
            open = true; 
            title = $event.detail.title; 
            message = $event.detail.message; 
            targetForm = $event.detail.form;
         "
         x-show="open"
         x-cloak
         class="relative z-[9999]"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">

        <!-- Backdrop -->
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <div x-show="open"
                     @click.away="open = false"
                     @keydown.escape.window="open = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start gap-4">
                            <!-- Icono -->
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 ring-8 ring-red-50">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            
                            <div class="mt-3 text-center sm:ml-2 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title" x-text="title"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500" x-text="message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100 gap-3">
                        <button type="button" 
                                @click="confirmAction()"
                                class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-transform active:scale-95">
                            Confirmar Acción
                        </button>
                        <button type="button" 
                                @click="open = false"
                                class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-transform active:scale-95">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zahara Tech')</title>

    {{-- Favicon SVG Anti-Caché --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}?v=3">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .input-pill {
            @apply w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none;
        }
        .btn-primary {
            @apply bg-indigo-600 text-white hover:bg-indigo-700 active:bg-indigo-800 rounded-xl px-4 py-2 font-semibold transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5;
        }
        .btn-secondary {
            @apply bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:text-slate-800 rounded-xl px-4 py-2 font-semibold transition-all;
        }
        .animate-enter {
            animation: enter 0.4s ease-out forwards;
        }
        @keyframes enter {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#F8FAFC] font-sans text-slate-600 antialiased" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden" style="display: none;"></div>

    <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 shadow-2xl md:shadow-none transform transition-transform duration-300 md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="h-20 flex items-center px-8 border-b border-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-200">
                    Z
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 text-lg leading-tight">Zahara</h1>
                    <p class="text-[10px] font-bold text-indigo-500 tracking-widest uppercase">Technology</p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-5rem)]">
            @php
                $navItem = function($route, $label, $icon) {
                    $active = request()->routeIs($route.'*');
                    $classes = $active 
                        ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-100' 
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900';
                    return '<a href="'.route($route).'" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all group '.$classes.'">
                                <span class="'.($active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600').'">'.$icon.'</span>
                                '.$label.'
                            </a>';
                };
                
                $iHome = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>';
                $iEds = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>';
                $iClients = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
                $iUsers = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
                $iRoles = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
                $iAbonos = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/></svg>';
            @endphp

            <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Operación</div>
            {!! $navItem('dashboard', 'Inicio', $iHome) !!}
            
            {!! $navItem('eds.index', 'Estaciones EDS', $iEds) !!}
            {!! $navItem('clientes.index', 'Clientes', $iClients) !!}
            
            {{-- CAMBIO DE NOMBRE A 'CUENTAS' --}}
            {!! $navItem('facturas.index', 'Cuentas', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>') !!}
            
            {!! $navItem('abonos.index', 'Abonos / Recibos', $iAbonos) !!}

            <div class="mt-8 px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Administración</div>
            {!! $navItem('users.index', 'Usuarios', $iUsers) !!}
            {!! $navItem('roles.index', 'Roles y Permisos', $iRoles) !!}
            
        </nav>
        
        <div class="absolute bottom-0 w-full border-t border-slate-100 p-4 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email ?? 'user@zahara.com' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="Cerrar Sesión">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="md:pl-72 min-h-screen flex flex-col transition-all duration-300">
        <!-- Mobile Header -->
        <header class="h-16 md:hidden flex items-center justify-between px-4 bg-white border-b border-slate-100 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <span class="font-bold text-lg text-slate-900">Zahara</span>
            </div>
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
        </header>

        <div class="flex-1 p-4 md:p-8 max-w-7xl mx-auto w-full">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        @yield('breadcrumb')
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">@yield('page_title')</h2>
                    @hasSection('page_subtitle')
                        <p class="text-sm text-slate-500 mt-1">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('page_actions')
                </div>
            </div>

            @if(session('ok'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                     class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm animate-enter">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">{{ session('ok') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded-xl shadow-sm animate-enter">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-bold text-sm">Por favor revisa los siguientes errores:</p>
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1 ml-1 text-red-700/80">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <div x-data="{ open: false, title: '', message: '', form: null }"
         @confirm-action.window="open = true; title = $event.detail.title; message = $event.detail.message; form = $event.detail.form"
         class="relative z-[60]" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;" x-show="open">
        
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" x-text="title"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500" x-text="message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                        <button type="button" @click="form.submit(); open = false" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Confirmar Acción</button>
                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
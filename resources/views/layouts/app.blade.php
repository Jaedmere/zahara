<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zahara Tech')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=3">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .input-pill { @apply w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none; }
        .btn-primary { @apply bg-indigo-600 text-white hover:bg-indigo-700 active:bg-indigo-800 rounded-xl px-4 py-2 font-semibold transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5; }
        .btn-secondary { @apply bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:text-slate-800 rounded-xl px-4 py-2 font-semibold transition-all; }
        .animate-enter { animation: enter 0.4s ease-out forwards; }
        @keyframes enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

{{-- SISTEMA GLOBAL DE UI Y NOTIFICACIONES --}}
<body class="bg-[#F8FAFC] font-sans text-slate-600 antialiased min-h-screen flex flex-col md:flex-row overflow-x-hidden"
      x-data="globalSystem()" x-init="initSystem()">

    <!-- Overlay Móvil -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden" style="display: none;"></div>

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 shadow-2xl md:shadow-none transform transition-transform duration-300 md:translate-x-0 flex flex-col h-full"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="h-20 flex-none flex items-center justify-between px-6 border-b border-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-200">Z</div>
                <div><h1 class="font-bold text-slate-900 text-lg leading-tight">Zahara</h1><p class="text-[10px] font-bold text-indigo-500 tracking-widest uppercase">Combured</p></div>
            </div>
            
            {{-- CAMPANITA DESKTOP (Visible solo en sidebar) --}}
            <a href="{{ route('notificaciones.index') }}" class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors hidden md:block" title="Notificaciones">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                <span x-show="notificationCount > 0" x-text="notificationCount" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white" style="display: none;"></span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1 no-scrollbar">
            @php
                $navItem = fn($r, $l, $i) => '<a href="'.route($r).'" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all group '.(request()->routeIs($r.'*') ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900').'"><span class="'.(request()->routeIs($r.'*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600').'">'.$i.'</span>'.$l.'</a>';
                
                // ICONOS PRINCIPALES
                $iCartera = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
            @endphp

            <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Operación</div>
            {!! $navItem('dashboard', 'Inicio', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>') !!}
            {!! $navItem('eds.index', 'Estaciones EDS', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>') !!}
            {!! $navItem('clientes.index', 'Clientes', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>') !!}
            
            <!-- ACORDEÓN CARTERA -->
            <div x-data="{ open: {{ request()->routeIs('cartera*') || request()->routeIs('cartera_eds*') || request()->routeIs('cartera_cuentas*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="flex items-center justify-between w-full gap-3 px-4 py-3 rounded-xl font-medium transition-all group cursor-pointer select-none" :class="open ? 'bg-slate-50 text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'">
                    <div class="flex items-center gap-3"><span :class="open ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'">{!! $iCartera !!}</span><span>Estado de Cartera</span></div>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                
                <!-- SUBMENÚ CON ICONOS -->
                <div x-show="open" x-collapse x-cloak class="pl-11 pr-2 space-y-1">
                    <a href="{{ route('cartera.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('cartera.index') ? 'text-indigo-700 font-medium bg-indigo-50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Consolidado Combured
                    </a>
                    <a href="{{ route('cartera_eds.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('cartera_eds.index') ? 'text-indigo-700 font-medium bg-indigo-50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Consolidado por EDS
                    </a>
                    <a href="{{ route('cartera_cuentas.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('cartera_cuentas.index') ? 'text-indigo-700 font-medium bg-indigo-50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Consolidado por Cuenta
                    </a>
                </div>
            </div>

            {!! $navItem('facturas.index', 'Cuentas', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>') !!}
            {!! $navItem('abonos.index', 'Abonos / Recibos', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/></svg>') !!}
            {!! $navItem('seguimientos.index', 'Seguimientos CRM', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>') !!}

            <div class="mt-8 px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Administración</div>
            {!! $navItem('users.index', 'Usuarios', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>') !!}
            {!! $navItem('roles.index', 'Roles y Permisos', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>') !!}
        </nav>
        
        <div class="flex-none border-t border-slate-100 p-4 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                <div class="flex-1 min-w-0"><p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Usuario' }}</p><p class="text-xs text-slate-500 truncate">{{ Auth::user()->email ?? 'user@zahara.com' }}</p></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="Cerrar Sesión"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button></form>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="md:ml-72 flex-1 flex flex-col transition-all duration-300 min-h-screen">
        <header class="h-16 md:hidden flex-none flex items-center justify-between px-4 bg-white border-b border-slate-100 sticky top-0 z-30">
            <div class="flex items-center gap-3"><button @click="sidebarOpen = true" class="p-2 -ml-2 text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button><span class="font-bold text-lg text-slate-900">Zahara</span></div>
            <div class="flex items-center gap-3">
                <a href="{{ route('notificaciones.index') }}" class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    <span x-show="notificationCount > 0" x-text="notificationCount" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white" style="display: none;"></span>
                </a>
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
            </div>
        </header>

        <div class="flex-1 p-4 md:p-8 w-full max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div><div class="flex items-center gap-2 text-xs text-slate-500 mb-1">@yield('breadcrumb')</div><h2 class="text-2xl font-bold text-slate-900 tracking-tight">@yield('page_title')</h2>@hasSection('page_subtitle')<p class="text-sm text-slate-500 mt-1">@yield('page_subtitle')</p>@endif</div>
                <div class="flex items-center gap-3">@yield('page_actions')</div>
            </div>

            @if(session('ok'))<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm animate-enter"><svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-sm font-medium">{{ session('ok') }}</p></div>@endif
            @if($errors->any())<div class="mb-6 bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded-xl shadow-sm animate-enter"><div class="flex items-center gap-2 mb-2"><svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="font-bold text-sm">Errores:</p></div><ul class="list-disc list-inside text-sm space-y-1 ml-1 text-red-700/80">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            @yield('content')
        </div>
    </main>

    @include('components.modals-feedback')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalSystem', () => ({
                sidebarOpen: false,
                notificationCount: 0,
                toast: { show: false, message: '', type: 'success' },
                modal: { show: false, title: '', message: '', type: 'danger', action: null },
                initSystem() {
                    fetch('{{ route("api.notificaciones.conteo") }}')
                        .then(res => res.json())
                        .then(data => { this.notificationCount = data.count; });
                }
            }))
        })
    </script>
</body>
</html>
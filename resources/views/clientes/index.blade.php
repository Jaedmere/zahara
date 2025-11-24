@extends('layouts.app')

@section('title', 'Clientes - Zahara')
@section('page_title', 'Cartera de Clientes')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Clientes</span>
@endsection

@section('page_actions')
    <a href="{{ route('clientes.create') }}" class="btn-primary px-4 py-2 text-sm inline-flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Nuevo Cliente
    </a>
@endsection

@section('content')
<div x-data="searchHandler()" class="flex flex-col gap-6">

    <!-- TOOLBAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Search -->
        <div class="relative w-full max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted transition-colors group-focus-within:text-indigo-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input 
                type="text" 
                x-model="search"
                @input.debounce.300ms="performSearch"
                placeholder="Buscar por NIT, nombre o email..." 
                class="input-pill pl-11 pr-10 bg-white/80 backdrop-blur"
            >
            <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-4 flex items-center" style="display: none;">
                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex p-1 bg-slate-200/60 rounded-xl self-start md:self-auto">
            @php $status = request('status', 'activo'); @endphp
            
            <a href="{{ route('clientes.index', ['status' => 'activo']) }}" 
               class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $status === 'activo' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Activos
            </a>
            <a href="{{ route('clientes.index', ['status' => 'bloqueado']) }}" 
               class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $status === 'bloqueado' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Bloqueados
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white/60 backdrop-blur-xl rounded-2xl border border-soft shadow-sm overflow-hidden relative min-h-[300px]">
        <div x-show="isLoading" class="absolute inset-0 bg-white/40 z-10 backdrop-blur-[1px]" style="display: none;"></div>
        <div id="results-container">
            @include('clientes.partials.table', ['clientes' => $clientes])
        </div>
    </div>
</div>

<script>
    function searchHandler() {
        return {
            search: @js(request('search')),
            isLoading: false,
            controller: null,
            performSearch() {
                const self = this;
                if (this.controller) this.controller.abort();
                this.controller = new AbortController();
                this.isLoading = true;

                const params = new URLSearchParams(window.location.search);
                this.search ? params.set('search', this.search) : params.delete('search');
                params.delete('page');

                const url = `${window.location.pathname}?${params.toString()}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: this.controller.signal,
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('results-container').innerHTML = html;
                    window.history.pushState({}, '', url);
                })
                .finally(() => { self.isLoading = false; });
            }
        }
    }
</script>
@endsection
@extends('layouts.app')

@section('title', 'Cuentas - Zahara')
@section('page_title', 'Gestión de Cuentas')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Cuentas</span>
@endsection

@section('page_actions')
    <a href="{{ route('facturas.create') }}" class="btn-primary px-4 py-2.5 text-sm inline-flex items-center justify-center gap-2 shadow-sm w-full md:w-auto transition-transform active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16m8-8H4"/></svg>
        <span>Nueva Cuenta</span>
    </a>
@endsection

@section('content')
<div x-data="searchHandler({ showFilters: {{ request('eds_id') || request('fecha_desde') ? 'true' : 'false' }} })" 
     class="flex flex-col gap-6 w-full max-w-full">

    <!-- TOOLBAR STICKY -->
    <div class="sticky top-0 z-20 bg-[#F8FAFC]/95 backdrop-blur py-2 md:static md:bg-transparent md:py-0 transition-all w-full">
        <div class="flex flex-col gap-3">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Buscador -->
                <div class="relative w-full md:max-w-md group shadow-sm md:shadow-none rounded-xl flex-shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="search" 
                        @input.debounce.300ms="performSearch"
                        placeholder="N° Cuenta o Cliente..." 
                        class="input-pill !pl-12 pr-10 bg-white h-12 md:h-10 text-base md:text-sm shadow-sm md:shadow-none border-slate-200 focus:border-indigo-500 relative z-0 w-full"
                    >
                    
                    <!-- Botón Toggle Filtros -->
                    <button @click="toggleFilters" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center gap-1 text-slate-500 hover:text-indigo-600 transition-colors z-20"
                            :class="{'text-indigo-600': showFilters}"
                            title="Filtros Avanzados">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                </div>

                <!-- CONTENEDOR DERECHO -->
                <div class="flex items-center gap-2 overflow-x-auto max-w-full no-scrollbar pb-1 min-w-0">
                    
                    <!-- TABS de Estado -->
                    <div class="flex p-1 bg-slate-200/60 rounded-xl self-start flex-none">
                        @php $estado = request('estado', 'pendientes'); @endphp
                        @foreach(['pendientes' => 'Por Cobrar', 'pagadas' => 'Pagadas', 'anuladas' => 'Anuladas', 'todas' => 'Todas'] as $key => $label)
                            <a href="#" 
                               @click.prevent="setEstado('{{ $key }}')"
                               class="whitespace-nowrap px-4 py-2 md:py-1.5 rounded-lg text-xs font-semibold transition-all {{ $estado === $key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <!-- BOTÓN EXCEL -->
                    <a :href="'{{ route('facturas.export') }}?' + new URLSearchParams({
                            search: search || '', 
                            estado: estado || 'pendientes',
                            eds_id: filters.eds_id || '', 
                            fecha_desde: filters.fecha_desde || '', 
                            fecha_hasta: filters.fecha_hasta || ''
                       }).toString()" 
                       target="_blank"
                       class="btn-secondary h-full flex items-center justify-center px-3 py-2 rounded-xl text-emerald-600 border-emerald-200 hover:bg-emerald-50 hover:border-emerald-300 transition-colors shadow-sm flex-none"
                       title="Exportar a Excel">
                       <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- PANEL DE FILTROS AVANZADOS -->
            <div x-show="showFilters" x-transition.origin.top style="display: none;"
                 class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4 items-end animate-enter w-full">
                
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Estación (EDS)</label>
                    <select x-model="filters.eds_id" class="input-pill bg-slate-50 text-xs py-2">
                        <option value="">Todas las Estaciones</option>
                        @foreach(App\Models\EDS::where('activo', true)->orderBy('nombre')->get() as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Desde</label>
                    <input type="date" x-model="filters.fecha_desde" class="input-pill bg-slate-50 text-xs py-2">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Hasta</label>
                    <input type="date" x-model="filters.fecha_hasta" class="input-pill bg-slate-50 text-xs py-2">
                </div>

                <div class="flex gap-2">
                    <button @click="performSearch" class="btn-primary w-full py-2 text-xs">Aplicar</button>
                    <button @click="clearFilters" class="btn-secondary w-auto px-3 py-2 text-xs text-red-500 hover:text-red-700" title="Limpiar">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR RESULTADOS (AQUÍ ESTÁ LA CLAVE: w-full relative sin decoraciones) -->
    <div id="results-container" class="relative min-h-[200px] w-full">
        <div x-show="isLoading" class="absolute inset-0 bg-white/40 z-10 backdrop-blur-[1px] rounded-2xl transition-opacity" style="display: none;"></div>
        
        @include('facturas.partials.table', ['facturas' => $facturas])
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchHandler', (config) => ({
            search: @js(request('search')),
            estado: @js(request('estado', 'pendientes')),
            showFilters: config.showFilters,
            isLoading: false,
            controller: null,
            
            filters: {
                eds_id: @js(request('eds_id', '')),
                fecha_desde: @js(request('fecha_desde', '')),
                fecha_hasta: @js(request('fecha_hasta', ''))
            },

            toggleFilters() { this.showFilters = !this.showFilters; },
            setEstado(val) { this.estado = val; this.performSearch(); },
            clearFilters() {
                this.filters.eds_id = ''; this.filters.fecha_desde = ''; this.filters.fecha_hasta = '';
                this.performSearch();
            },

            performSearch() {
                const self = this;
                if (this.controller) this.controller.abort();
                this.controller = new AbortController();
                this.isLoading = true;
                
                const params = new URLSearchParams();
                if (this.search) params.set('search', this.search);
                if (this.estado) params.set('estado', this.estado);
                if (this.filters.eds_id) params.set('eds_id', this.filters.eds_id);
                if (this.filters.fecha_desde) params.set('fecha_desde', this.filters.fecha_desde);
                if (this.filters.fecha_hasta) params.set('fecha_hasta', this.filters.fecha_hasta);
                
                const url = `${window.location.pathname}?${params.toString()}&ajax=1`;
                window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
                
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, signal: this.controller.signal })
                .then(r => r.text())
                .then(html => { 
                    document.getElementById('results-container').innerHTML = html; 
                })
                .finally(() => { self.isLoading = false; });
            }
        }))
    })
</script>
@endsection
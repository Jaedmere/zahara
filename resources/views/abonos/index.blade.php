@extends('layouts.app')

@section('title', 'Abonos - Zahara')
@section('page_title', 'Recibos de Caja')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Abonos</span>
@endsection

@section('page_actions')
    <a href="{{ route('abonos.create') }}" class="btn-primary px-4 py-2.5 text-sm inline-flex items-center justify-center gap-2 shadow-sm w-full md:w-auto transition-transform active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16m8-8H4"/></svg>
        <span>Nuevo Abono</span>
    </a>
@endsection

@section('content')
{{-- Inicializamos showFilters según si hay filtros en la URL --}}
<div x-data="searchHandler({ showFilters: {{ request('eds_id') || request('fecha_desde') ? 'true' : 'false' }} })" class="flex flex-col gap-4 md:gap-6 pb-20 md:pb-0">

    <!-- TOOLBAR -->
    <div class="sticky top-0 z-20 bg-[#F8FAFC]/95 backdrop-blur py-2 md:static md:bg-transparent md:py-0 transition-all">
        <div class="flex flex-col gap-3">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Buscador -->
                <div class="relative w-full md:max-w-md group shadow-sm md:shadow-none rounded-xl">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 z-10">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="search"
                        @input.debounce.300ms="performSearch"
                        placeholder="Buscar por N° Recibo, Cuenta o Cliente..." 
                        class="input-pill !pl-12 pr-10 bg-white h-12 md:h-10 text-base md:text-sm shadow-sm md:shadow-none border-slate-200 focus:border-indigo-500 relative z-0"
                    >
                    
                    <!-- Botón Toggle Filtros -->
                    <button @click="toggleFilters" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center gap-1 text-slate-500 hover:text-indigo-600 transition-colors z-20"
                            :class="{'text-indigo-600': showFilters}"
                            title="Filtros Avanzados">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                </div>

                <!-- TABS de Estado -->
                <div class="flex p-1 bg-slate-200/60 rounded-xl self-start overflow-x-auto max-w-full no-scrollbar">
                    {{-- Eliminamos el php request hardcodeado aquí, dejamos que Alpine controle el estado visual --}}
                    
                    <a href="#" 
                       @click.prevent="setStatus('activos')"
                       class="whitespace-nowrap px-4 py-2 md:py-1.5 rounded-lg text-xs font-semibold transition-all"
                       :class="status === 'activos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        Vigentes
                    </a>
                    <a href="#" 
                       @click.prevent="setStatus('anulados')"
                       class="whitespace-nowrap px-4 py-2 md:py-1.5 rounded-lg text-xs font-semibold transition-all"
                       :class="status === 'anulados' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        Anulados
                    </a>
                </div>
            </div>

            <!-- PANEL DE FILTROS AVANZADOS -->
            <div x-show="showFilters" x-transition.origin.top style="display: none;"
                 class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4 items-end animate-enter">
                
                <!-- Filtro EDS -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Estación (EDS)</label>
                    <select x-model="filters.eds_id" class="input-pill bg-slate-50 text-xs py-2">
                        <option value="">Todas las Estaciones</option>
                        @foreach($eds_list as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Desde</label>
                    <input type="date" x-model="filters.fecha_desde" class="input-pill bg-slate-50 text-xs py-2">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Hasta</label>
                    <input type="date" x-model="filters.fecha_hasta" class="input-pill bg-slate-50 text-xs py-2">
                </div>

                <!-- Botones Acción -->
                <div class="flex gap-2">
                    <button @click="performSearch" class="btn-primary w-full py-2 text-xs">Aplicar Filtros</button>
                    <button @click="clearFilters" class="btn-secondary w-auto px-3 py-2 text-xs text-red-500 hover:text-red-700" title="Limpiar Filtros">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Resultados -->
    <div class="bg-white/60 backdrop-blur-xl rounded-2xl border border-soft shadow-sm overflow-hidden relative min-h-[300px]">
        <div x-show="isLoading" class="absolute inset-0 bg-white/40 z-10 backdrop-blur-[1px]" style="display: none;"></div>
        <div id="results-container">
            @include('abonos.partials.table', ['abonos' => $abonos])
        </div>
    </div>
</div>

<!-- MODAL DE DETALLE DE ABONO -->
<div x-data="{ open: false, abono: {}, details: [] }"
     @open-abono-details.window="open = true; abono = $event.detail.abono; details = $event.detail.details"
     class="relative z-[70]" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;" x-show="open">
    
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                
                <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-white font-bold text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Detalle del Recibo #<span x-text="abono.id"></span>
                    </h3>
                    <button @click="open = false" class="text-indigo-200 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-[10px] uppercase text-slate-400 font-bold">Fecha</span>
                            <p class="text-sm font-medium text-slate-700" x-text="abono.fecha"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase text-slate-400 font-bold">Total Pagado</span>
                            <p class="text-lg font-bold text-emerald-600 font-mono" x-text="'+$' + abono.total"></p>
                        </div>
                        <div class="col-span-2 border-t border-slate-200 pt-2 mt-1">
                            <span class="text-[10px] uppercase text-slate-400 font-bold">Cliente</span>
                            <p class="text-sm font-medium text-slate-700" x-text="abono.cliente"></p>
                        </div>
                    </div>

                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cuentas Afectadas</h4>
                    <div class="border rounded-xl overflow-hidden border-slate-200 max-h-60 overflow-y-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100 text-xs text-slate-500 uppercase sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">Cuenta</th>
                                    <th class="px-3 py-2">EDS</th>
                                    <th class="px-3 py-2 text-right">Abono</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="d in details" :key="d.id">
                                    <tr>
                                        <td class="px-3 py-2 font-mono font-bold text-indigo-600" x-text="'#' + d.factura"></td>
                                        <td class="px-3 py-2 text-xs text-slate-500" x-text="d.eds"></td>
                                        <td class="px-3 py-2 text-right font-medium text-slate-700" x-text="'$' + d.monto"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <button @click="open = false" class="btn-secondary w-full justify-center text-xs">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchHandler', (config) => ({
            search: @js(request('search')),
            status: @js(request('status', 'activos')), // Estado inicial desde PHP
            showFilters: config.showFilters,
            isLoading: false,
            controller: null,
            
            // Filtros Avanzados
            filters: {
                eds_id: @js(request('eds_id', '')),
                fecha_desde: @js(request('fecha_desde', '')),
                fecha_hasta: @js(request('fecha_hasta', ''))
            },

            toggleFilters() {
                this.showFilters = !this.showFilters;
            },

            setStatus(val) {
                this.status = val;
                this.performSearch(); // Disparar búsqueda al cambiar tab
            },

            clearFilters() {
                this.filters.eds_id = '';
                this.filters.fecha_desde = '';
                this.filters.fecha_hasta = '';
                this.performSearch();
            },

            performSearch() {
                const self = this;
                if (this.controller) this.controller.abort();
                this.controller = new AbortController();
                this.isLoading = true;
                
                // CONSTRUCCIÓN DE URL CORREGIDA
                // Ahora sí enviamos 'status' explícitamente
                const params = new URLSearchParams();
                
                if (this.search) params.set('search', this.search);
                
                // AQUÍ ESTABA EL ERROR: Debemos enviar el status actual
                params.set('status', this.status); 
                
                if (this.filters.eds_id) params.set('eds_id', this.filters.eds_id);
                if (this.filters.fecha_desde) params.set('fecha_desde', this.filters.fecha_desde);
                if (this.filters.fecha_hasta) params.set('fecha_hasta', this.filters.fecha_hasta);

                // Resetear a página 1 al filtrar
                // Nota: Laravel Paginate maneja esto, pero si cambiamos filtros es mejor reiniciar
                // params.delete('page'); 

                const url = `${window.location.pathname}?${params.toString()}`;
                
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, signal: this.controller.signal })
                .then(r => r.text())
                .then(html => { 
                    document.getElementById('results-container').innerHTML = html; 
                    window.history.pushState({}, '', url); 
                })
                .finally(() => { self.isLoading = false; });
            }
        }))
    })
</script>
@endsection
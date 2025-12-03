@extends('layouts.app')

@section('title', 'Consolidado por EDS - Zahara')
@section('page_title', 'Consolidado por EDS')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Consolidado EDS</span>
@endsection

@section('content')
<div class="flex flex-col gap-6 w-full max-w-full" 
     x-data="carteraEdsManager()" 
     @open-detail-eds.window="openDetail($event.detail.eds_id, $event.detail.cliente_id, $event.detail.eds_name, $event.detail.cliente_name)">

    <!-- TOOLBAR -->
    <div class="flex flex-col md:flex-row justify-between gap-4 w-full">
        <div class="relative w-full md:max-w-md group shadow-sm md:shadow-none rounded-xl flex-shrink-0">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-indigo-500 z-10">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <input 
                type="text" 
                x-model="search" 
                @input.debounce.300ms="performSearch"
                placeholder="Buscar Estación o Cliente..." 
                class="input-pill !pl-12 pr-10 bg-white h-12 md:h-10 text-base md:text-sm shadow-sm md:shadow-none border-slate-200 focus:border-indigo-500 w-full relative z-0"
            >
            
            <div x-show="isSearching" class="absolute inset-y-0 right-0 pr-4 flex items-center z-20" style="display: none;">
                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        
        <div class="flex items-center gap-2 self-start md:self-auto min-w-0">
            <a :href="'{{ route('cartera_eds.export') }}?search=' + (search || '')" 
               target="_blank"
               class="bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-50 px-4 py-2 rounded-xl shadow-sm flex items-center gap-2 h-10 transition-colors font-medium text-sm flex-none"
               title="Descargar Reporte General">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="hidden md:inline">Excel</span>
            </a>
        </div>
    </div>

    <!-- CONTENEDOR AJAX -->
    <div id="results-container" class="relative min-h-[200px] w-full">
        <div x-show="isSearching" class="absolute inset-0 bg-white/50 z-10 backdrop-blur-[1px] rounded-2xl transition-all" style="display: none;"></div>
        @include('cartera_eds.partials.table', ['items' => $items, 'grand_total' => $grand_total])
    </div>

    <!-- PANEL LATERAL (DRAWER) AJUSTADO VISUALMENTE -->
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="isOpen" style="display: none;">
        <!-- Overlay -->
        <div x-show="isOpen" 
             x-transition.opacity 
             class="fixed inset-0 bg-slate-900/75 transition-opacity backdrop-blur-sm" 
             @click="isOpen = false">
        </div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-0 md:pl-10">
                    <div x-show="isOpen" 
                         x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-6xl">

                        <form action="{{ route('abonos.store') }}" 
                              method="POST" 
                              class="flex h-full flex-col bg-white shadow-2xl" 
                              @submit.prevent="submitPayment">
                            @csrf
                            <input type="hidden" name="cliente_id" x-model="clienteId">

                            <!-- HEADER (MISMO ESTILO QUE LA MODAL 1) -->
                            <div class="px-4 sm:px-6 py-6 bg-indigo-600 text-white shadow-md relative z-10 flex-none">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 overflow-hidden">
                                        <h2 class="text-lg font-bold leading-6" id="slide-over-title">
                                            <span class="block opacity-75 text-xs font-normal uppercase tracking-wide">
                                                Detalle de Cartera por EDS
                                            </span>
                                            <span class="block truncate" x-text="edsNombre"></span>
                                            <span class="block text-xs text-indigo-200 truncate" x-text="clienteNombre"></span>
                                        </h2>
                                        
                                        <div class="flex items-center gap-3 mt-3 flex-wrap">
                                            <p class="text-indigo-200 text-xs hidden sm:block">
                                                Cartera pendiente por estación y cliente.
                                            </p>

                                            <a :href="exportUrl" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1 text-[10px] bg-white/10 hover:bg-white/20 border border-white/20 text-white px-3 py-1.5 rounded-lg transition-colors font-bold shadow-sm whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Exportar Detalle
                                            </a>
                                        </div>
                                    </div>

                                    <button type="button" 
                                            @click="isOpen = false" 
                                            class="rounded-md text-indigo-200 hover:text-white focus:outline-none ml-3 p-2">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- BLOQUE TOTALIZADOR (CLAVADO AL DE LA MODAL 1) -->
                                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between bg-indigo-700/50 p-4 rounded-xl border border-indigo-500/30 gap-4">
                                    <!-- Total a Abonar -->
                                    <div class="w-full sm:w-auto">
                                        <span class="text-[10px] uppercase font-bold text-indigo-200 tracking-wider block">
                                            Total a Abonar
                                        </span>
                                        <div class="text-3xl font-mono font-bold" x-text="formatMoney(totalAbono)">
                                            $0.00
                                        </div>
                                        <p class="text-[10px] text-indigo-200/80 mt-1">
                                            Selecciona las cuentas y define el monto por cada una.
                                        </p>
                                    </div>

                                    <!-- Fecha / Método -->
                                    <div class="flex gap-2 w-full sm:w-auto">
                                        <input type="date" 
                                               name="fecha" 
                                               value="{{ date('Y-m-d') }}" 
                                               class="input-pill text-xs py-2 h-9 bg-indigo-800/50 border-indigo-500 text-white placeholder-indigo-300 focus:ring-white/20 flex-1"
                                               required>
                                        
                                        <select name="metodo_pago" 
                                                class="input-pill text-xs py-2 h-9 bg-indigo-800/50 border-indigo-500 text-white focus:ring-white/20 appearance-none flex-1 min-w-[130px]"
                                                required>
                                            <option value="Transferencia Bancaria" class="text-slate-800">Transferencia</option>
                                            <option value="Efectivo" class="text-slate-800">Efectivo</option>
                                            <option value="Cheque" class="text-slate-800">Cheque</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Referencia / Notas (MISMO ESTILO) -->
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <input type="text" 
                                           name="referencia" 
                                           class="input-pill text-xs py-1.5 h-8 bg-indigo-800/30 border-transparent text-white placeholder-indigo-300 focus:ring-white/10" 
                                           placeholder="Referencia / Recibo *" 
                                           required>
                                    
                                    <input type="text" 
                                           name="notas" 
                                           class="input-pill text-xs py-1.5 h-8 bg-indigo-800/30 border-transparent text-white placeholder-indigo-300 focus:ring-white/10" 
                                           placeholder="Notas...">
                                </div>
                            </div>

                            <!-- BODY (Misma estructura general, respetando tu tabla y filtros) -->
                            <div class="relative flex-1 bg-slate-50 flex flex-col min-h-0 overflow-y-auto">
                                
                                <!-- Filtros Internos Sticky -->
                                <div class="sticky top-0 z-20 bg-white border-b border-slate-200 px-4 py-3 shadow-sm flex-none">
                                    <div class="flex justify-between items-center">
                                        <button type="button" 
                                                @click="toggleSelectAll" 
                                                class="text-xs text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                            <span x-text="allSelected ? 'Desmarcar Todo' : 'Seleccionar Todo Visible'"></span>
                                        </button>

                                        <button type="button" 
                                                @click="showFilters = !showFilters" 
                                                class="text-xs flex items-center gap-1 text-slate-500 hover:text-indigo-600 bg-slate-100 px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                            </svg>
                                            Filtros
                                        </button>
                                    </div>

                                    <div x-show="showFilters" 
                                         x-transition 
                                         class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-100">
                                        <div class="col-span-1">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase">
                                                Buscar (Factura)
                                            </label>
                                            <input type="text" 
                                                   x-model="filters.q_factura" 
                                                   @input.debounce.500ms="reloadCartera()" 
                                                   class="input-pill text-xs py-1.5 mt-1" 
                                                   placeholder="# Factura">
                                        </div>
                                        <div class="col-span-1">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase">Desde</label>
                                            <input type="date" 
                                                   x-model="filters.corte_desde" 
                                                   @change="reloadCartera()" 
                                                   class="input-pill text-xs py-1.5 mt-1">
                                        </div>
                                        <div class="col-span-1">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase">Hasta</label>
                                            <input type="date" 
                                                   x-model="filters.corte_hasta" 
                                                   @change="reloadCartera()" 
                                                   class="input-pill text-xs py-1.5 mt-1">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla -->
                                <div class="p-4 pb-20">
                                    <div x-show="isLoading" class="flex justify-center py-10">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                    </div>
                                    
                                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                                        <div class="overflow-x-auto w-full">
                                            <table class="w-full min-w-[800px] text-left text-xs">
                                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500 uppercase">
                                                    <tr>
                                                        <th class="px-4 py-3 w-8 text-center">
                                                            <input type="checkbox" 
                                                                   @change="toggleSelectAll" 
                                                                   :checked="allSelected" 
                                                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                        </th>
                                                        <th class="px-4 py-3">Cuenta</th>
                                                        <th class="px-4 py-3 text-center">Corte</th>
                                                        <th class="px-4 py-3 text-center" title="Días de Mora">Días</th>
                                                        <th class="px-4 py-3 text-right">Total</th>
                                                        <th class="px-4 py-3 text-right text-red-500">Desc.</th>
                                                        <th class="px-4 py-3 text-right text-emerald-600">Abonos</th>
                                                        <th class="px-4 py-3 text-right font-black text-slate-700 bg-slate-100/50">
                                                            Saldo
                                                        </th>
                                                        <th class="px-4 py-3 w-32 text-right bg-indigo-50/30">
                                                            Abonar ($)
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-xs">
                                                    <template x-for="(item, index) in cartera" :key="item.id">
                                                        <tr class="hover:bg-indigo-50/10 transition-colors group cursor-pointer" 
                                                            @click="toggleSelection(item)" 
                                                            :class="{'bg-indigo-50': isSelected(item.id)}">
                                                            
                                                            <td class="px-4 py-3 text-center" @click.stop>
                                                                <input type="checkbox" 
                                                                       :checked="isSelected(item.id)" 
                                                                       @change="toggleSelection(item)" 
                                                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                                            </td>
                                                            
                                                            <td class="px-4 py-3">
                                                                <div class="font-bold font-mono text-indigo-900 text-sm" 
                                                                     x-text="'#' + item.consecutivo">
                                                                </div>
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                                <div class="text-[10px] font-mono text-slate-600" 
                                                                     x-text="item.corte_desde + ' - ' + item.corte_hasta">
                                                                </div>
                                                                <div class="text-[9px] text-red-400 font-medium" 
                                                                     x-text="'Vence: ' + item.fecha_vencimiento">
                                                                </div>
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-center font-bold" 
                                                                :class="item.dias_vencidos > 0 ? 'text-red-600' : 'text-emerald-600'" 
                                                                x-text="item.dias_vencidos > 0 ? item.dias_vencidos : 'OK'">
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-right text-slate-500" 
                                                                x-text="formatMoney(item.valor_total)">
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-right text-red-500 font-medium" 
                                                                x-text="item.descuento > 0 ? '-' + formatMoney(item.descuento) : '--'">
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-right text-emerald-600 font-medium" 
                                                                x-text="item.abonos_previos > 0 ? formatMoney(item.abonos_previos) : '--'">
                                                            </td>
                                                            
                                                            <td class="px-4 py-3 text-right font-black text-slate-8 00 bg-slate-50/50 border-l border-slate-100"
                                                                x-text="formatMoney(item.saldo_pendiente)">
                                                            </td>
                                                            
                                                            <td class="px-4 py-2 text-right bg-indigo-50/30 border-l border-indigo-100" @click.stop>
                                                                <input type="number" 
                                                                       step="0.01" 
                                                                       x-model.number="selectedMap[item.id]" 
                                                                       :disabled="!isSelected(item.id)"
                                                                       class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-right font-mono font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-xs disabled:bg-transparent disabled:border-transparent disabled:text-slate-300"
                                                                       :max="item.saldo_pendiente">
                                                                
                                                                <template x-if="isSelected(item.id)">
                                                                    <div>
                                                                        <input type="hidden" :name="`detalles[${index}][factura_id]`" :value="item.id">
                                                                        <input type="hidden" :name="`detalles[${index}][abono]`" :value="selectedMap[item.id]">
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-center" x-show="meta.current_page < meta.last_page">
                                        <button @click="loadMore()" class="btn-secondary text-xs w-full py-3 border-dashed">
                                            Cargar más...
                                        </button>
                                    </div>

                                    <div x-show="cartera.length === 0 && !isLoading" 
                                         class="text-center py-10 text-slate-400 text-sm">
                                        No hay cuentas pendientes.
                                    </div>
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="flex-shrink-0 border-t border-slate-200 px-4 py-5 sm:px-6 bg-white flex justify-end gap-3 sticky bottom-0 z-20">
                                <button type="button" @click="isOpen = false" class="btn-secondary">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn-primary" :disabled="totalAbono <= 0">
                                    <span class="mr-1">Registrar Abono de</span>
                                    <span x-text="formatMoney(totalAbono)"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('carteraEdsManager', () => ({
            search: @js(request('search')),
            isSearching: false,
            isOpen: false,
            isLoading: false,
            showFilters: false,
            edsId: null,
            edsNombre: '',
            clienteId: null,
            clienteNombre: '',
            cartera: [],
            selectedMap: {}, 
            allSelected: false,
            meta: { current_page: 1, last_page: 1, total: 0, total_deuda: 0 },
            filters: { q_factura: '', corte_desde: '', corte_hasta: '' },

            get totalDeuda() { return this.meta.total_deuda || 0; },
            get totalAbono() {
                return Object.values(this.selectedMap)
                    .reduce((sum, val) => sum + (parseFloat(val) || 0), 0);
            },
            get exportUrl() { 
                let url = "{{ route('cartera_eds.exportar_par', [':eds', ':cliente']) }}"
                    .replace(':eds', this.edsId || 0)
                    .replace(':cliente', this.clienteId || 0);

                const params = new URLSearchParams({
                    q_factura: this.filters.q_factura,
                    corte_desde: this.filters.corte_desde,
                    corte_hasta: this.filters.corte_hasta
                });

                return `${url}?${params.toString()}`;
            },

            performSearch() {
                const params = new URLSearchParams({ search: this.search, ajax: '1' });
                window.history.replaceState({}, '', `${window.location.pathname}?search=${this.search}`);

                fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('results-container').innerHTML = html;
                });
            },

            openDetail(eds_id, cliente_id, eds_name, cliente_name) {
                this.edsId = eds_id;
                this.clienteId = cliente_id;
                this.edsNombre = eds_name;
                this.clienteNombre = cliente_name;
                this.selectedMap = {}; 
                this.allSelected = false;
                this.cartera = [];
                this.filters = { q_factura: '', corte_desde: '', corte_hasta: '' };
                this.isOpen = true;
                this.loadCartera(1);
            },

            reloadCartera() {
                this.cartera = [];
                this.loadCartera(1);
            },

            loadMore() {
                this.loadCartera(this.meta.current_page + 1);
            },

            loadCartera(page) {
                this.isLoading = true;

                let url = "{{ route('api.cartera_eds.detalle', [':eds', ':cliente']) }}"
                    .replace(':eds', this.edsId)
                    .replace(':cliente', this.clienteId);

                const params = new URLSearchParams({
                    page: page,
                    per_page: 50,
                    q_factura: this.filters.q_factura,
                    corte_desde: this.filters.corte_desde,
                    corte_hasta: this.filters.corte_hasta
                });

                fetch(`${url}?${params.toString()}`)
                    .then(r => r.json())
                    .then(response => {
                        if (page === 1) {
                            this.cartera = response.data;
                        } else {
                            this.cartera = [...this.cartera, ...response.data];
                        }
                        this.meta = response.meta;
                    })
                    .finally(() => this.isLoading = false);
            },

            isSelected(id) {
                return this.selectedMap.hasOwnProperty(id);
            },

            toggleSelection(item) {
                if (this.isSelected(item.id)) {
                    delete this.selectedMap[item.id];
                } else {
                    this.selectedMap[item.id] = parseFloat(item.saldo_pendiente);
                }
                this.selectedMap = { ...this.selectedMap };
            },

            toggleSelectAll() {
                this.allSelected = !this.allSelected;

                if (this.allSelected) {
                    this.cartera.forEach(item => {
                        this.selectedMap[item.id] = parseFloat(item.saldo_pendiente);
                    });
                } else {
                    this.selectedMap = {};
                }

                this.selectedMap = { ...this.selectedMap };
            },

            updateAmount(id, value) {
                if (this.isSelected(id)) {
                    this.selectedMap[id] = parseFloat(value);
                    this.selectedMap = { ...this.selectedMap };
                }
            },

            formatMoney(v) {
                return '$' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
            },

            submitPayment(e) {
                if (this.totalAbono <= 0) return;
                e.target.submit();
            }
        }))
    })
</script>
@endsection

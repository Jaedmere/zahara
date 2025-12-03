@extends('layouts.app')

@section('title', 'Consolidado por Cuenta - Zahara')
@section('page_title', 'Consolidado por Cuenta')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Consolidado Cuentas</span>
@endsection

@section('content')
<div class="flex flex-col gap-6" 
     x-data="carteraCuentasManager()" 
     @open-detail-cuenta.window="openDetail($event.detail.id, $event.detail.consecutivo)">

    <!-- TOOLBAR -->
    <div class="flex flex-col md:flex-row justify-between gap-4">
        <div class="relative w-full md:max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 transition-colors group-focus-within:text-indigo-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <input 
                type="text" 
                x-model="search" 
                @input.debounce.300ms="performSearch"
                placeholder="Buscar Cuenta, Cliente o EDS..." 
                class="input-pill !pl-12 pr-10 bg-white h-12 md:h-10 text-base md:text-sm shadow-sm border-slate-200 focus:border-indigo-500"
            >
            
            <div x-show="isSearching" class="absolute inset-y-0 right-0 pr-4 flex items-center" style="display: none;">
                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        
        <div class="flex items-center gap-2 self-start md:self-auto">
            {{-- FILTRO EDS --}}
            <div class="relative">
                <select x-model="filters.eds_id" 
                        @change="performSearch()" 
                        class="input-pill text-xs h-10 pl-3 pr-8 appearance-none cursor-pointer shadow-sm hover:border-indigo-300 transition-colors w-full md:w-auto bg-white">
                    <option value="">Todas las EDS</option>
                    @foreach($eds_list as $e) 
                        <option value="{{ $e->id }}">{{ $e->nombre }}</option> 
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <a :href="'{{ route('cartera_cuentas.export') }}?search=' + (search || '') + '&eds_id=' + (filters.eds_id || '')" 
               target="_blank"
               class="bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-50 px-4 py-2 rounded-xl shadow-sm flex items-center gap-2 h-10 transition-colors font-medium text-sm"
               title="Descargar Reporte">
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
        @include('cartera_cuentas.partials.table', ['items' => $items, 'grand_total' => $grand_total])
    </div>

    <!-- PANEL LATERAL -->
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="isOpen" style="display: none;">
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/75 transition-opacity backdrop-blur-sm" @click="isOpen = false"></div>
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
                         class="pointer-events-auto w-screen max-w-2xl">
                        
                        <form action="{{ route('abonos.store') }}" method="POST" class="flex h-full flex-col bg-white shadow-2xl" @submit.prevent="submitPayment">
                            @csrf
                            <input type="hidden" name="cliente_id" x-model="clienteId">

                            <!-- Header -->
                            <div class="px-4 sm:px-6 py-6 bg-indigo-600 text-white shadow-md relative z-10 flex-none">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h2 class="text-lg font-bold leading-6" id="slide-over-title">
                                            <span class="block opacity-75 text-xs font-normal uppercase tracking-wide">Detalle de Cuenta</span>
                                            <span x-text="'#' + cuentaConsecutivo"></span>
                                        </h2>
                                        <div class="flex items-center gap-4 mt-2">
                                            <a :href="exportUrl" target="_blank" class="inline-flex items-center gap-1 text-[10px] bg-white/10 hover:bg-white/20 border border-white/20 text-white px-3 py-1.5 rounded-lg transition-colors font-bold shadow-sm">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                                </svg>
                                                Exportar
                                            </a>
                                        </div>
                                    </div>
                                    <button type="button" @click="isOpen = false" class="rounded-md text-indigo-200 hover:text-white focus:outline-none ml-3 p-2">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between bg-indigo-700/50 p-4 rounded-xl border border-indigo-500/30 gap-4">
                                    <div class="w-full sm:w-auto">
                                        <span class="text-[10px] uppercase font-bold text-indigo-200 tracking-wider block">Total a Abonar</span>
                                        <div class="text-3xl font-mono font-bold" x-text="formatMoney(totalAbono)">$0.00</div>
                                    </div>
                                    <div class="flex gap-2 w-full sm:w-auto">
                                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="input-pill text-xs py-2 h-9 bg-indigo-800/50 border-indigo-500 text-white placeholder-indigo-300 focus:ring-white/20" required>
                                        <select name="metodo_pago" class="input-pill text-xs py-2 h-9 bg-indigo-800/50 border-indigo-500 text-white focus:ring-white/20 appearance-none" required>
                                            <option value="Transferencia Bancaria" class="text-slate-800">Transferencia</option>
                                            <option value="Efectivo" class="text-slate-800">Efectivo</option>
                                            <option value="Cheque" class="text-slate-800">Cheque</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <input type="text" name="referencia" class="input-pill text-xs py-1.5 h-8 bg-indigo-800/30 border-transparent text-white placeholder-indigo-300 focus:ring-white/10" placeholder="Referencia / Recibo *" required>
                                    <input type="text" name="notas" class="input-pill text-xs py-1.5 h-8 bg-indigo-800/30 border-transparent text-white placeholder-indigo-300 focus:ring-white/10" placeholder="Notas...">
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="relative flex-1 bg-slate-50 flex flex-col min-h-0 p-6 overflow-y-auto">
                                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-4">
                                    <template x-for="(item, index) in cartera" :key="item.id">
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-slate-500">Cliente</span>
                                                <span class="font-bold text-slate-800 text-right" x-text="item.cliente_nombre"></span>
                                            </div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-slate-500">Estación EDS</span>
                                                <span class="font-medium text-slate-700 text-right" x-text="item.eds_nombre"></span>
                                            </div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-slate-500">Vencimiento</span>
                                                <span class="font-mono text-red-500 text-right" x-text="item.fecha_vencimiento"></span>
                                            </div>
                                            <div class="flex justify-between text-sm mb-3 pb-3 border-b border-slate-100">
                                                <span class="text-slate-500">Días Mora</span>
                                                <span class="font-bold text-right" 
                                                      :class="item.dias_vencidos > 0 ? 'text-red-600' : 'text-emerald-600'"
                                                      x-text="item.dias_vencidos > 0 ? item.dias_vencidos + ' días' : 'Al día'">
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                                <span>Valor Original</span>
                                                <span x-text="formatMoney(item.valor_total)"></span>
                                            </div>
                                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                                <span>Abonos Previos</span>
                                                <span class="text-emerald-600" x-text="formatMoney(item.abonos_previos)"></span>
                                            </div>
                                            <div class="mt-4 pt-4 border-t border-slate-100">
                                                <label class="block text-xs font-bold text-indigo-600 uppercase mb-1">Monto a Abonar</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2 text-slate-400 font-bold">$</span>
                                                    <input type="number" step="0.01" 
                                                           x-model.number="selectedMap[item.id]" 
                                                           class="w-full bg-indigo-50 border border-indigo-200 rounded-xl pl-7 pr-3 py-2 font-mono font-bold text-indigo-9

00 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-lg" 
                                                           :max="item.saldo_pendiente">
                                                </div>
                                                <p class="text-[10px] text-slate-400 mt-1 text-right">
                                                    Máximo: <span x-text="formatMoney(item.saldo_pendiente)"></span>
                                                </p>
                                                
                                                <input type="hidden" :name="`detalles[0][factura_id]`" :value="item.id">
                                                <input type="hidden" :name="`detalles[0][abono]`" :value="selectedMap[item.id]">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex-shrink-0 border-t border-slate-200 px-4 py-5 sm:px-6 bg-white flex justify-end gap-3 sticky bottom-0 z-20">
                                <button type="button" @click="isOpen = false" class="btn-secondary">Cancelar</button>
                                <button type="submit" class="btn-primary" :disabled="totalAbono <= 0">Confirmar Abono</button>
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
        Alpine.data('carteraCuentasManager', () => ({
            search: @js(request('search')),
            isSearching: false,
            isOpen: false,
            isLoading: false,
            cuentaId: null,
            cuentaConsecutivo: '',
            clienteId: null, 
            cartera: [],
            selectedMap: {},
            allSelected: false,
            filters: { eds_id: @js(request('eds_id', '')) },

            get totalAbono() { 
                return Object.values(this.selectedMap).reduce((sum, val) => sum + (parseFloat(val) || 0), 0); 
            },
            
            get exportUrl() { 
                let url = "{{ route('cartera_cuentas.exportar_individual', ':id') }}".replace(':id', this.cuentaId || 0);
                return url;
            },

            // AJUSTADO: sin "search=null" y manejando loader
            performSearch() {
                this.isSearching = true;

                const params = new URLSearchParams();
                params.set('ajax', '1');

                if (this.search && this.search.trim() !== '') {
                    params.set('search', this.search.trim());
                }

                if (this.filters.eds_id) {
                    params.set('eds_id', this.filters.eds_id);
                }

                const url = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', url);

                fetch(url, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'Accept': 'text/html' 
                    } 
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('results-container').innerHTML = html;
                })
                .finally(() => {
                    this.isSearching = false;
                });
            },

            openDetail(id, consecutivo) {
                this.cuentaId = id;
                this.cuentaConsecutivo = consecutivo;
                this.selectedMap = {}; 
                this.cartera = [];
                this.isOpen = true;
                this.loadCartera();
            },

            loadCartera() {
                this.isLoading = true;
                let url = "{{ route('api.cartera_cuentas.detalle', ':id') }}".replace(':id', this.cuentaId);
                fetch(url)
                    .then(r => r.json())
                    .then(res => {
                        this.cartera = res.data;
                        this.clienteId = res.meta.cliente_id;
                        if (this.cartera.length > 0) {
                            this.selectedMap[this.cartera[0].id] = parseFloat(this.cartera[0].saldo_pendiente);
                        }
                    })
                    .finally(() => this.isLoading = false);
            },

            isSelected(id) { 
                return this.selectedMap.hasOwnProperty(id); 
            },

            toggleSelection(item) {
                if (this.isSelected(item.id)) { 
                    delete this.selectedMap[item.id]; 
                    this.allSelected = false; 
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

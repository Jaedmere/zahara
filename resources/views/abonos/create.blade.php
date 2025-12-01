@extends('layouts.app')

@section('title', 'Nuevo Recibo - Zahara')
@section('page_title', 'Registrar Recibo de Caja')

@section('breadcrumb')
    <a href="{{ route('abonos.index') }}" class="hover:text-indigo-600">Abonos</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Registrar Pago</span>
@endsection

@section('content')
<div class="max-w-6xl animate-enter" x-data="erpAbono()">
    <form action="{{ route('abonos.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="cliente_id" x-model="cliente.id">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- COLUMNA IZQUIERDA --}}
            <div class="lg:col-span-1 space-y-6">
                <!-- BUSCADOR CLIENTE -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        1. Cliente
                    </h3>

                    <div class="relative">
                        <input type="text" 
                               x-model="search" 
                               @input.debounce.300ms="buscarCliente"
                               @click="showResults = true"
                               @click.outside="showResults = false"
                               class="input-pill font-bold" 
                               :class="cliente.id ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : ''"
                               placeholder="Buscar Cliente o NIT...">

                        <button type="button" x-show="cliente.id" @click="resetCliente" class="absolute inset-y-0 right-0 px-3 text-indigo-400 hover:text-red-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <!-- Resultados Dropdown -->
                        <div x-show="showResults && results.length > 0" 
                             class="absolute w-full bg-white mt-1 border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto z-50">
                            <template x-for="c in results" :key="c.id">
                                <div @click="selectCliente(c)" class="p-3 hover:bg-indigo-50 cursor-pointer border-b border-slate-50 transition-colors">
                                    <div class="font-bold text-slate-800 text-sm" x-text="c.razon_social"></div>
                                    <div class="text-xs text-slate-500" x-text="'NIT: ' + c.documento"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Resumen Deuda Global -->
                    <div x-show="cliente.id" x-transition class="mt-4 pt-4 border-t border-slate-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-slate-500">Total Facturas Pendientes:</span>
                            <span class="font-bold text-slate-800" x-text="meta.total"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500">Deuda Total Global:</span>
                            <span class="font-bold text-red-500" x-text="formatMoney(meta.total_deuda)"></span>
                        </div>
                    </div>
                    @error('cliente_id') <p class="mt-2 text-xs text-red-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <!-- DATOS PAGO -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" :class="{'opacity-50 pointer-events-none': !cliente.id}">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        2. Pago
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Fecha</label>
                            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="input-pill" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Método</label>
                            <select name="metodo_pago" class="input-pill appearance-none" required>
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Consignación">Consignación</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Referencia</label>
                            <input type="text" name="referencia" class="input-pill" placeholder="Referencia / Recibo">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notas</label>
                            <textarea name="notas" rows="2" class="input-pill resize-none" placeholder="Notas..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- TOTAL PAGAR -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-lg sticky top-4">
                    <div class="flex justify-between items-end mb-2">
                        <span class="block text-xs font-bold text-slate-400 uppercase">Total a Pagar</span>
                        <span class="text-xs bg-slate-800 px-2 py-1 rounded-lg text-slate-300" x-text="countSelected + ' facturas'"></span>
                    </div>
                    <div class="text-4xl font-bold font-mono tracking-tight" x-text="formatMoney(totalAbono)"></div>
                    
                    {{-- Inputs ocultos dinámicos --}}
                    <template x-for="(amount, id) in selectedMap" :key="id">
                        <div x-if="amount > 0">
                            <input type="hidden" :name="'detalles[' + id + '][factura_id]'" :value="id">
                            <input type="hidden" :name="'detalles[' + id + '][abono]'" :value="amount">
                        </div>
                    </template>

                    <button type="submit" 
                            class="mt-4 w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="totalAbono <= 0">
                        Confirmar Recibo
                    </button>
                    
                    @if($errors->any())
                         <div class="mt-2 text-xs text-red-400 font-bold text-center">
                            Verifica los campos obligatorios.
                         </div>
                    @endif
                </div>
            </div>

            {{-- COLUMNA DERECHA: TABLA DE CARTERA --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full min-h-[600px]">
                    
                    {{-- HEADER CON FILTROS RESTAURADOS --}}
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-3">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                3. Seleccionar Cuentas
                            </h3>
                            
                            {{-- Botón Toggle Filtros --}}
                            <button type="button" @click="showFilters = !showFilters" class="text-xs flex items-center gap-1 text-slate-500 hover:text-indigo-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                Filtros
                            </button>
                        </div>

                        {{-- Panel Filtros Desplegable --}}
                        <div x-show="showFilters" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 pb-2 border-t border-slate-200 mt-2">
                            
                            <!-- Filtro EDS -->
                            <select x-model="filters.eds" @change="cargarCartera(1)" class="input-pill text-xs py-2 bg-white">
                                <option value="">Todas las EDS</option>
                                @foreach($eds as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                            
                            <!-- Filtro Búsqueda Factura -->
                            <input type="text" x-model="filters.q_factura" @input.debounce.500ms="cargarCartera(1)" 
                                   class="input-pill text-xs py-2" placeholder="Buscar N° Cuenta...">
                        </div>
                    </div>

                    <div class="overflow-y-auto flex-1 p-0 relative">
                        <div x-show="isLoading" class="absolute inset-0 bg-white/80 z-20 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <div x-show="!cliente.id" class="h-full flex flex-col items-center justify-center text-slate-400 p-10">
                            <p class="text-sm">Selecciona un cliente para ver su cartera.</p>
                        </div>

                        <div x-show="cliente.id && cartera.length === 0 && !isLoading" class="h-full flex flex-col items-center justify-center text-slate-400 p-10">
                            <p class="text-sm font-medium text-emerald-600">¡Al día!</p>
                            <p class="text-xs">No hay cuentas pendientes.</p>
                        </div>

                        <table x-show="cartera.length > 0" class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                <tr class="text-[10px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                                    <th class="px-4 py-3 w-10 text-center">#</th>
                                    <th class="px-4 py-3">Cuenta / EDS</th>
                                    <th class="px-4 py-3">Vencimiento</th>
                                    <th class="px-4 py-3 text-right">Saldo</th>
                                    <th class="px-4 py-3 w-40 text-right">Abonar ($)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <template x-for="item in cartera" :key="item.id">
                                    <tr class="hover:bg-slate-50 transition-colors group" 
                                        :class="{'bg-indigo-50/50': isSelected(item.id)}">
                                        
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" 
                                                   @change="toggleSelection(item)"
                                                   :checked="isSelected(item.id)"
                                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                        </td>
                                        
                                        <td class="px-4 py-3">
                                            <div class="font-bold font-mono text-indigo-900" x-text="'#' + item.consecutivo"></div>
                                            <div class="text-[10px] text-slate-400 uppercase" x-text="item.eds.nombre"></div>
                                            {{-- CORTE VISIBLE --}}
                                            <div class="text-[9px] text-slate-500 mt-0.5 bg-slate-50 px-1 rounded inline-block">
                                                <span class="font-bold">Corte:</span> <span x-text="formatDate(item.corte_desde) + ' a ' + formatDate(item.corte_hasta)"></span>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-3 text-slate-500" x-text="formatDate(item.fecha_vencimiento)"></td>
                                        
                                        <td class="px-4 py-3 text-right font-medium text-slate-700" x-text="formatMoney(item.saldo_pendiente)"></td>
                                        
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" 
                                                   step="0.01" 
                                                   @input="updateAmount(item.id, $event.target.value)"
                                                   :value="selectedMap[item.id] || ''"
                                                   :disabled="!isSelected(item.id)"
                                                   class="input-pill py-1 px-2 text-right font-bold font-mono text-sm"
                                                   :class="isSelected(item.id) ? 'bg-white border-indigo-300 text-emerald-600' : 'bg-transparent border-transparent text-slate-300'"
                                                   :max="item.saldo_pendiente">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- PAGINACIÓN -->
                    <div x-show="meta.last_page > 1" class="p-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                        <button type="button" @click="changePage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="btn-secondary px-3 py-1 text-xs disabled:opacity-50">Anterior</button>
                        <span class="text-xs text-slate-500">Página <strong x-text="meta.current_page"></strong> de <strong x-text="meta.last_page"></strong></span>
                        <button type="button" @click="changePage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="btn-secondary px-3 py-1 text-xs disabled:opacity-50">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('erpAbono', () => ({
            search: '',
            showResults: false,
            showFilters: true, // Filtros visibles por defecto para que sepan que existen
            results: [],
            cliente: { id: null, razon_social: '' },
            
            // FILTROS RESTAURADOS
            filters: { eds: '', q_factura: '' },

            cartera: [],
            meta: { current_page: 1, last_page: 1, total: 0, total_deuda: 0 },
            isLoading: false,
            selectedMap: {}, 

            get countSelected() { return Object.keys(this.selectedMap).length; },
            get totalAbono() { return Object.values(this.selectedMap).reduce((sum, val) => sum + (parseFloat(val) || 0), 0); },

            buscarCliente() {
                if (this.search.length < 2) return;
                fetch(`{{ route('api.clientes.buscar') }}?q=${this.search}`)
                    .then(res => res.json())
                    .then(data => { this.results = data; this.showResults = true; });
            },

            selectCliente(c) {
                this.cliente = c;
                this.search = c.razon_social;
                this.showResults = false;
                this.selectedMap = {};
                this.cargarCartera(1);
            },

            resetCliente() {
                this.cliente = { id: null, razon_social: '' };
                this.search = '';
                this.cartera = [];
                this.selectedMap = {};
                this.meta = { current_page: 1, last_page: 1, total: 0, total_deuda: 0 };
                this.filters = { eds: '', q_factura: '' };
            },

            cargarCartera(page) {
                if (!this.cliente.id) return;
                this.isLoading = true;
                
                let url = "{{ route('api.clientes.cartera', ':id') }}".replace(':id', this.cliente.id);
                const params = new URLSearchParams({
                    page: page,
                    // Enviamos los filtros al backend
                    eds_id: this.filters.eds,
                    q_factura: this.filters.q_factura
                });

                fetch(`${url}?${params.toString()}`)
                    .then(res => res.json())
                    .then(response => {
                        this.cartera = response.data;
                        this.meta = response.meta;
                    })
                    .finally(() => this.isLoading = false);
            },

            changePage(page) {
                if (page < 1 || page > this.meta.last_page) return;
                this.cargarCartera(page);
            },

            isSelected(id) { return this.selectedMap.hasOwnProperty(id); },

            toggleSelection(item) {
                if (this.isSelected(item.id)) {
                    delete this.selectedMap[item.id];
                } else {
                    this.selectedMap[item.id] = parseFloat(item.saldo_pendiente);
                }
                this.selectedMap = { ...this.selectedMap };
            },

            updateAmount(id, value) {
                if (this.isSelected(id)) {
                    this.selectedMap[id] = parseFloat(value);
                    this.selectedMap = { ...this.selectedMap };
                }
            },

            formatMoney(amount) {
                return '$' + parseFloat(amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            },
            
            formatDate(dateStr) {
                if (!dateStr) return '';
                return dateStr.split('T')[0];
            },

            submitForm(e) {
                if (this.totalAbono <= 0) {
                    alert('Debes seleccionar al menos una cuenta y definir un monto.');
                    return;
                }
                e.target.submit();
            }
        }))
    })
</script>
@endsection
@extends('layouts.app')

@section('title', 'CRM Cobranzas - Zahara')
@section('page_title', 'Gestión de Compromisos')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Seguimientos</span>
@endsection

@section('content')
<div class="flex flex-col gap-6 w-full max-w-full" 
     x-data="crmManager()" 
     @confirmed-action.window="executeAction()"
     @open-crm.window="openCrm($event.detail.id, $event.detail.name)">

    <!-- ALERTAS SUPERIORES -->
    @if($countHoy > 0 || $countVencidos > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-enter">
        @if($countHoy > 0)
        <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex items-center justify-between shadow-sm cursor-pointer hover:bg-indigo-100 transition-colors" @click="filterStatus('hoy')">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>
                <div><h4 class="font-bold text-indigo-900 text-sm">Compromisos para Hoy</h4><p class="text-xs text-indigo-600">Tienes {{ $countHoy }} clientes por gestionar.</p></div>
            </div>
            <span class="text-2xl font-black text-indigo-400">{{ $countHoy }}</span>
        </div>
        @endif

        @if($countVencidos > 0)
        <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-center justify-between shadow-sm cursor-pointer hover:bg-red-100 transition-colors" @click="filterStatus('vencidos')">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-lg"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                <div><h4 class="font-bold text-red-900 text-sm">Compromisos Vencidos</h4><p class="text-xs text-red-600">{{ $countVencidos }} gestiones urgentes.</p></div>
            </div>
            <span class="text-2xl font-black text-red-400">{{ $countVencidos }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- TOOLBAR -->
    <div class="flex flex-col md:flex-row justify-between gap-4 w-full sticky top-0 z-20 bg-[#F8FAFC]/95 backdrop-blur py-2 md:static md:bg-transparent md:py-0">
        <div class="relative w-full md:max-w-md group shadow-sm rounded-xl">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
            <input type="text" x-model="search" @input.debounce.300ms="performSearch" placeholder="Buscar Cliente..." class="input-pill !pl-12 pr-10 bg-white h-12 md:h-10 text-base md:text-sm shadow-sm border-slate-200 focus:border-indigo-500 w-full">
            <div x-show="isSearching" class="absolute inset-y-0 right-0 pr-4 flex items-center" style="display: none;"><svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
        </div>
        <div class="flex gap-2 p-1 bg-slate-200/60 rounded-xl">
             <button @click="filterStatus('todos')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all" :class="filter === 'todos' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Todos</button>
             <button @click="filterStatus('hoy')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1" :class="filter === 'hoy' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:text-indigo-600'"><span class="w-1.5 h-1.5 rounded-full bg-current" x-show="filter!=='hoy'"></span> Hoy</button>
             <button @click="filterStatus('vencidos')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1" :class="filter === 'vencidos' ? 'bg-red-600 text-white shadow-md' : 'text-slate-500 hover:text-red-600'"><span class="w-1.5 h-1.5 rounded-full bg-current" x-show="filter!=='vencidos'"></span> Vencidos</button>
        </div>
    </div>

    <!-- TABLA PRINCIPAL -->
    <div id="results-container" class="relative min-h-[200px] w-full">
        <div x-show="isSearching" class="absolute inset-0 bg-white/50 z-10 backdrop-blur-[1px] rounded-2xl transition-all" style="display: none;"></div>
        @include('seguimientos.partials.table', ['clientes' => $clientes])
    </div>

    <!-- PANEL LATERAL (CRM) -->
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="isOpen" style="display: none;">
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/75 transition-opacity backdrop-blur-sm" @click="isOpen = false"></div>
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-0 md:pl-10">
                    <div x-show="isOpen" 
                         x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                         class="pointer-events-auto w-screen max-w-7xl">
                        
                        <div class="flex h-full flex-col bg-white shadow-2xl">
                            <!-- Header -->
                            <div class="px-6 py-6 bg-slate-900 text-white shadow-md relative z-10 flex justify-between items-start">
                                <div><h2 class="text-lg font-bold leading-6" x-text="clienteNombre"></h2><p class="text-slate-400 text-xs mt-1">Panel de Gestión de Cobranza</p></div>
                                <button type="button" @click="isOpen = false" class="rounded-md text-slate-400 hover:text-white focus:outline-none"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <!-- BODY: GRID DE 2 COLUMNAS -->
                            <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                                <!-- COLUMNA IZQUIERDA: FACTURAS PENDIENTES -->
                                <div class="w-full md:w-1/2 bg-slate-50 border-r border-slate-200 flex flex-col">
                                    <div class="p-4 border-b border-slate-200 bg-white flex justify-between items-center sticky top-0 z-10">
                                        <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wide">Cuentas Pendientes</h3>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-lg font-bold">Total Sel: <span x-text="formatMoney(totalSeleccionado)"></span></span>
                                            <button @click="toggleAll(true)" class="text-[10px] text-indigo-600 font-bold hover:bg-indigo-50 px-2 py-1 rounded">Todas</button>
                                            <button @click="toggleAll(false)" class="text-[10px] text-slate-500 font-bold hover:bg-slate-100 px-2 py-1 rounded">Ninguna</button>
                                        </div>
                                    </div>
                                    <div class="flex-1 overflow-y-auto p-4">
                                        <div class="space-y-2">
                                            <template x-for="fac in pendientes" :key="fac.id">
                                                <label class="flex items-center gap-3 p-3 bg-white rounded-xl border shadow-sm cursor-pointer transition-all hover:border-indigo-300 relative"
                                                       :class="selectedFacturas.includes(fac.id) ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50' : 'border-slate-200'"
                                                       @click.prevent="toggleSelection(fac.id)">
                                                    <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors" :class="selectedFacturas.includes(fac.id) ? 'bg-indigo-600 border-indigo-600' : 'border-slate-300 bg-white'">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="selectedFacturas.includes(fac.id)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex justify-between"><div class="flex items-center gap-1.5"><span class="font-mono font-bold text-sm text-slate-700" x-text="'#' + fac.consecutivo"></span><span class="text-[10px] text-slate-400 uppercase tracking-wide border border-slate-100 px-1 rounded" x-text="fac.eds"></span></div><span class="font-bold text-sm text-indigo-600" x-text="'$' + fac.saldo_fmt"></span></div>
                                                        <div class="flex justify-between items-center text-[10px] text-slate-500 mt-1"><div class="flex items-center gap-1"><svg class="w-3 h-3 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg><span x-text="fac.corte_desde + ' - ' + fac.corte_hasta"></span></div><span :class="fac.dias_mora > 0 ? 'text-red-500 font-bold' : 'text-emerald-500'" x-text="fac.dias_mora > 0 ? fac.dias_mora + ' días mora' : 'Al día'"></span></div>
                                                    </div>
                                                </label>
                                            </template>
                                            <div x-show="pendientes.length === 0 && !isLoading" class="text-center py-10 text-slate-400 text-xs">Este cliente está al día.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMNA DERECHA: GESTIÓN -->
                                <div class="w-full md:w-1/2 flex flex-col bg-white">
                                    <div class="p-6 border-b border-slate-200 bg-indigo-50/30">
                                        <form @submit.prevent="saveData" class="flex flex-col gap-3">
                                            <input type="hidden" name="cliente_id" :value="clienteId">
                                            <template x-for="id in selectedFacturas" :key="id"><input type="hidden" name="facturas_ids[]" :value="id"></template>
                                            
                                            <div><label class="text-[10px] font-bold text-slate-500 uppercase">Nueva Gestión</label><textarea x-model="form.observacion" rows="2" class="input-pill w-full mt-1 text-sm" placeholder="Resultado de la llamada..." required></textarea></div>
                                            
                                            <div class="grid grid-cols-2 gap-3">
                                                <div><label class="text-[10px] font-bold text-slate-500 uppercase">Fecha Compromiso</label><input type="date" x-model="form.fecha_compromiso" class="input-pill w-full mt-1 text-sm"></div>
                                                <div><label class="text-[10px] font-bold text-slate-500 uppercase">Monto Prometido</label><input type="number" x-model="form.monto_compromiso" class="input-pill w-full mt-1 text-sm" placeholder="$ 0.00"></div>
                                            </div>
                                            
                                            <button type="submit" class="btn-primary w-full justify-center mt-2 text-xs shadow-md transition-colors" :class="editMode ? 'bg-orange-500 hover:bg-orange-600' : 'bg-indigo-600 hover:bg-indigo-700'" :disabled="isSaving">
                                                <span x-show="!isSaving" x-text="editMode ? 'Actualizar Gestión' : (selectedFacturas.length > 0 ? 'Registrar Compromiso' : 'Registrar Gestión')"></span><span x-show="isSaving">Guardando...</span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex-1 overflow-y-auto p-6">
                                        <h3 class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-widest">Historial</h3>
                                        <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:bg-slate-100">
                                            <div x-show="isLoading" class="flex justify-center py-4 relative z-10"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div></div>
                                            <template x-for="item in historial" :key="item.id">
                                                <div class="relative flex items-start group">
                                                    <div class="absolute left-0 h-10 w-10 flex items-center justify-center rounded-full bg-white border-4 transition-colors z-10" :class="item.estado === 'pendiente' ? (new Date(item.fecha_compromiso) < new Date() ? 'border-red-100' : 'border-amber-100') : 'border-emerald-100'">
                                                        <template x-if="item.estado === 'pendiente'"><button @click="confirmAction('check', item.id)" class="w-4 h-4 rounded-full border-2 border-slate-300 hover:border-emerald-500 hover:bg-emerald-50 transition-all" title="Marcar cumplido"></button></template>
                                                        <template x-if="item.estado === 'cumplido'"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></template>
                                                    </div>
                                                    <div class="pl-14 w-full group/item">
                                                        <div class="flex justify-between items-start"><span class="text-xs font-bold text-slate-500" x-text="item.fecha_gestion"></span><div class="flex items-center gap-2"><span class="text-[10px] text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-100" x-text="item.usuario"></span><div class="flex gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity" x-show="item.usuario_id == {{ Auth::id() }}"><button @click="editItem(item)" class="p-1 text-slate-400 hover:text-orange-500" title="Editar"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button><button @click="confirmAction('delete', item.id)" class="p-1 text-slate-400 hover:text-red-500" title="Eliminar"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div></div></div>
                                                        <div class="mt-1 p-3 rounded-xl bg-slate-50 border border-slate-100 group-hover:border-indigo-100 transition-colors">
                                                            <p class="text-sm text-slate-700 whitespace-pre-wrap" x-text="item.observacion"></p>
                                                            <template x-if="item.facturas_afectadas !== 'General'"><div class="mt-2 text-[10px] text-slate-400 font-mono border-t border-slate-200 pt-1">Facturas: <span x-text="item.facturas_afectadas"></span></div></template>
                                                            <template x-if="item.fecha_compromiso"><div class="mt-2 pt-2 border-t border-slate-200 flex items-center gap-2 text-xs"><svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg><span class="font-bold" :class="item.estado === 'pendiente' && new Date(item.fecha_compromiso) < new Date() ? 'text-red-600' : 'text-indigo-600'">Compromiso: <span x-text="item.fecha_compromiso_human"></span></span><template x-if="item.monto"><span class="text-slate-500">- $<span x-text="item.monto"></span></span></template></div></template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.modals-feedback')
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('crmManager', () => ({
            // CORRECCIÓN CLAVE: Inicializar search con cadena vacía si es null para evitar string "null" en JS
            search: @js(request('search') ?? ''),
            filter: @js(request('filtro', 'todos')),
            isSearching: false,
            isOpen: false,
            isLoading: false,
            isSaving: false,
            editMode: false,
            editId: null,
            
            pendingAction: null, 
            pendingId: null,

            clienteId: null,
            clienteNombre: '',
            historial: [],
            pendientes: [],
            selectedFacturas: [],
            form: { observacion: '', fecha_compromiso: '', monto_compromiso: '' },

            get totalSeleccionado() {
                return this.pendientes
                    .filter(f => this.selectedFacturas.includes(f.id))
                    .reduce((sum, f) => sum + parseFloat(f.saldo), 0);
            },

            init() {
                this.$watch('selectedFacturas', value => {
                    if (value.length > 0 && !this.editMode) {
                        this.form.monto_compromiso = this.totalSeleccionado.toFixed(0);
                    } else if (!this.editMode) {
                        this.form.monto_compromiso = '';
                    }
                });
            },

            performSearch() {
                this.isSearching = true;
                // CORRECCIÓN: Asegurar parámetros limpios
                const params = new URLSearchParams();
                if (this.search) params.set('search', this.search);
                params.set('filtro', this.filter);
                params.set('ajax', '1');
                params.set('_t', new Date().getTime());

                // Actualizar URL limpia
                const urlDisplay = new URLSearchParams();
                if (this.search) urlDisplay.set('search', this.search);
                urlDisplay.set('filtro', this.filter);
                window.history.replaceState({}, '', `${window.location.pathname}?${urlDisplay.toString()}`);

                fetch(`${window.location.pathname}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text()).then(html => document.getElementById('results-container').innerHTML = html)
                .finally(() => { this.isSearching = false; });
            },

            filterStatus(status) {
                this.filter = status;
                this.performSearch();
            },

            // ... (Resto de funciones igual) ...
            openCrm(id, name) { this.clienteId = id; this.clienteNombre = name; this.cancelEdit(); this.isOpen = true; this.loadData(); },
            loadData() { this.isLoading = true; let url = "{{ route('api.seguimientos.historial', ':id') }}".replace(':id', this.clienteId); fetch(url).then(r => r.json()).then(data => { this.historial = data.historial; this.pendientes = data.pendientes; }).finally(() => this.isLoading = false); },
            toggleSelection(id) { if (this.selectedFacturas.includes(id)) { this.selectedFacturas = this.selectedFacturas.filter(fid => fid !== id); } else { this.selectedFacturas.push(id); } },
            toggleAll(state) { this.selectedFacturas = state ? this.pendientes.map(f => f.id) : []; },
            saveData() { this.isSaving = true; let url = this.editMode ? "{{ route('seguimientos.update', ':id') }}".replace(':id', this.editId) : "{{ route('seguimientos.store') }}"; let method = this.editMode ? 'PUT' : 'POST'; let payload = { ...this.form, cliente_id: this.clienteId, facturas_ids: this.selectedFacturas }; fetch(url, { method: method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify(payload) }).then(res => { if (!res.ok) return res.json().then(err => { throw err; }); return res.json(); }).then(() => { this.loadData(); this.cancelEdit(); this.$dispatch('toast-show', { message: 'Gestión guardada correctamente.', type: 'success' }); }).catch(error => { let msg = error.message || 'Error al guardar.'; if(error.errors) msg = Object.values(error.errors).flat().join('\n'); this.$dispatch('toast-show', { message: msg, type: 'error' }); }).finally(() => this.isSaving = false); },
            editItem(item) { this.editMode = true; this.editId = item.id; this.form.observacion = item.observacion; this.form.fecha_compromiso = item.fecha_compromiso; this.form.monto_compromiso = item.monto; this.selectedFacturas = item.facturas_ids || []; },
            confirmAction(type, id) { this.pendingAction = type; this.pendingId = id; let title = type === 'delete' ? 'Eliminar Gestión' : 'Marcar Cumplido'; let message = type === 'delete' ? '¿Estás seguro de eliminar este registro?' : '¿Confirmas que el compromiso se ha cumplido?'; let modalType = type === 'delete' ? 'danger' : 'info'; this.$dispatch('modal-confirm', { title, message, type: modalType }); },
            executeAction() { if(!this.pendingId) return; let url = '', method = ''; if(this.pendingAction === 'delete') { url = "{{ route('seguimientos.destroy', ':id') }}".replace(':id', this.pendingId); method = 'DELETE'; } else if(this.pendingAction === 'check') { url = "{{ route('api.seguimientos.check', ':id') }}".replace(':id', this.pendingId); method = 'POST'; } fetch(url, { method: method, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }).then(() => { this.loadData(); this.$dispatch('toast-show', { message: 'Acción realizada.', type: 'success' }); }); },
            cancelEdit() { this.editMode = false; this.editId = null; this.form.observacion = ''; this.form.fecha_compromiso = ''; this.form.monto_compromiso = ''; this.selectedFacturas = []; },
            formatMoney(amount) { return '$' + parseFloat(amount || 0).toLocaleString('en-US', {minimumFractionDigits: 0}); }
        }))
    })
</script>
@endsection
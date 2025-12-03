@extends('layouts.app')

@section('title', 'BI - Zahara')
@section('page_title', 'Tablero de Control')

@section('breadcrumb')
    <span class="text-slate-900 font-medium">Dashboard</span>
@endsection

@section('content')
<div x-data="dashboardBI()" x-init="init()" class="space-y-6 w-full">

    <!-- 1. BARRA DE CONTROL -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm sticky top-0 z-30 transition-shadow duration-300" 
         :class="isLoading ? 'shadow-md border-indigo-200' : ''">
        <div class="flex flex-col md:flex-row justify-between gap-4 items-center">
            <div class="flex flex-wrap gap-3 items-center w-full md:w-auto">
                
                <div class="flex items-center bg-slate-50 rounded-xl p-1 border border-slate-200">
                    <input type="date" x-model="filters.fecha_ini" @change="updateData" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 p-1.5 outline-none">
                    <span class="text-slate-400 px-1">-</span>
                    <input type="date" x-model="filters.fecha_fin" @change="updateData" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 p-1.5 outline-none">
                </div>

                <div class="relative">
                    <select x-model="filters.eds_id" @change="handleEdsChange" class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer">
                        <option value="">Todas las Estaciones</option>
                        @foreach($eds_list as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-2 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div x-show="isLoading" class="text-indigo-600 text-xs font-bold flex items-center gap-1 animate-pulse" style="display: none;">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Calculando...
                </div>
            </div>

            <!-- Badges Filtros Activos -->
            <div class="flex gap-2 mt-2" x-show="filters.rango_mora" x-cloak x-transition>
                <button @click="clearMoraFilter()" class="flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded-full text-[10px] font-bold hover:bg-indigo-700 transition shadow-sm group">
                    <span>Filtro:</span>
                    <span x-text="filters.rango_mora"></span> 
                    <svg class="w-3 h-3 ml-1 opacity-50 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. TARJETAS KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Cartera Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cartera Viva</p>
            <h3 class="text-3xl font-black text-slate-800 mt-1 transition-all duration-300" x-text="kpis.cartera.total">...</h3>
            <div class="mt-2 text-xs text-slate-500">Ticket: <span x-text="kpis.cartera.ticket_promedio"></span></div>
        </div>

        <!-- Vencido -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Vencida</p>
            <h3 class="text-3xl font-black text-red-500 mt-1" x-text="kpis.cartera.vencida">...</h3>
            <div class="mt-2 text-xs text-slate-500">
                Representa el <strong class="text-red-500" x-text="kpis.cartera.porc_vencida + '%'"></strong> del total
            </div>
        </div>

        <!-- Recaudo -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Recaudo</p>
            <h3 class="text-3xl font-black text-emerald-600 mt-1" x-text="kpis.recaudo.actual">...</h3>
            <div class="mt-2 flex items-center gap-1 text-xs font-bold">
                <span :class="kpis.recaudo.trend === 'up' ? 'text-emerald-600' : 'text-red-500'">
                    <span x-text="kpis.recaudo.trend === 'up' ? '▲' : '▼'"></span> <span x-text="kpis.recaudo.variacion + '%'"></span>
                </span>
                <span class="text-slate-400 font-normal">vs mes ant.</span>
            </div>
        </div>

        <!-- Días Mora -->
        <div class="bg-slate-900 p-5 rounded-2xl shadow-lg flex flex-col justify-center text-white relative overflow-hidden">
            <div class="absolute -right-2 -top-2 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Días Ponderados</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-4xl font-black tracking-tighter" x-text="kpis.cartera.dias_mora_pond">0</h3>
                <span class="text-sm font-medium text-slate-400">días</span>
            </div>
            <div class="mt-2 w-full bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-1000" :style="`width: ${Math.min(kpis.cartera.dias_mora_pond, 100)}%`"></div>
            </div>
        </div>
    </div>

    <!-- 3. GRÁFICOS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Aging de Cartera (Click para filtrar)</h3>
            <div class="relative h-72 w-full"><canvas id="chartAging"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Top EDS Recaudo</h3>
            <div class="relative h-72 w-full flex justify-center"><canvas id="chartRecaudo"></canvas></div>
        </div>
    </div>

    <!-- 4. TOP CLIENTES Y RANKING -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-700">Top 15 Deudores</h3>
                <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg font-bold">
                    Concentración Top 5: <span x-text="kpis.riesgo.concentracion + '%'"></span>
                </span>
            </div>
            <div class="relative h-[500px] w-full"><canvas id="chartTopClientes"></canvas></div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Ranking de Riesgo por EDS</h3>
            <div class="overflow-x-auto h-[500px] custom-scrollbar">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-3 py-2 bg-slate-50">Estación</th>
                            <th class="px-3 py-2 text-right bg-slate-50">Deuda Total</th>
                            <th class="px-3 py-2 text-right text-red-500 bg-slate-50">% Vencido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="e in edsRiesgo" :key="e.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2 font-medium text-slate-700" x-text="e.nombre"></td>
                                <td class="px-3 py-2 text-right font-mono text-slate-600" x-text="formatMoney(e.total)"></td>
                                <td class="px-3 py-2 text-right font-bold">
                                    <span :class="e.porc_vencido > 50 ? 'text-red-600' : (e.porc_vencido > 20 ? 'text-amber-600' : 'text-emerald-600')" 
                                          x-text="parseFloat(e.porc_vencido).toFixed(1) + '%'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardBI', () => {
            let chartAging = null;
            let chartRecaudo = null;
            let chartTop = null;
            let edsIdsMap = [];

            // COLORES AGING (11 Categorías)
            const AGING_COLORS = [
                '#10B981', // Corriente
                '#34D399', // 1-7
                '#6EE7B7', // 8-15
                '#A7F3D0', // 16-22
                '#FCD34D', // 23-30
                '#F59E0B', // 31-60
                '#D97706', // 61-90
                '#F87171', // 91-120
                '#EF4444', // 121-150
                '#DC2626', // 151-180
                '#7F1D1D'  // +180
            ];

            return {
                isLoading: false,
                filters: {
                    eds_id: '',
                    rango_mora: '',
                    fecha_ini: '{{ date("Y-m-01") }}',
                    fecha_fin: '{{ date("Y-m-d") }}'
                },
                kpis: { 
                    cartera: { total: 0, vencida: 0, corriente: 0, porc_vencida: 0, facturas_vivas: 0, ticket_promedio: 0, dias_mora_pond: 0 },
                    recaudo: { actual: 0, anterior: 0, variacion: 0, trend: 'up' },
                    riesgo: { clientes_mora: 0, clientes_total: 0, porc_clientes_mora: 0, criticos: 0, concentracion: 0 }
                },
                edsRiesgo: [],

                init() {
                    this.initCharts();
                    this.updateData();
                },

                // Lógica para obtener colores dinámicos (Efecto Power BI)
                getAgingColors(labels) {
                    // Si no hay filtro, devolver colores originales
                    if (!this.filters.rango_mora) return AGING_COLORS;
                    
                    // Si hay filtro, devolver gris para los no seleccionados
                    return labels.map((label, index) => {
                        return label === this.filters.rango_mora ? AGING_COLORS[index] : '#E2E8F0'; // Color base vs Gris
                    });
                },

                updateData() {
                    this.isLoading = true;
                    const params = new URLSearchParams(this.filters).toString();
                    
                    fetch(`{{ route('api.dashboard.data') }}?${params}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.error) return console.error(data.error);

                            this.kpis = data.kpis;
                            this.edsRiesgo = data.charts.eds_riesgo;

                            // Actualizar Aging con colores dinámicos
                            if (chartAging) {
                                chartAging.data.labels = data.charts.aging.labels;
                                chartAging.data.datasets[0].data = data.charts.aging.data;
                                chartAging.data.datasets[0].backgroundColor = this.getAgingColors(data.charts.aging.labels);
                                chartAging.update();
                            }

                            // Actualizar otros gráficos normalmente
                            this.updateChart(chartTop, data.charts.top_clientes.labels, data.charts.top_clientes.data);
                            
                            edsIdsMap = data.charts.recaudo_eds.ids; 
                            this.updateChart(chartRecaudo, data.charts.recaudo_eds.labels, data.charts.recaudo_eds.data);
                        })
                        .catch(e => console.error(e))
                        .finally(() => this.isLoading = false);
                },

                clearMoraFilter() {
                    this.filters.rango_mora = '';
                    this.updateData();
                },

                updateChart(chart, labels, data) {
                    if (chart) {
                        chart.data.labels = labels;
                        chart.data.datasets[0].data = data;
                        chart.update();
                    }
                },

                resetFilters() {
                    this.filters.eds_id = '';
                    this.filters.rango_mora = '';
                    this.updateData();
                },

                handleEdsChange() { this.updateData(); },
                formatMoney(v) { return '$' + parseFloat(v).toLocaleString('en-US', {maximumFractionDigits: 0}); },

                initCharts() {
                    const self = this;
                    const commonOptions = { responsive: true, maintainAspectRatio: false };

                    // 1. AGING
                    const ctxAging = document.getElementById('chartAging');
                    if(ctxAging) {
                        const old = Chart.getChart(ctxAging); if(old) old.destroy();
                        chartAging = new Chart(ctxAging, {
                            type: 'bar',
                            data: { labels: [], datasets: [{ label: 'Cartera', data: [], backgroundColor: AGING_COLORS, borderRadius: 6, minBarLength: 5 }] },
                            options: {
                                ...commonOptions,
                                plugins: { legend: { display: false }, tooltip: { intersect: false } },
                                scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } },
                                onClick: (e, elements, chart) => {
                                    if (elements.length > 0) {
                                        const idx = elements[0].index;
                                        const label = chart.data.labels[idx];
                                        
                                        // Toggle: Si clic en la misma, limpiar filtro
                                        if (self.filters.rango_mora === label) {
                                            self.filters.rango_mora = '';
                                        } else {
                                            self.filters.rango_mora = label;
                                        }
                                        self.updateData();
                                    }
                                },
                                onHover: (e, elements) => { e.native.target.style.cursor = elements.length ? 'pointer' : 'default'; }
                            }
                        });
                    }

                    // 2. RECAUDO
                    const ctxRecaudo = document.getElementById('chartRecaudo');
                    if(ctxRecaudo) {
                        const old = Chart.getChart(ctxRecaudo); if(old) old.destroy();
                        chartRecaudo = new Chart(ctxRecaudo, {
                            type: 'doughnut',
                            data: { labels: [], datasets: [{ data: [], backgroundColor: ['#6366F1', '#8B5CF6', '#EC4899', '#F43F5E', '#10B981', '#F59E0B'], borderWidth: 0 }] },
                            options: {
                                ...commonOptions,
                                plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 }, usePointStyle: true } } },
                                cutout: '65%',
                                onClick: (e, elements) => {
                                    if (elements.length > 0) {
                                        const idx = elements[0].index;
                                        const id = edsIdsMap[idx]; 
                                        if(id) {
                                            self.filters.eds_id = id;
                                            self.updateData();
                                        }
                                    }
                                },
                                onHover: (e, elements) => { e.native.target.style.cursor = elements.length ? 'pointer' : 'default'; }
                            }
                        });
                    }

                    // 3. TOP
                    const ctxTop = document.getElementById('chartTopClientes');
                    if(ctxTop) {
                        const old = Chart.getChart(ctxTop); if(old) old.destroy();
                        chartTop = new Chart(ctxTop, {
                            type: 'bar',
                            data: { labels: [], datasets: [{ label: 'Deuda', data: [], backgroundColor: '#4F46E5', borderRadius: 4, barThickness: 15, minBarLength: 5 }] },
                            options: {
                                ...commonOptions,
                                indexAxis: 'y', 
                                plugins: { legend: { display: false } },
                                scales: { x: { beginAtZero: true, grid: { borderDash: [2, 4] } }, y: { grid: { display: false } } },
                                onClick: (e, elements, chart) => {
                                    if (elements.length > 0) {
                                        const idx = elements[0].index;
                                        const clientName = chart.data.labels[idx];
                                        let url = `{{ route('cartera_cuentas.index') }}?search=${encodeURIComponent(clientName)}`;
                                        if(self.filters.eds_id) url += `&eds_id=${self.filters.eds_id}`;
                                        window.location.href = url;
                                    }
                                },
                                onHover: (e, elements) => { e.native.target.style.cursor = elements.length ? 'pointer' : 'default'; }
                            }
                        });
                    }
                }
            };
        });
    });
</script>
@endsection
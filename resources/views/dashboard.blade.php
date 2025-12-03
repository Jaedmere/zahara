@extends('layouts.app')

@section('title', 'Dashboard Financiero - Zahara')
@section('page_title', 'Tablero de Control')

@section('breadcrumb')
    <span class="text-slate-900 font-medium">Dashboard</span>
@endsection

@section('content')
<div x-data="dashboardBI()" x-init="initDashboard()" class="space-y-6 w-full">

    <!-- BARRA DE FILTROS -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between sticky top-0 z-20">
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto items-center">
            
            <!-- Filtro EDS -->
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <select x-model="filters.eds_id" @change="updateData" class="input-pill pl-9 text-xs py-2 cursor-pointer appearance-none bg-slate-50">
                    <option value="">Todas las Estaciones</option>
                    @foreach($eds_list as $eds)
                        <option value="{{ $eds->id }}">{{ $eds->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Fechas (Para Recaudo) -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="date" x-model="filters.fecha_ini" @change="updateData" class="input-pill text-xs py-2 w-full md:w-auto" title="Desde">
                <span class="text-slate-300">-</span>
                <input type="date" x-model="filters.fecha_fin" @change="updateData" class="input-pill text-xs py-2 w-full md:w-auto" title="Hasta">
            </div>
        </div>

        <!-- Spinner de Carga -->
        <div x-show="isLoading" class="flex items-center gap-2 text-indigo-600 text-xs font-bold animate-pulse" style="display: none;">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Actualizando...
        </div>
    </div>

    <!-- TARJETAS KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- KPI 1: Cartera Total -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera Total Viva</p>
            <div>
                <h3 class="text-2xl font-black text-slate-800" x-text="'$' + kpis.total_cartera">...</h3>
                <p class="text-[10px] text-slate-400 mt-1">Saldo pendiente global</p>
            </div>
        </div>

        <!-- KPI 2: Cartera Vencida -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-bold text-red-400 uppercase tracking-wider">Cartera Vencida</p>
            <div>
                <h3 class="text-2xl font-black text-red-600" x-text="'$' + kpis.total_vencido">...</h3>
                <p class="text-[10px] text-slate-400 mt-1">
                    Representa el <span class="font-bold text-red-500" x-text="kpis.porc_vencido + '%'"></span> del total
                </p>
            </div>
        </div>

        <!-- KPI 3: Recaudo (Filtro Fecha) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between h-28 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Recaudo Periodo</p>
            <div>
                <h3 class="text-2xl font-black text-emerald-600" x-text="'$' + kpis.total_recaudo">...</h3>
                <p class="text-[10px] text-slate-400 mt-1">Abonos registrados en fecha</p>
            </div>
        </div>

        <!-- KPI 4: Accesos Directos -->
        <div class="bg-indigo-600 p-5 rounded-2xl shadow-lg shadow-indigo-200 flex flex-col justify-center gap-3 text-white">
            <a href="{{ route('facturas.create') }}" class="flex items-center gap-2 hover:text-indigo-200 transition-colors">
                <div class="bg-white/20 p-1.5 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></div>
                <span class="text-sm font-bold">Nueva Cuenta</span>
            </a>
            <a href="{{ route('abonos.create') }}" class="flex items-center gap-2 hover:text-indigo-200 transition-colors">
                <div class="bg-white/20 p-1.5 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/></svg></div>
                <span class="text-sm font-bold">Nuevo Recaudo</span>
            </a>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- GRÁFICO 1: Aging (Edades) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Edades de Cartera (Aging)</h3>
            <div class="relative h-64 w-full">
                <canvas id="chartAging"></canvas>
            </div>
        </div>

        <!-- GRÁFICO 2: Por EDS (Doughnut) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Distribución por Estación</h3>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="chartEds"></canvas>
            </div>
        </div>
    </div>
    
    <!-- GRÁFICO 3: Top Clientes (Bar Horizontal) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Top 5 Clientes Deudores</h3>
        <div class="relative h-64 w-full">
            <canvas id="chartTopClientes"></canvas>
        </div>
    </div>

</div>

<!-- LIBRERÍA CHART.JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardBI', () => {
            // --- VARIABLES LOCALES (NO REACTIVAS) ---
            // Esto evita el "Maximum call stack size exceeded" porque Alpine NO las vigila
            let chartAging = null;
            let chartEds = null;
            let chartTop = null;

            return {
                isLoading: false,
                filters: {
                    eds_id: '',
                    fecha_ini: '{{ date("Y-m-01") }}',
                    fecha_fin: '{{ date("Y-m-d") }}'
                },
                kpis: { total_cartera: 0, total_vencido: 0, porc_vencido: 0, total_recaudo: 0 },

                initDashboard() {
                    this.initCharts();
                    this.updateData();
                },

                updateData() {
                    this.isLoading = true;
                    const params = new URLSearchParams(this.filters).toString();
                    
                    fetch(`{{ route('api.dashboard.data') }}?${params}`)
                        .then(res => res.json())
                        .then(data => {
                            this.kpis = data.kpis;
                            // Usamos las variables locales para actualizar
                            this.updateChart(chartAging, data.charts.aging.labels, data.charts.aging.data);
                            this.updateChart(chartEds, data.charts.eds.labels, data.charts.eds.data);
                            this.updateChart(chartTop, data.charts.top_clientes.labels, data.charts.top_clientes.data);
                        })
                        .catch(err => console.error('Error API Dashboard:', err))
                        .finally(() => this.isLoading = false);
                },

                updateChart(chartInstance, labels, data) {
                    if (chartInstance) {
                        chartInstance.data.labels = labels;
                        chartInstance.data.datasets[0].data = data;
                        chartInstance.update();
                    }
                },

                initCharts() {
                    // Asignamos a las variables locales
                    const commonOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                    };

                    chartAging = new Chart(document.getElementById('chartAging'), {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{ label: 'Monto', data: [], backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#7C3AED'], borderRadius: 4 }]
                        },
                        options: { ...commonOptions, plugins: { legend: { display: false } } }
                    });

                    chartEds = new Chart(document.getElementById('chartEds'), {
                        type: 'doughnut',
                        data: {
                            labels: [],
                            datasets: [{ data: [], backgroundColor: ['#6366F1', '#8B5CF6', '#EC4899', '#F43F5E', '#10B981'], borderWidth: 0 }]
                        },
                        options: { ...commonOptions, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, cutout: '70%' }
                    });

                    chartTop = new Chart(document.getElementById('chartTopClientes'), {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{ label: 'Deuda', data: [], backgroundColor: '#6366F1', borderRadius: 4, barThickness: 20 }]
                        },
                        options: { ...commonOptions, indexAxis: 'y', plugins: { legend: { display: false } } }
                    });
                }
            };
        });
    })
</script>
@endsection
@extends('layouts.app')

@section('title', 'Cartera por Edades - Zahara')
@section('page_title', 'Cartera por Edades')
@section('page_subtitle', 'Foto de la cartera agrupada por rangos de vencimiento a una fecha de corte.')

@section('breadcrumb')
    <span>Inicio</span> /
    <span class="text-slate-900 font-medium">Reportes</span> /
    <span class="text-slate-900 font-medium">Cartera por Edades</span>
@endsection

@section('content')

{{-- CONFIG JS --}}
<script>
    window.carteraEdadesConfig = {
        buckets: @json($buckets),
        fechaCorte: @json($fechaCorte),
        bucketSeleccionado: @json($bucketSeleccionado ?? null),
    };
</script>

<div class="flex flex-col gap-6 w-full max-w-full" x-data="carteraEdades()">

    {{-- FILTRO FECHA CORTE --}}
    <div class="bg-white rounded-2xl shadow-sm border p-5 flex flex-col md:flex-row md:items-end md:justify-between gap-4 animate-enter">
        <div>
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                Fecha de corte
            </label>
            <form method="GET" action="{{ route('informes.cartera_edades') }}" class="flex flex-col sm:flex-row gap-3 mt-1">
                <input type="date" name="fecha_corte"
                       value="{{ $fechaCorte }}"
                       class="input-pill h-11 text-sm w-full sm:w-48">

                <button type="submit"
                        class="btn-primary h-11 flex items-center gap-2 justify-center w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h6a1 1 0 011 1v2h9a1 1 0 011 1v4h-2V8h-8v2a1 1 0 01-1 1H4v9H3a1 1 0 01-1-1V5a1 1 0 011-1z" />
                    </svg>
                    Aplicar
                </button>
            </form>
            <p class="text-[11px] text-slate-400 mt-1">
                La cartera considera facturas y abonos registrados hasta esta fecha.
            </p>

            @if($bucketSeleccionado)
                <p class="text-[11px] text-indigo-600 mt-1">
                    Filtro activo:
                    <span class="font-semibold">
                        @switch($bucketSeleccionado)
                            @case('corriente') Corriente (no vencida) @break
                            @case('d1_7') 1 - 7 días @break
                            @case('d8_15') 8 - 15 días @break
                            @case('d16_22') 16 - 22 días @break
                            @case('d23_30') 23 - 30 días @break
                            @case('d31_60') 31 - 60 días @break
                            @case('d61_90') 61 - 90 días @break
                            @case('d91_180') 91 - 180 días @break
                            @case('d181_360') 181 - 360 días @break
                            @case('d360_mas') + 360 días @break
                        @endswitch
                    </span>
                </p>

                <a href="{{ route('informes.cartera_edades', ['fecha_corte' => $fechaCorte]) }}"
                   class="inline-flex items-center gap-1 mt-2 text-[11px] text-slate-600 hover:text-slate-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Quitar filtro de rango
                </a>
            @endif
        </div>

        {{-- CARDS KPI (en miles de millones) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full md:w-auto">
            {{-- TOTAL CARTERA --}}
            <div class="bg-slate-900 text-white rounded-2xl p-3 flex flex-col justify-between">
                <p class="text-[10px] uppercase tracking-widest text-slate-400">Total cartera</p>
                <p class="text-xl font-bold mt-1">
                    {{ number_format($totalCartera / 1000000000, 2, ',', '.') }} B$
                </p>
                <p class="text-[10px] text-slate-400 mt-1">
                    A {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }}<br>
                    <span class="text-[9px]">Equivalente: ${{ number_format($totalCartera, 0, ',', '.') }}</span>
                </p>
            </div>

            {{-- >30 DÍAS --}}
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-3">
                <p class="text-[10px] uppercase tracking-widest text-amber-600">&gt; 30 días</p>
                <p class="text-lg font-bold text-amber-900 mt-1">
                    {{ number_format($totalMas30 / 1000000000, 2, ',', '.') }} B$
                </p>
                <p class="text-[9px] text-amber-700 mt-1">
                    ${{ number_format($totalMas30, 0, ',', '.') }}
                </p>
            </div>

            {{-- >90 DÍAS --}}
            <div class="bg-red-50 border border-red-100 rounded-2xl p-3">
                <p class="text-[10px] uppercase tracking-widest text-red-600">&gt; 90 días</p>
                <p class="text-lg font-bold text-red-900 mt-1">
                    {{ number_format($totalMas90 / 1000000000, 2, ',', '.') }} B$
                </p>
                <p class="text-[9px] text-red-700 mt-1">
                    ${{ number_format($totalMas90, 0, ',', '.') }}
                </p>
            </div>

            {{-- CLIENTES / FACTURAS --}}
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3">
                <p class="text-[10px] uppercase tracking-widest text-slate-600">Clientes con saldo</p>
                <p class="text-lg font-bold text-slate-900 mt-1">
                    {{ $totalClientes }}
                </p>
                <p class="text-[10px] text-slate-400 mt-1">Facturas: {{ $totalFacturas }}</p>
            </div>
        </div>
    </div>

    {{-- GRAFICA + TABLA RESUMEN BUCKETS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 animate-enter">
        {{-- GRAFICA --}}
        <div class="bg-white rounded-2xl shadow-sm border p-4 flex flex-col">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase">
                    Distribución por edades
                </h3>
                <span class="text-[11px] text-slate-400">
                    Valores en millones (M$) – Click para filtrar
                </span>
            </div>

            <div class="flex-1 flex items-center justify-center min-h-[220px]">
                <canvas id="chartEdades" class="w-full max-h-[260px]"></canvas>
            </div>

            @if ($totalCartera <= 0)
                <p class="text-[11px] text-slate-400 text-center mt-3">
                    No hay cartera pendiente a la fecha de corte.
                </p>
            @endif
        </div>

        {{-- TABLA RESUMEN BUCKETS --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border p-4">
            <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase mb-3">
                Resumen por rango de vencimiento
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Rango</th>
                            <th class="px-3 py-2 text-right">Valor</th>
                            <th class="px-3 py-2 text-right">% sobre cartera</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $labels = [
                                'corriente'  => 'Corriente (no vencida)',
                                'd1_7'       => '1 - 7 días',
                                'd8_15'      => '8 - 15 días',
                                'd16_22'     => '16 - 22 días',
                                'd23_30'     => '23 - 30 días',
                                'd31_60'     => '31 - 60 días',
                                'd61_90'     => '61 - 90 días',
                                'd91_180'    => '91 - 180 días',
                                'd181_360'   => '181 - 360 días',
                                'd360_mas'   => '+ 360 días',
                            ];
                        @endphp

                        @foreach($labels as $key => $label)
                            @php
                                $valor = $buckets[$key] ?? 0;
                                $porc  = $totalCartera > 0 ? ($valor / $totalCartera) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-3 py-2 text-slate-700">{{ $label }}</td>
                                <td class="px-3 py-2 text-right font-mono">
                                    ${{ number_format($valor, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] text-slate-500">
                                    {{ number_format($porc, 1, ',', '.') }} %
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-[11px] text-slate-400 mt-3">
                Una alta concentración en rangos mayores a 90 días indica riesgo de incobrabilidad y necesidad de gestión prioritaria.
            </p>
        </div>
    </div>

    {{-- DETALLE FACTURAS (PAGINADO) --}}
    <div class="bg-white rounded-2xl shadow-sm border p-4 animate-enter">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase">
                    Facturas con saldo a corte
                </h3>
                <span class="text-[11px] text-slate-400">
                    Registros paginados ({{ $detalles->total() }} en total)
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Exportar CSV respetando filtros actuales (placeholder) --}}
                @php
                    $exportParams = array_merge(request()->except('page'), ['export' => 'csv']);
                @endphp
                <a href="{{ route('informes.cartera_edades', $exportParams) }}"
                   class="btn-secondary h-9 px-3 text-[11px] flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    Exportar CSV
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Cliente</th>
                        <th class="px-3 py-2 text-left">NIT</th>
                        <th class="px-3 py-2 text-left">EDS</th>
                        <th class="px-3 py-2 text-left">Cuenta</th>
                        <th class="px-3 py-2 text-left">Emisión</th>
                        <th class="px-3 py-2 text-left">Vence</th>
                        <th class="px-3 py-2 text-right">Días</th>
                        <th class="px-3 py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($detalles as $f)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2 text-slate-700">{{ $f['cliente'] }}</td>
                            <td class="px-3 py-2 text-slate-500 text-[11px]">{{ $f['documento'] }}</td>
                            <td class="px-3 py-2 text-slate-600 text-[11px]">{{ $f['eds'] }}</td>
                            <td class="px-3 py-2 font-mono text-[11px]">
                                {{ $f['prefijo'] }}-{{ $f['consecutivo'] }}
                            </td>
                            <td class="px-3 py-2 text-[11px]">
                                {{ \Carbon\Carbon::parse($f['fecha_emision'])->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 text-[11px]">
                                {{ \Carbon\Carbon::parse($f['fecha_venc'])->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 text-right text-[11px] {{ $f['dias_vencidos'] > 0 ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                {{ $f['dias_vencidos'] }}
                            </td>
                            <td class="px-3 py-2 text-right font-mono">
                                ${{ number_format($f['saldo_corte'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-slate-400 text-xs">
                                No hay facturas con saldo pendiente a la fecha de corte seleccionada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $detalles->links() }}
        </div>
    </div>

</div>

{{-- SCRIPTS: Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('carteraEdades', () => ({
            init() {
                this.initChart();
            },
            initChart() {
                const cfg = window.carteraEdadesConfig || { buckets: {}, fechaCorte: null };
                const dataBuckets = cfg.buckets || {};

                const labels = [
                    'Corriente',
                    '1 - 7 días',
                    '8 - 15 días',
                    '16 - 22 días',
                    '23 - 30 días',
                    '31 - 60 días',
                    '61 - 90 días',
                    '91 - 180 días',
                    '181 - 360 días',
                    '+ 360 días',
                ];

                const keys = [
                    'corriente',
                    'd1_7',
                    'd8_15',
                    'd16_22',
                    'd23_30',
                    'd31_60',
                    'd61_90',
                    'd91_180',
                    'd181_360',
                    'd360_mas',
                ];

                // Datos en millones
                const dataValues = keys.map(k => {
                    const valor = parseFloat(dataBuckets[k] || 0);
                    return (valor / 1_000_000).toFixed(2);
                });

                const ctx = document.getElementById('chartEdades');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Cartera (M$)',
                            data: dataValues,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const millones = context.parsed.y || 0;
                                        return ' ' + millones + ' M$';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    font: { size: 10 },
                                }
                            },
                            y: {
                                ticks: {
                                    font: { size: 10 },
                                },
                                beginAtZero: true,
                            }
                        },
                        // CLICK EN LA BARRA -> FILTRA POR BUCKET
                        onClick: (evt, elements) => {
                            if (!elements.length) return;
                            const idx   = elements[0].index;
                            const key   = keys[idx];

                            const url   = new URL(window.location.href);
                            url.searchParams.set('bucket', key);
                            url.searchParams.set('page', 1);
                            window.location.href = url.toString();
                        }
                    }
                });
            }
        }))
    });
</script>

@endsection

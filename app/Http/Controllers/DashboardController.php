<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Abono;
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $eds_list = EDS::where('activo', true)->select('id', 'nombre')->orderBy('nombre')->get();
        return view('dashboard', compact('eds_list'));
    }

    public function getData(Request $request)
    {
        try {
            $eds_id = $request->input('eds_id');
            $rango_mora = $request->input('rango_mora');
            
            $fecha_ini = $request->input('fecha_ini', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fecha_fin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // ---------------------------------------------------------
            // 1. DEFINICIÓN DE SCOPES (CONTEXTOS DE DATOS)
            // ---------------------------------------------------------

            // A. CONTEXTO GLOBAL (Solo filtros de jerarquía superior: EDS)
            // Se usa para: Gráfico Aging (para mostrar todas las barras aunque selecciones una)
            $qGlobal = Factura::query()
                ->where('facturas.saldo_pendiente', '>', 0)
                ->where('facturas.estado', '!=', 'anulada')
                ->when($eds_id, fn($q) => $q->where('facturas.eds_id', $eds_id));

            // B. CONTEXTO FILTRADO (Aplica TODOS los filtros activos, incluido el clic)
            // Se usa para: KPIs, Top Clientes, Ranking EDS (estos sí deben responder al clic)
            $qFiltered = clone $qGlobal;
            if ($rango_mora) {
                $this->aplicarFiltroRangoMora($qFiltered, $rango_mora);
            }

            // C. CONTEXTO RECAUDO
            $qRecaudo = Abono::query()
                ->when($eds_id, fn($q) => $q->where('abonos.eds_id', $eds_id))
                ->whereBetween('abonos.fecha', [$fecha_ini, $fecha_fin]);

            // ---------------------------------------------------------
            // 2. CÁLCULOS KPI (Usan $qFiltered)
            // ---------------------------------------------------------
            
            $totalCartera = (clone $qFiltered)->sum('facturas.saldo_pendiente');
            $totalVencido = (clone $qFiltered)->where('facturas.fecha_vencimiento', '<', now())->sum('facturas.saldo_pendiente');
            $porcVencido = $totalCartera > 0 ? ($totalVencido / $totalCartera) * 100 : 0;
            $countFacturas = (clone $qFiltered)->count('facturas.id');
            $ticketPromedio = $countFacturas > 0 ? $totalCartera / $countFacturas : 0;

            // Días Ponderados
            $ponderadoData = (clone $qFiltered)
                ->selectRaw('SUM(GREATEST(0, DATEDIFF(NOW(), facturas.fecha_vencimiento)) * facturas.saldo_pendiente) as numerador')
                ->value('numerador');
            $diasMoraPonderado = $totalCartera > 0 ? $ponderadoData / $totalCartera : 0;

            // Recaudo
            $totalRecaudo = (clone $qRecaudo)->sum('abonos.valor');
            
            $mesAnteriorIni = Carbon::parse($fecha_ini)->subMonth();
            $mesAnteriorFin = Carbon::parse($fecha_fin)->subMonth();
            $recaudoAnterior = Abono::query()
                ->when($eds_id, fn($q) => $q->where('abonos.eds_id', $eds_id))
                ->whereBetween('abonos.fecha', [$mesAnteriorIni, $mesAnteriorFin])
                ->sum('abonos.valor');

            $variacionRecaudo = 0;
            if ($recaudoAnterior > 0) {
                $variacionRecaudo = (($totalRecaudo - $recaudoAnterior) / $recaudoAnterior) * 100;
            }

            // Riesgo Clientes (Usan $qFiltered)
            $clientesCriticos = (clone $qFiltered)
                ->select('facturas.cliente_id', DB::raw('SUM(facturas.saldo_pendiente) as total'))
                ->groupBy('facturas.cliente_id')
                ->having('total', '>', 20000000)->get()->count();
            
            $totalClientes = (clone $qFiltered)->distinct('facturas.cliente_id')->count('facturas.cliente_id');
            $clientesMora = (clone $qFiltered)->where('facturas.fecha_vencimiento', '<', now())->distinct('facturas.cliente_id')->count('facturas.cliente_id');
            $porcClientesMora = $totalClientes > 0 ? ($clientesMora / $totalClientes) * 100 : 0;

            // ---------------------------------------------------------
            // 3. DATOS PARA GRÁFICOS
            // ---------------------------------------------------------

            // GRÁFICO 1: AGING (Usamos $qGlobal para mantener las barras visibles)
            // ¡CORRECCIÓN! Usamos el scope global para que las barras no desaparezcan
            $facturasAging = (clone $qGlobal)->select('fecha_vencimiento', 'saldo_pendiente')->get();
            
            $aging = [
                'Corriente'    => 0,
                '1-7 Días'     => 0,
                '8-15 Días'    => 0,
                '16-22 Días'   => 0,
                '23-30 Días'   => 0,
                '31-60 Días'   => 0,
                '61-90 Días'   => 0,
                '91-120 Días'  => 0,
                '121-150 Días' => 0,
                '151-180 Días' => 0,
                '+180 Días'    => 0,
            ];
            
            $now = Carbon::now()->startOfDay();
            foreach ($facturasAging as $f) {
                $vencimiento = Carbon::parse($f->fecha_vencimiento)->startOfDay();
                if ($vencimiento->gte($now)) {
                    $aging['Corriente'] += $f->saldo_pendiente;
                } else {
                    $dias = $vencimiento->diffInDays($now);
                    if ($dias <= 7)        $aging['1-7 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 15)   $aging['8-15 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 22)   $aging['16-22 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 30)   $aging['23-30 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 60)   $aging['31-60 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 90)   $aging['61-90 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 120)  $aging['91-120 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 150)  $aging['121-150 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 180)  $aging['151-180 Días'] += $f->saldo_pendiente;
                    else                   $aging['+180 Días'] += $f->saldo_pendiente;
                }
            }

            // GRÁFICO 2: Ranking EDS (Usamos $qFiltered)
            // Este SÍ debe filtrarse. Si selecciono "1-30 días", quiero ver qué EDS tienen deuda de 1-30 días.
            $rankingEds = (clone $qFiltered)
                ->join('eds', 'facturas.eds_id', '=', 'eds.id')
                ->select(
                    'eds.nombre', 
                    'eds.id',
                    DB::raw('SUM(facturas.saldo_pendiente) as total'),
                    DB::raw("SUM(CASE WHEN facturas.fecha_vencimiento < CURDATE() THEN facturas.saldo_pendiente ELSE 0 END) as vencido")
                )
                ->groupBy('eds.id', 'eds.nombre')
                ->orderByDesc('vencido')->limit(20)->get()
                ->map(function($item) {
                    $item->porc_vencido = $item->total > 0 ? ($item->vencido / $item->total) * 100 : 0;
                    return $item;
                });

            // GRÁFICO 3: Top Clientes (Usamos $qFiltered)
            // Igual, quiero ver los clientes que me deben en ESE rango de tiempo.
            $topDeudores = (clone $qFiltered)
                ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
                ->select('clientes.razon_social', DB::raw('SUM(facturas.saldo_pendiente) as deuda'))
                ->groupBy('clientes.id', 'clientes.razon_social')
                ->orderByDesc('deuda')
                ->limit(15)
                ->get();
            
            $deudaTop5 = $topDeudores->take(5)->sum('deuda');
            $concentracionTop5 = $totalCartera > 0 ? ($deudaTop5 / $totalCartera) * 100 : 0;

            // Recaudo EDS (Independiente del filtro de mora, usa su propio scope)
            $recaudoEds = (clone $qRecaudo)
                ->join('eds', 'abonos.eds_id', '=', 'eds.id')
                ->select('eds.nombre', 'eds.id', DB::raw('SUM(abonos.valor) as total'))
                ->groupBy('eds.id', 'eds.nombre')
                ->orderByDesc('total')->limit(10)->get();

            return response()->json([
                'kpis' => [
                    'cartera' => [
                        'total' => $this->formatCompact($totalCartera),
                        'vencida' => $this->formatCompact($totalVencido),
                        'porc_vencida' => number_format($porcVencido, 1),
                        'facturas_vivas' => number_format($countFacturas, 0),
                        'ticket_promedio' => $this->formatCompact($ticketPromedio),
                        'dias_mora_pond' => number_format($diasMoraPonderado, 0),
                    ],
                    'recaudo' => [
                        'actual' => $this->formatCompact($totalRecaudo),
                        'variacion' => number_format(abs($variacionRecaudo), 1),
                        'trend' => $variacionRecaudo >= 0 ? 'up' : 'down'
                    ],
                    'riesgo' => [
                        'clientes_mora' => $clientesMora,
                        'clientes_total' => $totalClientes,
                        'porc_clientes_mora' => number_format($porcClientesMora, 1),
                        'criticos' => $clientesCriticos,
                        'concentracion' => number_format($concentracionTop5, 1)
                    ]
                ],
                'charts' => [
                    'aging' => ['labels' => array_keys($aging), 'data' => array_values($aging)],
                    'eds_riesgo' => $rankingEds,
                    'top_clientes' => [
                        'labels' => $topDeudores->pluck('razon_social'),
                        'data' => $topDeudores->pluck('deuda')
                    ],
                    'recaudo_eds' => ['labels' => $recaudoEds->pluck('nombre'), 'ids' => $recaudoEds->pluck('id'), 'data' => $recaudoEds->pluck('total')]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' Line: ' . $e->getLine()], 500);
        }
    }

    private function aplicarFiltroRangoMora($query, $rango)
    {
        $sqlDiff = "DATEDIFF(NOW(), facturas.fecha_vencimiento)";
        switch ($rango) {
            case 'Corriente':    $query->whereRaw("$sqlDiff <= 0"); break;
            case '1-7 Días':     $query->whereRaw("$sqlDiff BETWEEN 1 AND 7"); break;
            case '8-15 Días':    $query->whereRaw("$sqlDiff BETWEEN 8 AND 15"); break;
            case '16-22 Días':   $query->whereRaw("$sqlDiff BETWEEN 16 AND 22"); break;
            case '23-30 Días':   $query->whereRaw("$sqlDiff BETWEEN 23 AND 30"); break;
            case '31-60 Días':   $query->whereRaw("$sqlDiff BETWEEN 31 AND 60"); break;
            case '61-90 Días':   $query->whereRaw("$sqlDiff BETWEEN 61 AND 90"); break;
            case '91-120 Días':  $query->whereRaw("$sqlDiff BETWEEN 91 AND 120"); break;
            case '121-150 Días': $query->whereRaw("$sqlDiff BETWEEN 121 AND 150"); break;
            case '151-180 Días': $query->whereRaw("$sqlDiff BETWEEN 151 AND 180"); break;
            case '+180 Días':    $query->whereRaw("$sqlDiff > 180"); break;
        }
    }

    private function formatCompact($n)
    {
        $n = (float) $n;
        if ($n >= 1000000000) return '$' . number_format($n / 1000000000, 2, ',', '.') . ' MM';
        if ($n >= 1000000) return '$' . number_format($n / 1000000, 2, ',', '.') . ' M';
        if ($n >= 1000) return '$' . number_format($n / 1000, 1, ',', '.') . ' K';
        return '$' . number_format($n, 0, ',', '.');
    }
}
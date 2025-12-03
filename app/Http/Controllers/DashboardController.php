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
            $fecha_ini = $request->input('fecha_ini');
            $fecha_fin = $request->input('fecha_fin');

            // 1. SCOPES BASE
            $qCartera = Factura::query()
                ->where('facturas.saldo_pendiente', '>', 0)
                ->where('facturas.estado', '!=', 'anulada')
                ->when($eds_id, fn($q) => $q->where('facturas.eds_id', $eds_id));

            $qRecaudo = Abono::query()
                ->when($eds_id, fn($q) => $q->where('eds_id', $eds_id))
                ->when($fecha_ini, fn($q) => $q->whereDate('fecha', '>=', $fecha_ini))
                ->when($fecha_fin, fn($q) => $q->whereDate('fecha', '<=', $fecha_fin));
            
            // --- KPIs PRINCIPALES ---
            $totalCartera = (clone $qCartera)->sum('facturas.saldo_pendiente');
            $totalVencido = (clone $qCartera)->where('facturas.fecha_vencimiento', '<', now())->sum('facturas.saldo_pendiente');
            $totalRecaudo = (clone $qRecaudo)->sum('valor');
            
            $porcVencido = $totalCartera > 0 ? ($totalVencido / $totalCartera) * 100 : 0;

            // --- GRÁFICO 1: AGING (Cálculo en PHP para evitar error SQL 500) ---
            // Traemos solo lo necesario para iterar rápido
            $facturasAging = (clone $qCartera)->select('fecha_vencimiento', 'saldo_pendiente')->get();
            
            $aging = [
                'Corriente'  => 0,
                '1-30 Días'  => 0,
                '31-60 Días' => 0,
                '+60 Días'   => 0,
            ];

            $now = Carbon::now();

            foreach ($facturasAging as $f) {
                // diffInDays retorna positivo absoluto, ajustamos la lógica
                // Si vence en el futuro, es corriente. Si venció ayer, diff es 1.
                if ($f->fecha_vencimiento > $now) {
                    $aging['Corriente'] += $f->saldo_pendiente;
                } else {
                    $dias = $now->diffInDays($f->fecha_vencimiento);
                    if ($dias <= 30) $aging['1-30 Días'] += $f->saldo_pendiente;
                    elseif ($dias <= 60) $aging['31-60 Días'] += $f->saldo_pendiente;
                    else $aging['+60 Días'] += $f->saldo_pendiente;
                }
            }

            // --- GRÁFICO 2: TOP 5 CLIENTES ---
            $topClientes = (clone $qCartera)
                ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
                ->select('clientes.razon_social', DB::raw('SUM(facturas.saldo_pendiente) as deuda'))
                ->groupBy('clientes.id', 'clientes.razon_social')
                ->orderByDesc('deuda')
                ->limit(5)
                ->get();

            // --- GRÁFICO 3: POR EDS ---
            $deudaEds = (clone $qCartera)
                ->join('eds', 'facturas.eds_id', '=', 'eds.id')
                ->select('eds.nombre', DB::raw('SUM(facturas.saldo_pendiente) as deuda'))
                ->groupBy('eds.id', 'eds.nombre')
                ->orderByDesc('deuda')
                ->get();

            return response()->json([
                'kpis' => [
                    'total_cartera' => number_format($totalCartera, 0, ',', '.'),
                    'total_vencido' => number_format($totalVencido, 0, ',', '.'),
                    'porc_vencido'  => number_format($porcVencido, 1),
                    'total_recaudo' => number_format($totalRecaudo, 0, ',', '.'),
                ],
                'charts' => [
                    'aging' => [
                        'labels' => array_keys($aging),
                        'data'   => array_values($aging),
                    ],
                    'top_clientes' => [
                        'labels' => $topClientes->pluck('razon_social'),
                        'data'   => $topClientes->pluck('deuda'),
                    ],
                    'eds' => [
                        'labels' => $deudaEds->pluck('nombre'),
                        'data'   => $deudaEds->pluck('deuda'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            // Si falla, devolvemos el error en JSON para verlo en la consola del navegador
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }
}
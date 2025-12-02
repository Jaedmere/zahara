<?php

namespace App\Http\Controllers;

use App\Models\EDS;
use App\Models\Factura;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarteraEdsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        // 1. CONSULTA BASE AGRUPADA (EDS + CLIENTE)
        $query = Factura::query()
            ->join('eds', 'facturas.eds_id', '=', 'eds.id')
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('facturas.saldo_pendiente', '>', 0)
            ->where('facturas.estado', '!=', 'anulada')
            ->selectRaw('
                facturas.eds_id,
                facturas.cliente_id,
                eds.nombre as eds_nombre,
                eds.codigo as eds_codigo,
                clientes.razon_social as cliente_nombre,
                clientes.documento as cliente_documento,
                COUNT(facturas.id) as cuentas_activas,
                SUM(facturas.saldo_pendiente) as total_deuda,
                CAST(MAX(DATEDIFF(NOW(), facturas.fecha_vencimiento)) AS SIGNED) as max_dias_mora
            ')
            ->groupBy('facturas.eds_id', 'facturas.cliente_id', 'eds.nombre', 'eds.codigo', 'clientes.razon_social', 'clientes.documento');

        // 2. BUSCADOR
        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('eds.nombre', 'like', "%{$search}%")
                  ->orWhere('clientes.razon_social', 'like', "%{$search}%")
                  ->orWhere('clientes.documento', 'like', "%{$search}%");
            });
        }

        // 3. GRAN TOTAL
        $grand_total_query = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query->getQuery());
        $grand_total = $grand_total_query->sum('total_deuda');

        // 4. PAGINACIÓN
        $items = $query->orderBy('eds.nombre')
                       ->orderBy('clientes.razon_social')
                       ->paginate(15)
                       ->withQueryString();

        if ($request->ajax()) {
            return view('cartera_eds.partials.table', compact('items', 'grand_total'))->render();
        }

        return view('cartera_eds.index', compact('items', 'grand_total'));
    }

    // --- API PARA EL MODAL (DETALLE DE LA PAREJA) ---
    public function detallePar(Request $request, $eds, $cliente)
    {
        $query = Factura::query()
            ->where('eds_id', $eds)
            ->where('cliente_id', $cliente)
            ->pendientes()
            ->with('cliente:id,razon_social'); 

        // Filtros internos del modal
        if ($request->filled('q_factura')) {
            $query->where('consecutivo', 'like', '%' . $request->q_factura . '%');
        }
        
        if ($request->filled('corte_desde')) {
            $query->whereDate('corte_desde', '>=', $request->corte_desde);
        }
        if ($request->filled('corte_hasta')) {
            $query->whereDate('corte_desde', '<=', $request->corte_hasta);
        }

        $totalDeuda = (clone $query)->sum('saldo_pendiente');
        
        $pendientes = $query->orderBy('fecha_vencimiento', 'asc')
                            ->paginate($request->input('per_page', 50));

        $data = $pendientes->getCollection()->map(function($f) {
            $dias = \Carbon\Carbon::now()->diffInDays($f->fecha_vencimiento, false) * -1;
            return [
                'id' => $f->id,
                'consecutivo' => $f->consecutivo,
                'prefijo' => $f->prefijo,
                'fecha_vencimiento' => $f->fecha_vencimiento->format('Y-m-d'),
                'saldo_pendiente' => $f->saldo_pendiente,
                'cliente_nombre' => $f->cliente->razon_social,
                'corte_desde' => $f->corte_desde ? $f->corte_desde->format('Y-m-d') : 'N/A',
                'corte_hasta' => $f->corte_hasta ? $f->corte_hasta->format('Y-m-d') : 'N/A',
                'valor_total' => $f->valor_total,
                'descuento' => $f->descuento,
                'abonos_previos' => $f->valor_total - $f->saldo_pendiente,
                'dias_vencidos' => intval($dias),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $pendientes->currentPage(),
                'last_page'    => $pendientes->lastPage(),
                'total'        => $pendientes->total(),
                'total_deuda'  => $totalDeuda
            ]
        ]);
    }

    // --- EXPORTAR CSV GENERAL ---
    public function export(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        $search = $request->string('search')->trim()->toString();
        if ($search === 'null') $search = '';
        
        $fileName = 'consolidado_eds_' . date('Y-m-d_H-i') . '.csv';

        // Consulta agrupada idéntica al index pero para stream
        $query = Factura::query()
            ->join('eds', 'facturas.eds_id', '=', 'eds.id')
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('facturas.saldo_pendiente', '>', 0)
            ->where('facturas.estado', '!=', 'anulada')
            ->selectRaw('
                eds.nombre as eds_nombre,
                eds.codigo as eds_codigo,
                clientes.razon_social as cliente_nombre,
                clientes.documento as cliente_documento,
                COUNT(facturas.id) as cuentas_activas,
                SUM(facturas.saldo_pendiente) as total_deuda,
                CAST(MAX(DATEDIFF(NOW(), facturas.fecha_vencimiento)) AS SIGNED) as max_dias_mora
            ')
            ->groupBy('facturas.eds_id', 'facturas.cliente_id', 'eds.nombre', 'eds.codigo', 'clientes.razon_social', 'clientes.documento')
            ->orderBy('eds.nombre');

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('eds.nombre', 'like', "%{$search}%")
                  ->orWhere('clientes.razon_social', 'like', "%{$search}%");
            });
        }

        return response()->streamDownload(function() use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            
            // ENCABEZADOS (Incluye Mora Máxima)
            fputcsv($out, ['Estación', 'Código EDS', 'Cliente', 'NIT/CC', 'Cuentas', 'Mora Máxima', 'Total Deuda'], ';');
            
            foreach ($query->cursor() as $row) {
                // Validación visual para no mostrar días negativos
                $mora = $row->max_dias_mora > 0 ? $row->max_dias_mora : 0;

                fputcsv($out, [
                    $row->eds_nombre, 
                    $row->eds_codigo, 
                    $row->cliente_nombre, 
                    $row->cliente_documento, 
                    $row->cuentas_activas,
                    $mora, // Columna nueva
                    number_format($row->total_deuda, 2, ',', '')
                ], ';');
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // --- EXPORTAR DETALLE PAREJA (Desde Modal) ---
    public function exportarPar(Request $request, $eds, $cliente)
    {
        set_time_limit(300);
        
        $edsModel = EDS::find($eds);
        $clienteModel = Cliente::find($cliente);
        
        if(!$edsModel || !$clienteModel) abort(404);

        $fileName = 'estado_cuenta_' . \Str::slug($edsModel->nombre . '_' . $clienteModel->razon_social) . '.csv';
        $delimiter = ';';

        $query = Factura::query()
            ->where('eds_id', $eds)
            ->where('cliente_id', $cliente)
            ->pendientes();

        if ($request->filled('q_factura')) $query->where('consecutivo', 'like', '%' . $request->q_factura . '%');
        if ($request->filled('corte_desde')) $query->whereDate('corte_desde', '>=', $request->corte_desde);
        if ($request->filled('corte_hasta')) $query->whereDate('corte_desde', '<=', $request->corte_hasta);
        
        $query->orderBy('fecha_vencimiento');

        return response()->streamDownload(function() use ($query, $edsModel, $clienteModel, $delimiter) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            
            fputcsv($out, ['EDS', 'Cliente', 'NIT/CC', 'Cuenta', 'Corte Desde', 'Corte Hasta', 'Vencimiento', 'Días Vencidos', 'Valor Original', 'Descuento', 'Abonos Previos', 'Saldo Pendiente'], $delimiter);
            
            $now = now();
            $query->chunk(500, function($facturas) use ($out, $edsModel, $clienteModel, $delimiter, $now) {
                foreach ($facturas as $f) {
                    $dias = $now->diffInDays($f->fecha_vencimiento, false) * -1;
                    fputcsv($out, [
                        $edsModel->nombre,
                        $clienteModel->razon_social,
                        $clienteModel->documento,
                        $f->consecutivo,
                        $f->corte_desde->format('Y-m-d'),
                        $f->corte_hasta->format('Y-m-d'),
                        $f->fecha_vencimiento->format('Y-m-d'),
                        intval($dias),
                        number_format($f->valor_total, 2, ',', ''),
                        number_format($f->descuento, 2, ',', ''),
                        number_format($f->valor_total - $f->saldo_pendiente, 2, ',', ''),
                        number_format($f->saldo_pendiente, 2, ',', '')
                    ], $delimiter);
                }
                flush();
            });
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
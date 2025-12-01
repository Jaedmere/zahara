<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class CarteraController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $clientesQuery = Cliente::query()
            ->whereHas('facturas', function ($q) {
                $q->where('saldo_pendiente', '>', 0)
                  ->where('estado', '!=', 'anulada');
            });

        if ($search !== '') {
            $clientesQuery->where(function($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%");
            });
        }

        $grand_total = Factura::query()
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', '!=', 'anulada')
            ->whereIn('cliente_id', (clone $clientesQuery)->select('id'))
            ->sum('saldo_pendiente');

        $clientes = $clientesQuery
            ->select('clientes.*')
            ->withCount(['facturas as cuentas_activas' => function ($q) {
                $q->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
            }])
            ->withSum(['facturas as total_deuda' => function ($q) {
                $q->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
            }], 'saldo_pendiente')
            ->addSelect(['max_dias_mora' => function ($query) {
                $query->selectRaw('CAST(MAX(DATEDIFF(NOW(), fecha_vencimiento)) AS SIGNED)')
                      ->from('facturas')
                      ->whereColumn('cliente_id', 'clientes.id')
                      ->where('saldo_pendiente', '>', 0)
                      ->where('estado', '!=', 'anulada');
            }])
            ->orderByDesc('total_deuda')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('cartera.partials.table', compact('clientes', 'grand_total'))->render();
        }

        return view('cartera.index', compact('clientes', 'grand_total'));
    }

    public function export(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $search = $request->string('search')->trim()->toString();
        $search = ($search === 'null' || $search === 'undefined') ? '' : $search;

        $fileName = 'reporte_cartera_' . date('Y-m-d_H-i') . '.csv';
        $delimiter = ';';

        $query = Cliente::query()
            ->whereHas('facturas', function ($q) {
                $q->where('saldo_pendiente', '>', 0)
                  ->where('estado', '!=', 'anulada');
            });

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%");
            });
        }

        $query->select('clientes.*')
            ->withCount(['facturas as cuentas_activas' => function ($q) {
                $q->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
            }])
            ->withSum(['facturas as total_deuda' => function ($q) {
                $q->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
            }], 'saldo_pendiente')
            ->addSelect(['max_dias_mora' => function ($query) {
                $query->selectRaw('CAST(MAX(DATEDIFF(NOW(), fecha_vencimiento)) AS SIGNED)')
                      ->from('facturas')
                      ->whereColumn('cliente_id', 'clientes.id')
                      ->where('saldo_pendiente', '>', 0)
                      ->where('estado', '!=', 'anulada');
            }])
            ->orderByDesc('total_deuda');

        return response()->streamDownload(function() use ($query, $delimiter) {
            if (ob_get_level()) ob_end_clean();
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Cliente', 'NIT/Documento', 'Teléfono', 'Email', 'Cuentas Pendientes', 'Días Mora Máxima', 'Deuda Total'], $delimiter);

            $query->chunk(500, function($clientes) use ($out, $delimiter) {
                foreach ($clientes as $c) {
                    fputcsv($out, [
                        $c->razon_social,
                        $c->tipo_id . ' ' . $c->documento,
                        $c->telefono,
                        $c->email,
                        $c->cuentas_activas,
                        intval($c->max_dias_mora > 0 ? $c->max_dias_mora : 0),
                        number_format($c->total_deuda, 2, ',', ''),
                    ], $delimiter);
                }
                flush();
            });
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, no-cache']);
    }
    
    // --- NUEVO MÉTODO: EXPORTAR DETALLE DE UN CLIENTE ---
    public function exportarCliente(Request $request, Cliente $cliente)
    {
        set_time_limit(300);
        $fileName = 'estado_cuenta_' . \Str::slug($cliente->razon_social) . '_' . date('Y-m-d') . '.csv';
        $delimiter = ';';

        $query = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->pendientes()
            ->with('eds:id,nombre');

        // Aplicamos los mismos filtros del modal
        if ($request->filled('eds_id')) $query->where('eds_id', $request->eds_id);
        if ($request->filled('q_factura')) $query->where('consecutivo', 'like', '%' . $request->q_factura . '%');
        if ($request->filled('corte_desde')) $query->whereDate('corte_desde', '>=', $request->corte_desde);
        if ($request->filled('corte_hasta')) $query->whereDate('corte_desde', '<=', $request->corte_hasta);
        
        $query->orderBy('fecha_vencimiento', 'asc');

        return response()->streamDownload(function() use ($query, $delimiter, $cliente) {
            if (ob_get_level()) ob_end_clean();
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            // COLUMNA CLIENTE PRIMERO, LUEGO DIAS VENCIDOS
            fputcsv($out, [
                'Cliente', 'NIT/CC', 'EDS', 'Cuenta', 'Prefijo', 
                'Corte Desde', 'Corte Hasta', 'Vencimiento', 
                'Días Vencidos', // <-- Nueva columna
                'Valor Original', 'Descuento', 'Abonos Previos', 'Saldo Pendiente'
            ], $delimiter);

            $query->chunk(500, function($facturas) use ($out, $delimiter, $cliente) {
                $now = now();
                foreach ($facturas as $f) {
                    // Cálculo de días
                    $dias = $now->diffInDays($f->fecha_vencimiento, false) * -1;
                    
                    fputcsv($out, [
                        $cliente->razon_social,
                        $cliente->documento,
                        $f->eds->nombre,
                        $f->consecutivo,
                        $f->prefijo,
                        $f->corte_desde ? $f->corte_desde->format('Y-m-d') : '',
                        $f->corte_hasta ? $f->corte_hasta->format('Y-m-d') : '',
                        $f->fecha_vencimiento->format('Y-m-d'),
                        intval(round($dias)), // Días como entero
                        number_format($f->valor_total, 2, ',', ''),
                        number_format($f->descuento, 2, ',', ''),
                        number_format($f->valor_total - $f->saldo_pendiente, 2, ',', ''),
                        number_format($f->saldo_pendiente, 2, ',', '')
                    ], $delimiter);
                }
                flush();
            });
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, no-cache']);
    }
}
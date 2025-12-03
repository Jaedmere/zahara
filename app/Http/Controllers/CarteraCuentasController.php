<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarteraCuentasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        
        // Filtros avanzados
        $eds_id = $request->input('eds_id');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        // Lista para filtros
        $eds_list = EDS::select('id', 'nombre')->where('activo', true)->orderBy('nombre')->get();

        // CONSULTA BASE
        $query = Factura::query()
            ->with(['cliente:id,razon_social', 'eds:id,nombre,codigo'])
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', '!=', 'anulada');

        // Filtros
        $query->when($eds_id, fn($q) => $q->where('eds_id', $eds_id))
              ->when($fecha_desde, fn($q) => $q->whereDate('corte_desde', '>=', $fecha_desde))
              ->when($fecha_hasta, fn($q) => $q->whereDate('corte_desde', '<=', $fecha_hasta));

        // Buscador General
        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function($sub) use ($search) {
                $sub->where('consecutivo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('razon_social', 'like', "%{$search}%")->orWhere('documento', 'like', "%{$search}%"))
                    ->orWhereHas('eds', fn($e) => $e->where('nombre', 'like', "%{$search}%"));
            });
        });

        // GRAN TOTAL
        $grand_total = $query->sum('saldo_pendiente');

        // PAGINACIÓN (Calculamos días vencidos en SQL para ordenar si quisieramos, o en la vista)
        // Agregamos cálculo de días para ordenamiento o visualización rápida
        $query->addSelect(['dias_mora_calc' => function ($q) {
            $q->selectRaw('CAST(DATEDIFF(NOW(), fecha_vencimiento) AS SIGNED)');
        }]);

        $items = $query->orderBy('fecha_vencimiento', 'asc')
                       ->paginate(15)
                       ->withQueryString();

        if ($request->ajax() || $request->input('ajax')) {
            return view('cartera_cuentas.partials.table', compact('items', 'grand_total'))->render();
        }

        return view('cartera_cuentas.index', compact('items', 'grand_total', 'eds_list'));
    }

    // API MODAL (Una sola factura, pero formato lista para compatibilidad JS)
    public function detalleCuenta(Request $request, Factura $factura)
    {
        // Aunque es una sola, la devolvemos como colección para que el JS 'cartera.forEach' funcione igual
        // Recalculamos días al vuelo
        $dias = Carbon::now()->diffInDays($factura->fecha_vencimiento, false) * -1;
        
        $data = [[
            'id' => $factura->id,
            'consecutivo' => $factura->consecutivo,
            'prefijo' => $factura->prefijo,
            'cliente_nombre' => $factura->cliente->razon_social,
            'eds_nombre' => $factura->eds->nombre,
            'fecha_vencimiento' => $factura->fecha_vencimiento->format('Y-m-d'),
            'saldo_pendiente' => $factura->saldo_pendiente,
            'corte_desde' => $factura->corte_desde->format('Y-m-d'),
            'corte_hasta' => $factura->corte_hasta->format('Y-m-d'),
            'valor_total' => $factura->valor_total,
            'descuento' => $factura->descuento,
            'abonos_previos' => $factura->valor_total - $factura->saldo_pendiente,
            'dias_vencidos' => intval($dias),
        ]];

        return response()->json([
            'data' => $data,
            'meta' => [
                'total_deuda' => $factura->saldo_pendiente,
                'cliente_id' => $factura->cliente_id // Dato extra para el hidden input del form
            ]
        ]);
    }

    // EXPORTAR GENERAL
    public function export(Request $request)
    {
        set_time_limit(300); ini_set('memory_limit', '512M');
        $search = $request->string('search')->trim()->toString();
        if ($search === 'null') $search = '';
        $fileName = 'consolidado_cuentas_' . date('Y-m-d_H-i') . '.csv';

        $query = Factura::query()
            ->with(['cliente:id,razon_social,documento', 'eds:id,nombre'])
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', '!=', 'anulada');

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('consecutivo', 'like', "%{$search}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('razon_social', 'like', "%{$search}%"));
            });
        }
        
        // Filtros adicionales si vienen en el request
        if ($request->filled('eds_id')) $query->where('eds_id', $request->eds_id);
        
        $query->orderBy('fecha_vencimiento', 'asc');

        return response()->streamDownload(function() use ($query) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Cuenta', 'EDS', 'Cliente', 'NIT/CC', 'Corte', 'Vencimiento', 'Días Mora', 'Saldo Pendiente'], ';');
            
            foreach ($query->cursor() as $f) {
                $dias = Carbon::now()->diffInDays($f->fecha_vencimiento, false) * -1;
                fputcsv($out, [
                    $f->consecutivo,
                    $f->eds->nombre,
                    $f->cliente->razon_social,
                    $f->cliente->documento,
                    $f->corte_desde->format('d/m/Y') . ' - ' . $f->corte_hasta->format('d/m/Y'),
                    $f->fecha_vencimiento->format('d/m/Y'),
                    intval($dias),
                    number_format($f->saldo_pendiente, 2, ',', '')
                ], ';');
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // EXPORTAR INDIVIDUAL (Desde Modal)
    public function exportarCuenta(Request $request, Factura $factura)
    {
        $fileName = 'cuenta_' . $factura->consecutivo . '.csv';
        return response()->streamDownload(function() use ($factura) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Cuenta', 'EDS', 'Cliente', 'Corte', 'Vencimiento', 'Días Mora', 'Valor Original', 'Abonos', 'Saldo'], ';');
            
            $dias = Carbon::now()->diffInDays($factura->fecha_vencimiento, false) * -1;
            fputcsv($out, [
                $factura->consecutivo,
                $factura->eds->nombre,
                $factura->cliente->razon_social,
                $factura->corte_desde->format('d/m/Y') . ' - ' . $factura->corte_hasta->format('d/m/Y'),
                $factura->fecha_vencimiento->format('d/m/Y'),
                intval($dias),
                number_format($factura->valor_total, 2, ',', ''),
                number_format($factura->valor_total - $factura->saldo_pendiente, 2, ',', ''),
                number_format($factura->saldo_pendiente, 2, ',', '')
            ], ';');
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
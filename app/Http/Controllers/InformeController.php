<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeController extends Controller
{
    public function aging(Request $r) {
        $query = DB::table('facturas as f')
            ->selectRaw("f.eds_id, f.cliente_id,
                SUM(CASE WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) <= 0 THEN f.total - IFNULL(ap.aplicado,0) ELSE 0 END) as d0,
                SUM(CASE WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) BETWEEN 1 AND 30 THEN f.total - IFNULL(ap.aplicado,0) ELSE 0 END) as d30,
                SUM(CASE WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) BETWEEN 31 AND 60 THEN f.total - IFNULL(ap.aplicado,0) ELSE 0 END) as d60,
                SUM(CASE WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) BETWEEN 61 AND 90 THEN f.total - IFNULL(ap.aplicado,0) ELSE 0 END) as d90,
                SUM(CASE WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 90 THEN f.total - IFNULL(ap.aplicado,0) ELSE 0 END) as dmas")
            ->leftJoin(DB::raw("(select factura_id, sum(valor_aplicado) as aplicado from abono_detalle group by factura_id) ap"), 'ap.factura_id','=','f.id')
            ->whereNull('f.deleted_at')
            ->groupBy('f.eds_id','f.cliente_id');

        if ($r->filled('eds_id')) $query->where('f.eds_id', $r->integer('eds_id'));
        if ($r->filled('cliente_id')) $query->where('f.cliente_id', $r->integer('cliente_id'));

        $rows = $query->get();
        return view('informes.aging', compact('rows'));
    }
}

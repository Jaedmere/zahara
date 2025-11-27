<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $estado = $request->input('estado', 'pendientes');
        
        $eds_id = $request->input('eds_id');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        $eds_list = EDS::select('id', 'nombre')->orderBy('nombre')->get();

        $query = Factura::query()
            ->with(['cliente:id,razon_social', 'eds:id,nombre']); 

        if ($estado === 'anuladas' || $estado === 'todas') {
            $query->withTrashed();
        }

        $facturas = $query
            ->when($estado === 'pendientes', fn($q) => $q->pendientes())
            ->when($estado === 'pagadas', fn($q) => $q->where('estado', 'pagada'))
            ->when($estado === 'anuladas', fn($q) => $q->where('estado', 'anulada'))
            ->when($eds_id, fn($q) => $q->where('eds_id', $eds_id))
            ->when($fecha_desde, fn($q) => $q->whereDate('fecha_emision', '>=', $fecha_desde))
            ->when($fecha_hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $fecha_hasta))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('consecutivo', 'like', "%{$search}%")
                        ->orWhereHas('cliente', fn($c) => $c->where('razon_social', 'like', "%{$search}%"));
                });
            })
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('facturas.partials.table', compact('facturas'))->render();
        }

        return view('facturas.index', compact('facturas', 'eds_list'));
    }

    public function create()
    {
        $clientes = Cliente::where('estado', 'activo')->select('id', 'razon_social', 'documento')->orderBy('razon_social')->get();
        $eds = EDS::where('activo', true)->select('id', 'nombre')->orderBy('nombre')->get();
        return view('facturas.create', compact('clientes', 'eds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'eds_id'            => 'required|exists:eds,id',
            'cliente_id'        => 'required|exists:clientes,id',
            'prefijo'           => 'nullable|string|max:10',
            'consecutivo'       => [
                'required', 'string', 'max:20',
                Rule::unique('facturas')->where(fn ($q) => $q->where('eds_id', $request->eds_id)->where('prefijo', $request->prefijo))
            ],
            'fecha_emision'     => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
            
            // --- NUEVOS CAMPOS: CORTE ---
            'corte_desde'       => 'required|date',
            'corte_hasta'       => 'required|date|after_or_equal:corte_desde',

            'valor_neto'        => 'required|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0',
            'notas'             => 'nullable|string'
        ]);

        $descuento = $data['descuento'] ?? 0;
        $total = $data['valor_neto'] - $descuento;

        Factura::create([
            'eds_id'            => $data['eds_id'],
            'cliente_id'        => $data['cliente_id'],
            'prefijo'           => $data['prefijo'],
            'consecutivo'       => $data['consecutivo'],
            'fecha_emision'     => $data['fecha_emision'],
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'corte_desde'       => $data['corte_desde'],
            'corte_hasta'       => $data['corte_hasta'],
            'valor_neto'        => $data['valor_neto'],
            'descuento'         => $descuento,
            'valor_total'       => $total,
            'saldo_pendiente'   => $total, 
            'estado'            => 'pendiente',
            'notas'             => $data['notas']
        ]);

        return redirect()->route('facturas.index')->with('ok', 'Cuenta registrada correctamente.');
    }

    public function show(Factura $factura)
    {
        return redirect()->route('facturas.edit', $factura);
    }

    public function edit(Factura $factura)
    {
         if ($factura->estado === 'anulada') {
            return back()->withErrors(['msg' => 'No se puede editar una cuenta anulada.']);
        }
        $clientes = Cliente::where('estado', 'activo')->select('id', 'razon_social')->orderBy('razon_social')->get();
        $eds = EDS::where('activo', true)->select('id', 'nombre')->orderBy('nombre')->get();
        return view('facturas.edit', compact('factura', 'clientes', 'eds'));
    }

    public function update(Request $request, Factura $factura)
    {
        $tienePagos = $factura->saldo_pendiente < $factura->valor_total;

        $rules = [
            'fecha_emision'     => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
            'corte_desde'       => 'required|date',
            'corte_hasta'       => 'required|date|after_or_equal:corte_desde',
            'notas'             => 'nullable|string'
        ];

        if (!$tienePagos) {
            $rules['eds_id']      = 'required|exists:eds,id';
            $rules['cliente_id']  = 'required|exists:clientes,id';
            $rules['prefijo']     = 'nullable|string|max:10';
            $rules['consecutivo'] = ['required', 'string', 'max:20', Rule::unique('facturas')->ignore($factura->id)->where(fn ($q) => $q->where('eds_id', $request->input('eds_id', $factura->eds_id))->where('prefijo', $request->input('prefijo', $factura->prefijo)))];
            $rules['valor_neto']  = 'required|numeric|min:0';
            $rules['descuento']   = 'nullable|numeric|min:0';
        }

        $data = $request->validate($rules);

        if ($tienePagos) {
            $factura->update([
                'fecha_emision'     => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'corte_desde'       => $data['corte_desde'],
                'corte_hasta'       => $data['corte_hasta'],
                'notas'             => $data['notas']
            ]);
            $msg = 'Cuenta actualizada (Montos protegidos).';
        } else {
            $total = $data['valor_neto'] - ($data['descuento'] ?? 0);
            $factura->update([
                'eds_id'            => $data['eds_id'],
                'cliente_id'        => $data['cliente_id'],
                'prefijo'           => $data['prefijo'],
                'consecutivo'       => $data['consecutivo'],
                'fecha_emision'     => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'corte_desde'       => $data['corte_desde'],
                'corte_hasta'       => $data['corte_hasta'],
                'valor_neto'        => $data['valor_neto'],
                'descuento'         => $data['descuento'] ?? 0,
                'valor_total'       => $total,
                'saldo_pendiente'   => $total,
                'notas'             => $data['notas']
            ]);
            $msg = 'Cuenta actualizada correctamente.';
        }

        return redirect()->route('facturas.index')->with('ok', $msg);
    }

    public function destroy(Factura $factura)
    {
         if ($factura->valor_total != $factura->saldo_pendiente) {
            return back()->withErrors(['msg' => 'No se puede anular una cuenta con abonos. Anule los abonos primero.']);
        }
        $factura->update(['estado' => 'anulada', 'saldo_pendiente' => 0]);
        $factura->delete(); 
        return back()->with('ok', 'Cuenta anulada.');
    }

    public function export(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $search = $request->string('search')->trim()->toString();
        $search = ($search === 'null' || $search === 'undefined') ? '' : $search;
        $estado = $request->input('estado');
        $estado = ($estado === 'null' || $estado === 'undefined' || !$estado) ? 'pendientes' : $estado;
        $eds_id = $request->input('eds_id');
        $eds_id = ($eds_id === 'null' || $eds_id === 'undefined') ? null : $eds_id;
        $fecha_desde = $request->input('fecha_desde');
        $fecha_desde = ($fecha_desde === 'null' || $fecha_desde === 'undefined') ? null : $fecha_desde;
        $fecha_hasta = $request->input('fecha_hasta');
        $fecha_hasta = ($fecha_hasta === 'null' || $fecha_hasta === 'undefined') ? null : $fecha_hasta;

        $query = Factura::query()->with(['cliente:id,razon_social,documento', 'eds:id,nombre']); 

        if ($estado === 'anuladas' || $estado === 'todas') { $query->withTrashed(); }

        $query->when($estado === 'pendientes', fn($q) => $q->pendientes())
              ->when($estado === 'pagadas', fn($q) => $q->where('estado', 'pagada'))
              ->when($estado === 'anuladas', fn($q) => $q->where('estado', 'anulada'))
              ->when($eds_id, fn($q) => $q->where('eds_id', $eds_id))
              ->when($fecha_desde, fn($q) => $q->whereDate('fecha_emision', '>=', $fecha_desde))
              ->when($fecha_hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $fecha_hasta))
              ->when($search !== '', function ($q) use ($search) {
                  $q->where(function($sub) use ($search) {
                      $sub->where('consecutivo', 'like', "%{$search}%")
                          ->orWhereHas('cliente', fn($c) => $c->where('razon_social', 'like', "%{$search}%"));
                  });
              })
              ->orderBy('fecha_emision', 'desc');

        $fileName  = 'reporte_cuentas_' . date('Y-m-d_H-i') . '.csv';
        $delimiter = ';';

        return response()->streamDownload(function() use ($query, $delimiter) {
            if (ob_get_level()) ob_end_clean();
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Consecutivo', 'Prefijo', 'Cliente', 'NIT/CC', 'EDS', 'Fecha Emisión', 'Corte Desde', 'Corte Hasta', 'Vencimiento', 'Total', 'Saldo Pendiente', 'Estado'], $delimiter);

            $query->chunk(500, function($facturas) use ($out, $delimiter) {
                foreach ($facturas as $f) {
                    fputcsv($out, [
                        $f->consecutivo,
                        $f->prefijo,
                        $f->cliente->razon_social,
                        $f->cliente->documento,
                        $f->eds->nombre,
                        $f->fecha_emision->format('Y-m-d'),
                        $f->corte_desde->format('Y-m-d'), // NUEVO EXPORT
                        $f->corte_hasta->format('Y-m-d'), // NUEVO EXPORT
                        $f->fecha_vencimiento->format('Y-m-d'),
                        number_format($f->valor_total, 2, ',', ''),
                        number_format($f->saldo_pendiente, 2, ',', ''),
                        ucfirst($f->estado)
                    ], $delimiter);
                }
                flush(); 
            });
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, no-cache', 'X-Accel-Buffering' => 'no']);
    }
}
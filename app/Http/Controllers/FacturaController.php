<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function index(Request $r) {
        $q = Factura::with(['cliente','eds']);
        if ($r->filled('estado')) $q->where('estado',$r->string('estado'));
        if ($r->filled('cliente')) $q->where('cliente_id',$r->integer('cliente'));
        if ($r->filled('eds')) $q->where('eds_id',$r->integer('eds'));
        if ($r->filled('search')) {
            $s = '%'.$r->string('search')->toString().'%';
            $q->where(function($qq) use ($s) {
                $qq->where('prefijo','like',$s)->orWhere('consecutivo','like',$s);
            });
        }
        $facturas = $q->orderByDesc('fecha_emision')->paginate(15);
        return view('facturas.index', compact('facturas'));
    }

    public function create() {
        $clientes = Cliente::orderBy('razon_social')->get();
        $eds = EDS::orderBy('nombre')->get();
        return view('facturas.create', compact('clientes','eds'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'prefijo'=>'nullable|string',
            'consecutivo'=>'required|integer|min:1',
            'cliente_id'=>'required|exists:clientes,id',
            'eds_id'=>'required|exists:eds,id',
            'fecha_emision'=>'required|date',
            'fecha_vencimiento'=>'required|date|after_or_equal:fecha_emision',
            'subtotal'=>'required|numeric|min:0',
            'iva'=>'required|numeric|min:0',
            'retenciones'=>'required|numeric|min:0',
            'total'=>'required|numeric|min:0',
            'notas'=>'nullable|string'
        ]);

        if (round($data['subtotal'] + $data['iva'] - $data['retenciones'], 2) != round($data['total'], 2)) {
            return back()->withErrors(['total'=>'Subtotal + IVA - Retenciones debe igualar Total'])->withInput();
        }

        DB::transaction(function() use ($data) {
            Factura::create($data);
        });

        return redirect()->route('facturas.index')->with('ok','Factura creada');
    }

    public function edit(Factura $factura) {
        $clientes = Cliente::orderBy('razon_social')->get();
        $eds = EDS::orderBy('nombre')->get();
        return view('facturas.edit', compact('factura','clientes','eds'));
    }

    public function update(Request $r, Factura $factura) {
        $data = $r->validate([
            'prefijo'=>'nullable|string',
            'consecutivo'=>'required|integer|min:1',
            'cliente_id'=>'required|exists:clientes,id',
            'eds_id'=>'required|exists:eds,id',
            'fecha_emision'=>'required|date',
            'fecha_vencimiento'=>'required|date|after_or_equal:fecha_emision',
            'subtotal'=>'required|numeric|min:0',
            'iva'=>'required|numeric|min:0',
            'retenciones'=>'required|numeric|min:0',
            'total'=>'required|numeric|min:0',
            'notas'=>'nullable|string'
        ]);

        if (round($data['subtotal'] + $data['iva'] - $data['retenciones'], 2) != round($data['total'], 2)) {
            return back()->withErrors(['total'=>'Subtotal + IVA - Retenciones debe igualar Total'])->withInput();
        }

        $factura->update($data);
        return redirect()->route('facturas.index')->with('ok','Factura actualizada');
    }

    public function destroy(Factura $factura) {
        if ($factura->detallesAbono()->exists()) {
            return back()->withErrors(['msg'=>'No puede eliminar una factura con abonos. Reversa primero.']);
        }
        $factura->delete();
        return back()->with('ok','Factura eliminada');
    }
}

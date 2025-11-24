<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\AbonoDetalle;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbonoController extends Controller
{
    public function index(Request $r) {
        $q = Abono::with(['cliente','eds']);
        if ($r->filled('cliente')) $q->where('cliente_id',$r->integer('cliente'));
        if ($r->filled('eds')) $q->where('eds_id',$r->integer('eds'));
        if ($r->filled('search')) {
            $s = '%'.$r->string('search')->toString().'%';
            $q->where('referencia_bancaria','like',$s);
        }
        $abonos = $q->orderByDesc('fecha')->paginate(15);
        return view('abonos.index', compact('abonos'));
    }

    public function create() {
        $clientes = Cliente::orderBy('razon_social')->get();
        $eds = EDS::orderBy('nombre')->get();
        return view('abonos.create', compact('clientes','eds'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'cliente_id'=>'required|exists:clientes,id',
            'eds_id'=>'required|exists:eds,id',
            'fecha'=>'required|date',
            'valor'=>'required|numeric|min:0.01',
            'medio_pago'=>'nullable|string',
            'referencia_bancaria'=>'nullable|string',
            'banco'=>'nullable|string',
            'descuento'=>'nullable|numeric|min:0',
            'aplicacion'=>'required|in:fifo,manual',
            'detalles'=>'nullable|array'
        ]);

        DB::transaction(function() use ($data) {
            $abono = Abono::create([
                'cliente_id'=>$data['cliente_id'],
                'eds_id'=>$data['eds_id'],
                'fecha'=>$data['fecha'],
                'valor'=>$data['valor'],
                'medio_pago'=>$data['medio_pago'] ?? null,
                'referencia_bancaria'=>$data['referencia_bancaria'] ?? null,
                'banco'=>$data['banco'] ?? null,
                'descuento'=>$data['descuento'] ?? 0,
            ]);

            $montoRestante = $abono->valor;

            if ($data['aplicacion'] === 'fifo') {
                $facturas = Factura::where('cliente_id',$abono->cliente_id)
                    ->whereIn('estado',['pendiente','parcial'])
                    ->orderBy('fecha_emision')->orderBy('consecutivo')
                    ->lockForUpdate()->get();

                foreach ($facturas as $f) {
                    $saldo = $f->saldo;
                    if ($saldo <= 0) continue;
                    $aplicar = min($saldo, $montoRestante);
                    if ($aplicar <= 0) break;
                    AbonoDetalle::create([
                        'abono_id'=>$abono->id,
                        'factura_id'=>$f->id,
                        'valor_aplicado'=>$aplicar,
                        'descuento_aplicado'=>0,
                    ]);
                    $montoRestante -= $aplicar;
                    self::recalcularEstadoFactura($f->id);
                    if ($montoRestante <= 0) break;
                }
            } else {
                foreach (($data['detalles'] ?? []) as $det) {
                    $f = Factura::findOrFail($det['factura_id']);
                    $aplicar = (float)$det['valor_aplicado'];
                    if ($aplicar <= 0) continue;
                    if ($aplicar > $f->saldo) throw new \Exception("Aplicación excede saldo de la factura {$f->id}");
                    AbonoDetalle::create([
                        'abono_id'=>$abono->id,
                        'factura_id'=>$f->id,
                        'valor_aplicado'=>$aplicar,
                        'descuento_aplicado'=>0,
                    ]);
                    self::recalcularEstadoFactura($f->id);
                }
            }
        });

        return redirect()->route('abonos.index')->with('ok','Abono registrado');
    }

    public static function recalcularEstadoFactura(int $facturaId): void {
        $f = Factura::find($facturaId);
        if (!$f) return;
        $saldo = $f->saldo;
        if ($saldo <= 0.00001) {
            $f->estado = 'pagado';
        } else {
            $f->estado = $f->fecha_vencimiento->isPast() ? 'vencido' : 'parcial';
        }
        $f->save();
    }
}

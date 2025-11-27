<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\AbonoDetalle;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\EDS; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbonoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'activos'); 
        
        $eds_id = $request->input('eds_id');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        // Lista para el select de filtros en la vista index
        $eds_list = EDS::select('id', 'nombre')->where('activo', true)->orderBy('nombre')->get();

        $query = Abono::query();

        // 1. Lógica de Estado (SoftDeletes) y Relaciones
        if ($status === 'anulados') {
            $query->onlyTrashed(); // Ver anulados
            // Cargar detalles incluso si están borrados
            $query->with(['cliente:id,razon_social', 'eds:id,nombre', 'user:id,name', 'detalles' => function($q) {
                $q->withTrashed()->with('factura');
            }]);
        } else {
            $query->with(['cliente:id,razon_social', 'eds:id,nombre', 'user:id,name', 'detalles.factura']);
        }

        // 2. Filtros Avanzados
        $query->when($eds_id, fn($q) => $q->where('eds_id', $eds_id))
              ->when($fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $fecha_desde))
              ->when($fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $fecha_hasta));

        // 3. Buscador General
        $query->when($search !== '', function ($q) use ($search, $status) {
            $q->where(function($sub) use ($search, $status) {
                $sub->whereHas('cliente', fn($c) => $c->where('razon_social', 'like', "%{$search}%"))
                    ->orWhereHas('detalles', function($d) use ($search, $status) {
                        if ($status === 'anulados') {
                            $d->withTrashed(); 
                        }
                        $d->whereHas('factura', fn($f) => $f->where('consecutivo', 'like', "%{$search}%"));
                    })
                    ->orWhere('referencia_bancaria', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        });

        $abonos = $query->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('abonos.partials.table', compact('abonos'))->render();
        }

        return view('abonos.index', compact('abonos', 'eds_list'));
    }

    public function create()
    {
        $eds = EDS::where('activo', true)->select('id', 'nombre')->orderBy('nombre')->get();
        return view('abonos.create', compact('eds'));
    }

    // API: Autocomplete de Clientes
    public function buscarClientes(Request $request)
    {
        $term = $request->input('q');
        if (strlen($term) < 2) return response()->json([]);

        $clientes = Cliente::where('estado', 'activo')
            ->where(function($q) use ($term) {
                $q->where('razon_social', 'like', "%{$term}%")
                  ->orWhere('documento', 'like', "%{$term}%");
            })
            ->limit(10)
            ->select('id', 'razon_social', 'documento')
            ->get();

        return response()->json($clientes);
    }

    // API: Cartera Pendiente de un Cliente
    public function carteraCliente(Request $request, Cliente $cliente)
    {
        $query = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->pendientes() 
            ->with('eds:id,nombre');

        // Filtros internos de la API
        if ($request->filled('eds_id')) {
            $query->where('eds_id', $request->eds_id);
        }

        if ($request->filled('q_factura')) {
            $query->where('consecutivo', 'like', '%' . $request->q_factura . '%');
        }

        // Total deuda global para el resumen
        $totalDeuda = (clone $query)->sum('saldo_pendiente');

        // Paginación
        $pendientes = $query
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(20);

        // Transformación de datos (Aquí agregamos el corte)
        $data = $pendientes->getCollection()->map(function($f) {
            return [
                'id' => $f->id,
                'consecutivo' => $f->consecutivo,
                'prefijo' => $f->prefijo,
                'fecha_vencimiento' => $f->fecha_vencimiento->format('Y-m-d'),
                'saldo_pendiente' => $f->saldo_pendiente,
                'eds' => $f->eds,
                
                // --- NUEVOS CAMPOS: FECHAS DE CORTE ---
                'corte_desde' => $f->corte_desde ? $f->corte_desde->format('Y-m-d') : '',
                'corte_hasta' => $f->corte_hasta ? $f->corte_hasta->format('Y-m-d') : '',
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

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'fecha'       => 'required|date',
            'metodo_pago' => 'required|string',
            'detalles'    => 'required|array|min:1',
            'detalles.*.factura_id' => 'required|exists:facturas,id',
            'detalles.*.abono'      => 'required|numeric|min:0.01',
        ]);

        try {
            $totalRecibo = 0;
            $primerFacturaId = array_values($request->detalles)[0]['factura_id'] ?? null;
            
            if(!$primerFacturaId) throw new \Exception("No se recibieron detalles válidos.");
            
            $facturaRef = Factura::find($primerFacturaId);
            if(!$facturaRef) throw new \Exception("La factura referencia no existe.");
            
            $edsId = $facturaRef->eds_id;

            // Validación de montos
            foreach ($request->detalles as $item) {
                $factura = Factura::findOrFail($item['factura_id']);
                if ($item['abono'] > $factura->saldo_pendiente + 0.01) {
                    return back()->withErrors(['msg' => "El abono a la cuenta #{$factura->consecutivo} supera su saldo pendiente."])->withInput();
                }
                $totalRecibo += $item['abono'];
            }

            DB::transaction(function () use ($request, $totalRecibo, $edsId) {
                // 1. Crear Recibo
                $abono = Abono::create([
                    'cliente_id'      => $request->cliente_id,
                    'eds_id'          => $edsId, 
                    'fecha'           => $request->fecha,
                    'valor'           => $totalRecibo,
                    'medio_pago'      => $request->metodo_pago,
                    'referencia_bancaria' => $request->referencia,
                    'observaciones'   => $request->notas,
                    'user_id'         => Auth::id()
                ]);

                // 2. Crear Detalles
                foreach ($request->detalles as $item) {
                    if ($item['abono'] > 0) {
                        AbonoDetalle::create([
                            'abono_id'       => $abono->id,
                            'factura_id'     => $item['factura_id'],
                            'valor_aplicado' => $item['abono']
                        ]);
                    }
                }
            });

            return redirect()->route('abonos.index')->with('ok', 'Recibo de caja registrado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error SQL: ' . $e->getMessage());
            return back()->withErrors(['msg' => 'Error de base de datos.'])->withInput();
        } catch (\Exception $e) {
            Log::error('Error General: ' . $e->getMessage());
            return back()->withErrors(['msg' => 'Error inesperado: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Abono $abono)
    {
        try {
            $abono->delete(); 
            return back()->with('ok', 'Abono anulado y saldos restaurados.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'No se pudo anular el abono.']);
        }
    }
}
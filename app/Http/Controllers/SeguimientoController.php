<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SeguimientoController extends Controller
{
    // 1. Vista de Notificaciones (Listado de tareas pendientes)
    public function notificacionesIndex(Request $request)
    {
        $alertas = Seguimiento::with('cliente', 'usuario')
            ->where('estado', 'pendiente')
            ->whereDate('fecha_compromiso', '<=', Carbon::today())
            ->orderBy('fecha_compromiso', 'asc')
            ->paginate(20);

        return view('seguimientos.notificaciones', compact('alertas'));
    }

    // 2. API para el Badge (Globito Rojo)
    public function conteoAlertas()
    {
        $count = Seguimiento::where('estado', 'pendiente')
            ->whereDate('fecha_compromiso', '<=', Carbon::today())
            ->count();

        return response()->json(['count' => $count]);
    }

    public function index(Request $request)
    {
        // 1) Sincronizar vencidos SOLO 1 VEZ POR DÍA
        $todayKey = 'seguimientos_vencidos_sync_' . Carbon::today()->toDateString();

        if (!Cache::has($todayKey)) {
            Seguimiento::where('estado', 'pendiente')
                ->whereNotNull('fecha_compromiso')
                ->whereDate('fecha_compromiso', '<', Carbon::today())
                ->update(['estado' => 'vencido']);

            Cache::put($todayKey, true, now()->addDay());
        }

        // 2) Filtros de búsqueda
        $search = $request->string('search')->trim()->toString();
        $filtro = $request->input('filtro', 'todos');

        $clientesQuery = Cliente::query()
            ->with([
                'seguimientos' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->withSum(
                ['facturas as deuda_total' => function ($q) {
                    $q->where('saldo_pendiente', '>', 0)
                      ->where('estado', '!=', 'anulada');
                }],
                'saldo_pendiente'
            );

        if ($filtro === 'hoy') {
            $clientesQuery->whereHas('seguimientos', function ($q) {
                $q->where('estado', 'pendiente')
                  ->whereDate('fecha_compromiso', Carbon::today());
            });
        } elseif ($filtro === 'vencidos') {
            $clientesQuery->whereHas('seguimientos', function ($q) {
                $q->where('estado', 'vencido');
            });
        } else {
            $clientesQuery->where(function ($q) {
                $q->whereHas('facturas', function ($f) {
                    $f->where('saldo_pendiente', '>', 0);
                })->orWhereHas('seguimientos', function ($s) {
                    $s->whereIn('estado', ['pendiente', 'vencido', 'cancelado']);
                });
            });
        }

        if ($search !== '') {
            $clientesQuery->where(function ($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%");
            });
        }

        $clientes = $clientesQuery
            ->orderByDesc('deuda_total')
            ->paginate(15)
            ->withQueryString();

        $countHoy = Seguimiento::where('estado', 'pendiente')
            ->whereNotNull('fecha_compromiso')
            ->whereDate('fecha_compromiso', Carbon::today())
            ->count();

        $countVencidos = Seguimiento::where('estado', 'vencido')->count();

        if ($request->ajax() || $request->input('ajax')) {
            return view('seguimientos.partials.table', compact('clientes'))->render();
        }

        return view('seguimientos.index', compact('clientes', 'countHoy', 'countVencidos'));
    }

    public function historial(Cliente $cliente)
    {
        $historial = $cliente->seguimientos()
            ->with(['usuario:id,name', 'facturas:id,consecutivo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                $facturasStr = $s->facturas->pluck('consecutivo')->implode(', ');

                return [
                    'id'                     => $s->id,
                    'fecha_gestion'          => $s->fecha_gestion
                        ? $s->fecha_gestion->format('d/m/Y')
                        : 'Hoy',
                    'usuario'                => $s->usuario->name,
                    'usuario_id'             => $s->user_id,
                    'observacion'            => $s->observacion,
                    'fecha_compromiso'       => $s->fecha_compromiso
                        ? $s->fecha_compromiso->format('Y-m-d')
                        : null,
                    'fecha_compromiso_human' => $s->fecha_compromiso
                        ? $s->fecha_compromiso->format('d/m/Y')
                        : '--',
                    'monto'                  => $s->monto_compromiso
                        ? number_format($s->monto_compromiso, 0)
                        : null,
                    'estado'                 => $s->estado,
                    'facturas_ids'           => $s->facturas->pluck('id')->toArray(),
                    'facturas_afectadas'     => $facturasStr ?: 'General',
                ];
            });

        $pendientes = $cliente->facturas()
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', '!=', 'anulada')
            ->with('eds:id,nombre')
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($f) {
                return [
                    'id'          => $f->id,
                    'consecutivo' => $f->consecutivo,
                    'eds'         => $f->eds->nombre,
                    'vencimiento' => $f->fecha_vencimiento
                        ? $f->fecha_vencimiento->format('d/m/Y')
                        : '',
                    'corte_desde' => $f->corte_desde
                        ? $f->corte_desde->format('d/m')
                        : '',
                    'corte_hasta' => $f->corte_hasta
                        ? $f->corte_hasta->format('d/m')
                        : '',
                    'dias_mora'   => intval(now()->diffInDays($f->fecha_vencimiento, false) * -1),
                    'saldo'       => (float) $f->saldo_pendiente,
                    'saldo_fmt'   => number_format($f->saldo_pendiente, 0),
                ];
            });

        return response()->json([
            'historial' => $historial,
            'pendientes'=> $pendientes,
            'auth_id'   => Auth::id(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'observacion'    => 'required|string|min:5',
            'fecha_compromiso' => 'nullable|date|after_or_equal:today',
            'facturas_ids'   => 'nullable|array',
            'facturas_ids.*' => 'exists:facturas,id',
            'monto_compromiso' => 'nullable|numeric|min:0',
        ]);

        $seguimiento = Seguimiento::create([
            'cliente_id'       => $request->cliente_id,
            'user_id'          => Auth::id(),
            'fecha_gestion'    => now(),
            'observacion'      => $request->observacion,
            'fecha_compromiso' => $request->fecha_compromiso,
            'monto_compromiso' => $request->monto_compromiso,
            'estado'           => $request->fecha_compromiso ? 'pendiente' : 'cumplido',
        ]);

        if ($request->has('facturas_ids')) {
            $seguimiento->facturas()->attach($request->facturas_ids);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gestión registrada exitosamente.',
            ]);
        }

        return back()->with('ok', 'Gestión registrada correctamente.');
    }

    public function update(Request $request, Seguimiento $seguimiento)
    {
        $request->validate([
            'observacion'      => 'required|string|min:5',
            'fecha_compromiso' => 'nullable|date',
            'monto_compromiso' => 'nullable|numeric|min:0',
            'facturas_ids'     => 'nullable|array',
        ]);

        $seguimiento->update([
            'observacion'      => $request->observacion,
            'fecha_compromiso' => $request->fecha_compromiso,
            'monto_compromiso' => $request->monto_compromiso,
            'estado'           => $request->fecha_compromiso ? 'pendiente' : 'cumplido',
        ]);

        if ($request->has('facturas_ids')) {
            $seguimiento->facturas()->sync($request->facturas_ids);
        } else {
            $seguimiento->facturas()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Actualizado correctamente.',
        ]);
    }

    public function destroy(Seguimiento $seguimiento)
    {
        $seguimiento->delete();

        return response()->json(['success' => true]);
    }

    public function check(Seguimiento $seguimiento)
    {
        $seguimiento->update(['estado' => 'cumplido']);

        return response()->json(['success' => true]);
    }

    public function cancel(Seguimiento $seguimiento)
    {
        $seguimiento->update(['estado' => 'cancelado']);

        return response()->json(['success' => true]);
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Abono;
use App\Models\EDS;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class InformeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EXTRACTO POR CLIENTE
    |--------------------------------------------------------------------------
    */
    public function extracto(Request $request)
    {
        $clienteId  = $request->input('cliente_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        // Si no hay filtros de fecha, usar el mes actual por defecto
        if (empty($fechaDesde) || empty($fechaHasta)) {
            $now        = Carbon::now();
            $fechaDesde = $now->copy()->startOfMonth()->format('Y-m-d');
            $fechaHasta = $now->copy()->format('Y-m-d');
        }

        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        $cliente       = null;
        $saldoInicial  = 0.0;
        $movimientos   = collect();
        $totalDebitos  = 0.0;
        $totalCreditos = 0.0;
        $saldoFinal    = 0.0;

        if ($clienteId) {
            // Solo lo que la vista necesita
            $cliente = Cliente::query()
                ->select('id', 'razon_social', 'documento')
                ->findOrFail($clienteId);

            // -------------------------------------------------------
            // 1) SALDO INICIAL
            // -------------------------------------------------------
            $totalFacturasAntes = Factura::query()
                ->where('cliente_id', $cliente->id)
                ->where('estado', '!=', 'anulada')
                ->where('fecha_emision', '<', $desde)
                ->sum('valor_total');

            $totalAbonosAntes = Abono::query()
                ->where('cliente_id', $cliente->id)
                ->where('fecha', '<', $desde)
                ->sum('valor');

            $saldoInicial = (float) $totalFacturasAntes - (float) $totalAbonosAntes;

            // -------------------------------------------------------
            // 2) MOVIMIENTOS: FACTURAS (Débitos)
            // -------------------------------------------------------
            $facturas = Factura::query()
                ->select('id', 'cliente_id', 'eds_id', 'fecha_emision', 'prefijo', 'consecutivo', 'valor_total')
                ->with(['eds:id,nombre'])
                ->where('cliente_id', $cliente->id)
                ->where('estado', '!=', 'anulada')
                ->whereBetween('fecha_emision', [$desde, $hasta])
                ->orderBy('fecha_emision')
                ->orderBy('id')
                ->get()
                ->toBase()
                ->map(function ($f) {
                    $fecha = $f->fecha_emision instanceof Carbon
                        ? $f->fecha_emision
                        : Carbon::parse($f->fecha_emision);

                    return [
                        'timestamp'       => $fecha->getTimestamp(),
                        'fecha'           => $fecha,
                        'tipo'            => 'Factura',
                        'documento'       => $f->prefijo . '-' . $f->consecutivo,
                        'eds'             => $f->eds->nombre ?? null,
                        'descripcion'     => 'Cuenta de cobro',
                        'debito'          => (float) $f->valor_total,
                        'credito'         => 0.0,
                        'orden_prioridad' => 1,
                    ];
                });

            // -------------------------------------------------------
            // 3) MOVIMIENTOS: ABONOS (Créditos)
            // -------------------------------------------------------
            $abonos = Abono::query()
                ->select('id', 'cliente_id', 'eds_id', 'fecha', 'medio_pago', 'referencia_bancaria', 'valor')
                ->with([
                    'eds:id,nombre',
                    'detalles.factura:id,prefijo,consecutivo',
                ])
                ->where('cliente_id', $cliente->id)
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('fecha')
                ->orderBy('id')
                ->get()
                ->toBase()
                ->map(function ($a) {
                    $fechaAbono = $a->fecha instanceof Carbon
                        ? $a->fecha
                        : Carbon::parse($a->fecha);

                    $refsFacturas = $a->detalles
                        ->map(function ($det) {
                            if (!$det->factura) {
                                return null;
                            }
                            return $det->factura->prefijo . '-' . $det->factura->consecutivo;
                        })
                        ->filter()
                        ->implode(', ');

                    $descripcion = 'Recaudo ' . ($a->medio_pago ?: '');
                    if ($refsFacturas !== '') {
                        $descripcion .= ' (Pago: ' . $refsFacturas . ')';
                    }

                    return [
                        'timestamp'       => $fechaAbono->getTimestamp(),
                        'fecha'           => $fechaAbono,
                        'tipo'            => 'Abono',
                        'documento'       => $a->referencia_bancaria ?: ('ABN-' . $a->id),
                        'eds'             => $a->eds->nombre ?? null,
                        'descripcion'     => $descripcion,
                        'debito'         => 0.0,
                        'credito'        => (float) $a->valor,
                        'orden_prioridad'=> 2,
                    ];
                });

            // -------------------------------------------------------
            // 4) UNIÓN Y CÁLCULO DE SALDOS
            // -------------------------------------------------------
            $movimientos = $facturas
                ->merge($abonos)
                ->sort(function ($a, $b) {
                    if ($a['timestamp'] === $b['timestamp']) {
                        return $a['orden_prioridad'] <=> $b['orden_prioridad'];
                    }
                    return $a['timestamp'] <=> $b['timestamp'];
                })
                ->values();

            $saldo = $saldoInicial;

            $movimientos = $movimientos->map(function ($m) use (&$saldo, &$totalDebitos, &$totalCreditos) {
                $totalDebitos  += $m['debito'];
                $totalCreditos += $m['credito'];

                $saldo += $m['debito'];
                $saldo -= $m['credito'];

                $m['saldo'] = $saldo;

                return $m;
            });

            $saldoFinal = $saldo;

            // -------------------------------------------------------
            // 5) EXPORT CSV
            // -------------------------------------------------------
            if ($request->get('export') === 'csv') {
                $fileName = 'extracto_' . Str::slug($cliente->razon_social) . '_' . $desde->format('Ymd') . '.csv';

                return response()->streamDownload(
                    function () use ($cliente, $fechaDesde, $fechaHasta, $saldoInicial, $movimientos) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

                        $delimiter = ';';

                        fputcsv($out, ['Cliente', $cliente->razon_social], $delimiter);
                        fputcsv($out, ['NIT', $cliente->documento], $delimiter);
                        fputcsv($out, ['Periodo', $fechaDesde . ' al ' . $fechaHasta], $delimiter);
                        fputcsv($out, [], $delimiter);
                        fputcsv(
                            $out,
                            ['Fecha', 'Tipo', 'Documento', 'EDS', 'Descripción', 'Débito', 'Crédito', 'Saldo'],
                            $delimiter
                        );
                        fputcsv(
                            $out,
                            ['', '', '', '', 'Saldo inicial', '', '', number_format($saldoInicial, 2, ',', '.')],
                            $delimiter
                        );

                        foreach ($movimientos as $m) {
                            fputcsv($out, [
                                $m['fecha']->format('Y-m-d'),
                                $m['tipo'],
                                $m['documento'],
                                $m['eds'],
                                $m['descripcion'],
                                $m['debito'] > 0 ? number_format($m['debito'], 2, ',', '.') : '',
                                $m['credito'] > 0 ? number_format($m['credito'], 2, ',', '.') : '',
                                number_format($m['saldo'], 2, ',', '.'),
                            ], $delimiter);
                        }

                        fclose($out);
                    },
                    $fileName,
                    ['Content-Type' => 'text/csv']
                );
            }
        }

        return view('informes.extracto', compact(
            'cliente',
            'fechaDesde',
            'fechaHasta',
            'saldoInicial',
            'movimientos',
            'totalDebitos',
            'totalCreditos',
            'saldoFinal'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CARTERA POR EDADES
    |--------------------------------------------------------------------------
    */
    public function carteraEdades(Request $request)
    {
        // 1. Fecha de corte
        $fechaCorteInput = $request->input('fecha_corte');
        if (empty($fechaCorteInput)) {
            $fechaCorte = Carbon::now()->endOfDay();
            $fechaCorteInput = $fechaCorte->toDateString();
        } else {
            $fechaCorte = Carbon::parse($fechaCorteInput)->endOfDay();
        }

        // bucket seleccionado (para filtro por rango desde la gráfica)
        $bucketSeleccionado = $request->input('bucket');

        // 2. Buckets inicializados
        $buckets = [
            'corriente'  => 0.0,
            'd1_7'       => 0.0,
            'd8_15'      => 0.0,
            'd16_22'     => 0.0,
            'd23_30'     => 0.0,
            'd31_60'     => 0.0,
            'd61_90'     => 0.0,
            'd91_180'    => 0.0,
            'd181_360'   => 0.0,
            'd360_mas'   => 0.0,
        ];

        $totalCartera      = 0.0;
        $totalMas30        = 0.0;
        $totalMas90        = 0.0;
        $totalFacturas     = 0;
        $totalClientes     = 0;
        $detallesFacturas  = collect();

        // 3. Traer facturas vigentes hasta la fecha de corte
        $facturasQuery = Factura::query()
            ->where('estado', '!=', 'anulada')
            ->whereDate('fecha_emision', '<=', $fechaCorte->toDateString())
            ->with(['cliente:id,razon_social,documento', 'eds:id,nombre']);

        $clienteId = $request->input('cliente_id');
        if ($clienteId) {
            $facturasQuery->where('cliente_id', $clienteId);
        }

        $facturas = $facturasQuery->get();

        if ($facturas->isNotEmpty()) {
            $facturaIds = $facturas->pluck('id')->all();

            // 4. Abonos aplicados hasta la fecha de corte (SIN modelo, SIN SoftDeletes)
            $abonosPorFactura = DB::table('abono_detalle as ad')
                ->join('abonos as a', 'a.id', '=', 'ad.abono_id')
                ->whereIn('ad.factura_id', $facturaIds)
                ->whereDate('a.fecha', '<=', $fechaCorte->toDateString())
                ->selectRaw('ad.factura_id, SUM(ad.valor_aplicado) as total_abonos')
                ->groupBy('ad.factura_id')
                ->pluck('total_abonos', 'factura_id'); // [factura_id => total_abonos]

            // 5. Recorrer facturas y calcular saldo a corte + bucket
            $clientesConSaldo = [];

            foreach ($facturas as $factura) {
                $valorTotalFactura = (float) $factura->valor_total;
                $abonosFactura     = (float) ($abonosPorFactura[$factura->id] ?? 0.0);

                // saldo a la fecha de corte
                $saldoCorte = $valorTotalFactura - $abonosFactura;

                if ($saldoCorte <= 0) {
                    continue;
                }

                $totalFacturas++;
                $totalCartera += $saldoCorte;
                $clientesConSaldo[$factura->cliente_id] = true;

                $fechaVenc = $factura->fecha_vencimiento instanceof Carbon
                    ? $factura->fecha_vencimiento->copy()->startOfDay()
                    : Carbon::parse($factura->fecha_vencimiento)->startOfDay();

                $fechaCorteDia = $fechaCorte->copy()->startOfDay();

                $diasVencidos = 0;
                $bucketKey    = 'corriente';

                // Determinar corriente o vencido
                if ($fechaVenc->greaterThanOrEqualTo($fechaCorteDia)) {
                    $buckets['corriente'] += $saldoCorte;
                    $diasVencidos = 0;
                    $bucketKey    = 'corriente';
                } else {
                    $diasVencidos = $fechaVenc->diffInDays($fechaCorteDia);

                    if ($diasVencidos >= 1 && $diasVencidos <= 7) {
                        $buckets['d1_7'] += $saldoCorte;
                        $bucketKey = 'd1_7';
                    } elseif ($diasVencidos >= 8 && $diasVencidos <= 15) {
                        $buckets['d8_15'] += $saldoCorte;
                        $bucketKey = 'd8_15';
                    } elseif ($diasVencidos >= 16 && $diasVencidos <= 22) {
                        $buckets['d16_22'] += $saldoCorte;
                        $bucketKey = 'd16_22';
                    } elseif ($diasVencidos >= 23 && $diasVencidos <= 30) {
                        $buckets['d23_30'] += $saldoCorte;
                        $bucketKey = 'd23_30';
                    } elseif ($diasVencidos >= 31 && $diasVencidos <= 60) {
                        $buckets['d31_60'] += $saldoCorte;
                        $bucketKey = 'd31_60';
                    } elseif ($diasVencidos >= 61 && $diasVencidos <= 90) {
                        $buckets['d61_90'] += $saldoCorte;
                        $bucketKey = 'd61_90';
                    } elseif ($diasVencidos >= 91 && $diasVencidos <= 180) {
                        $buckets['d91_180'] += $saldoCorte;
                        $bucketKey = 'd91_180';
                    } elseif ($diasVencidos >= 181 && $diasVencidos <= 360) {
                        $buckets['d181_360'] += $saldoCorte;
                        $bucketKey = 'd181_360';
                    } else {
                        $buckets['d360_mas'] += $saldoCorte;
                        $bucketKey = 'd360_mas';
                    }
                }

                // KPIs >30 y >90
                if ($diasVencidos > 30) {
                    $totalMas30 += $saldoCorte;
                }
                if ($diasVencidos > 90) {
                    $totalMas90 += $saldoCorte;
                }

                // Si hay filtro de bucket, solo guardamos detalle de ese rango
                if ($bucketSeleccionado && $bucketSeleccionado !== $bucketKey) {
                    continue;
                }

                // Detalle para tabla
                $detallesFacturas->push([
                    'cliente'       => $factura->cliente ? $factura->cliente->razon_social : 'Sin cliente',
                    'documento'     => $factura->cliente ? $factura->cliente->documento : '',
                    'eds'           => $factura->eds ? $factura->eds->nombre : '',
                    'prefijo'       => $factura->prefijo,
                    'consecutivo'   => $factura->consecutivo,
                    'fecha_emision' => $factura->fecha_emision instanceof Carbon
                        ? $factura->fecha_emision->format('Y-m-d')
                        : $factura->fecha_emision,
                    'fecha_venc'    => $fechaVenc->format('Y-m-d'),
                    'dias_vencidos' => $diasVencidos,
                    'saldo_corte'   => $saldoCorte,
                    'bucket'        => $bucketKey,
                ]);
            }

            $totalClientes = count($clientesConSaldo);
        }

        // 6. Redondeo buckets
        $buckets = array_map(function ($v) {
            return round($v, 2);
        }, $buckets);

        // 7. Paginación manual del detalle
        $perPage   = 50;
        $page      = LengthAwarePaginator::resolveCurrentPage();
        $sorted    = $detallesFacturas->sortByDesc('saldo_corte')->values();
        $totalRows = $sorted->count();

        $resultsForPage = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $detallesPaginator = new LengthAwarePaginator(
            $resultsForPage,
            $totalRows,
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('informes.cartera_edades', [
            'fechaCorte'        => $fechaCorteInput,
            'buckets'           => $buckets,
            'totalCartera'      => $totalCartera,
            'totalMas30'        => $totalMas30,
            'totalMas90'        => $totalMas90,
            'totalFacturas'     => $totalFacturas,
            'totalClientes'     => $totalClientes,
            'detalles'          => $detallesPaginator,
            'bucketSeleccionado'=> $bucketSeleccionado,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADO DE CUENTAS A UNA FECHA DE CORTE (VISTA)
    |--------------------------------------------------------------------------
    */
    public function estadoCuentas(Request $request)
    {
        // Fecha de corte
        $fechaCorteInput = $request->input('fecha_corte');
        if (empty($fechaCorteInput)) {
            $fechaCorte = Carbon::now()->endOfDay();
            $fechaCorteInput = $fechaCorte->toDateString();
        } else {
            $fechaCorte = Carbon::parse($fechaCorteInput)->endOfDay();
        }

        // Filtros
        $eds_id = $request->input('eds_id');
        $search = trim((string) $request->input('search'));

        // Lista de EDS para el filtro
        $eds_list = EDS::select('id', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        /*
        * Subconsulta de abonos por factura hasta la fecha de corte
        */
        $subAbonos = DB::table('abono_detalle as ad')
            ->join('abonos as a', 'a.id', '=', 'ad.abono_id')
            ->whereDate('a.fecha', '<=', $fechaCorte->toDateString())
            ->selectRaw('ad.factura_id, SUM(ad.valor_aplicado) as total_abonos')
            ->groupBy('ad.factura_id');

        /*
        * Builder base reutilizable (joins + filtros), sin SELECT todavía
        */
        $buildBase = function () use ($subAbonos, $fechaCorte, $eds_id, $search) {
            $q = Factura::query()
                ->join('clientes', 'clientes.id', '=', 'facturas.cliente_id')
                ->join('eds', 'eds.id', '=', 'facturas.eds_id')
                ->leftJoinSub($subAbonos, 'abonos_corte', function ($join) {
                    $join->on('abonos_corte.factura_id', '=', 'facturas.id');
                })
                ->where('facturas.estado', '!=', 'anulada')
                ->whereNull('facturas.deleted_at')
                ->whereDate('facturas.fecha_emision', '<=', $fechaCorte->toDateString())
                ->whereRaw('(facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)) > 0');

            // Filtro por EDS
            if (!empty($eds_id)) {
                $q->where('facturas.eds_id', $eds_id);
            }

            // Filtro por texto (cliente o NIT)
            if ($search !== '') {
                $q->where(function ($qq) use ($search) {
                    $qq->where('clientes.razon_social', 'like', "%{$search}%")
                    ->orWhere('clientes.documento', 'like', "%{$search}%");
                });
            }

            return $q;
        };

        /*
        * Consulta DETALLE (para la tabla paginada)
        */
        $query = $buildBase()->selectRaw("
            facturas.id,
            facturas.prefijo,
            facturas.consecutivo,
            facturas.fecha_emision,
            facturas.fecha_vencimiento,
            facturas.corte_desde,
            facturas.corte_hasta,
            facturas.valor_total,
            clientes.id as cliente_id,
            clientes.razon_social as cliente_nombre,
            clientes.documento as cliente_documento,
            eds.nombre as eds_nombre,
            IFNULL(abonos_corte.total_abonos, 0) as abonos_corte,
            (facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)) as saldo_corte,
            DATEDIFF(?, facturas.fecha_vencimiento) as dias_mora
        ", [$fechaCorte->toDateString()]);

        /*
        * Totales a partir de la base, pero usando SUM sin tocar el SELECT
        */
        $totalsBase = $buildBase();

        // Total cartera a corte
        $grand_total = (clone $totalsBase)
            ->sum(DB::raw('facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)'));
        $grand_total = $grand_total ?? 0;

        // Total > 30 días
        $total_mas_30 = (clone $totalsBase)
            ->whereRaw('DATEDIFF(?, facturas.fecha_vencimiento) > 30', [$fechaCorte->toDateString()])
            ->sum(DB::raw('facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)'));
        $total_mas_30 = $total_mas_30 ?? 0;

        // Total > 90 días
        $total_mas_90 = (clone $totalsBase)
            ->whereRaw('DATEDIFF(?, facturas.fecha_vencimiento) > 90', [$fechaCorte->toDateString()])
            ->sum(DB::raw('facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)'));
        $total_mas_90 = $total_mas_90 ?? 0;

        // Detalle paginado (orden por cliente y luego por vencimiento)
        $items = $query
            ->orderBy('cliente_nombre')
            ->orderBy('facturas.fecha_vencimiento')
            ->paginate(20)
            ->withQueryString();

        $totalFacturas = $items->total();

        return view('informes.estado_cuentas', [
            'fechaCorte'    => $fechaCorteInput,
            'items'         => $items,
            'totalCartera'  => $grand_total,
            'totalFacturas' => $totalFacturas,
            'totalMas30'    => $total_mas_30,
            'totalMas90'    => $total_mas_90,
            'eds_list'      => $eds_list,
            'eds_id'        => $eds_id,
            'search'        => $search,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO DE CUENTAS A CORTE (EXPORT CSV)
    |--------------------------------------------------------------------------
    */
    public function estadoCuentasExport(Request $request)
    {
        $fechaCorteInput = $request->input('fecha_corte');
        if (empty($fechaCorteInput)) {
            $fechaCorte = Carbon::now()->endOfDay();
            $fechaCorteInput = $fechaCorte->toDateString();
        } else {
            $fechaCorte = Carbon::parse($fechaCorteInput)->endOfDay();
        }

        $eds_id = $request->input('eds_id');
        $search = trim((string) $request->input('search'));

        $subAbonos = DB::table('abono_detalle as ad')
            ->join('abonos as a', 'a.id', '=', 'ad.abono_id')
            ->whereDate('a.fecha', '<=', $fechaCorte->toDateString())
            ->selectRaw('ad.factura_id, SUM(ad.valor_aplicado) as total_abonos')
            ->groupBy('ad.factura_id');

        $query = Factura::query()
            ->join('clientes', 'clientes.id', '=', 'facturas.cliente_id')
            ->join('eds', 'eds.id', '=', 'facturas.eds_id')
            ->leftJoinSub($subAbonos, 'abonos_corte', function ($join) {
                $join->on('abonos_corte.factura_id', '=', 'facturas.id');
            })
            ->where('facturas.estado', '!=', 'anulada')
            ->whereNull('facturas.deleted_at')
            ->whereDate('facturas.fecha_emision', '<=', $fechaCorte->toDateString())
            ->selectRaw("
                facturas.prefijo,
                facturas.consecutivo,
                facturas.fecha_emision,
                facturas.fecha_vencimiento,
                facturas.valor_total,
                clientes.razon_social,
                clientes.documento,
                eds.nombre as eds_nombre,
                IFNULL(abonos_corte.total_abonos, 0) as abonos_corte,
                (facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)) as saldo_corte,
                DATEDIFF(?, facturas.fecha_vencimiento) as dias_mora
            ", [$fechaCorte->toDateString()])
            ->whereRaw('(facturas.valor_total - IFNULL(abonos_corte.total_abonos, 0)) > 0');

        if (!empty($eds_id)) {
            $query->where('facturas.eds_id', $eds_id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('clientes.razon_social', 'like', "%{$search}%")
                  ->orWhere('clientes.documento', 'like', "%{$search}%");
            });
        }

        $fileName = 'estado_cuentas_corte_' . $fechaCorteInput . '.csv';

        return response()->streamDownload(function () use ($query, $fechaCorteInput) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            $delimiter = ';';

            fputcsv($out, ['Estado de cuentas a corte', $fechaCorteInput], $delimiter);
            fputcsv($out, [], $delimiter);
            fputcsv($out, [
                'Cliente',
                'NIT/CC',
                'EDS',
                'Cuenta',
                'Emisión',
                'Vencimiento',
                'Días Mora',
                'Valor Original',
                'Abonos a corte',
                'Saldo a corte'
            ], $delimiter);

            foreach ($query->orderBy('clientes.razon_social')->cursor() as $row) {
                fputcsv($out, [
                    $row->razon_social,
                    $row->documento,
                    $row->eds_nombre,
                    $row->prefijo . '-' . $row->consecutivo,
                    Carbon::parse($row->fecha_emision)->format('Y-m-d'),
                    Carbon::parse($row->fecha_vencimiento)->format('Y-m-d'),
                    (int) $row->dias_mora,
                    number_format($row->valor_total, 2, ',', '.'),
                    number_format($row->abonos_corte, 2, ',', '.'),
                    number_format($row->saldo_corte, 2, ',', '.'),
                ], $delimiter);
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

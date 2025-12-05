<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Abono;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InformeController extends Controller
{
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
                        'debito'          => 0.0,
                        'credito'         => (float) $a->valor,
                        'orden_prioridad' => 2,
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
}

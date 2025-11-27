<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- IMPORTANTE PARA CONSERVAR HISTORIAL
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbonoDetalle extends Model
{
    use SoftDeletes; // <--- IMPORTANTE

    protected $table = 'abono_detalle';
    protected $fillable = ['abono_id', 'factura_id', 'valor_aplicado', 'descuento_aplicado'];

    public function abono(): BelongsTo { return $this->belongsTo(Abono::class); }
    public function factura(): BelongsTo { return $this->belongsTo(Factura::class); }

    // --- LÓGICA FINANCIERA AUTOMÁTICA ---
    protected static function booted()
    {
        // 1. AL PAGAR (Crear detalle): RESTAR SALDO
        static::created(function ($detalle) {
            $factura = $detalle->factura;
            
            // Restamos el valor pagado
            $factura->saldo_pendiente -= $detalle->valor_aplicado;
            
            // Validamos que no quede negativo por error de redondeo
            if ($factura->saldo_pendiente < 0) $factura->saldo_pendiente = 0;

            // Actualizamos Estado
            if ($factura->saldo_pendiente <= 0.01) {
                $factura->estado = 'pagada';
            } else {
                $factura->estado = 'parcial';
            }
            $factura->save();
        });

        // 2. AL ANULAR (Borrar detalle): DEVOLVER SALDO
        static::deleted(function ($detalle) {
            $factura = $detalle->factura;
            
            // Devolvemos el valor a la deuda
            $factura->saldo_pendiente += $detalle->valor_aplicado;
            
            // Validamos que no supere el total original (seguridad)
            if ($factura->saldo_pendiente > $factura->valor_total) {
                $factura->saldo_pendiente = $factura->valor_total;
            }

            // Revertimos el Estado
            // Usamos un margen de error de 0.01 para flotantes
            if (abs($factura->saldo_pendiente - $factura->valor_total) < 0.01) {
                $factura->estado = 'pendiente'; // Debe todo otra vez
            } else {
                $factura->estado = 'parcial';   // Aún tiene otros pagos vivos
            }
            
            $factura->save();
        });
    }
}
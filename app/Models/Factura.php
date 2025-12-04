<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Factura extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'prefijo', 'consecutivo', 'cliente_id', 'eds_id',
        'fecha_emision', 'fecha_vencimiento', 'corte_desde', 'corte_hasta',
        'valor_neto', 'descuento', 'valor_total', 'saldo_pendiente',
        'estado', 'notas'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'corte_desde' => 'date',
        'corte_hasta' => 'date',
        'valor_total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    /**
     * RELACIONES
     */
    public function cliente() 
    { 
        return $this->belongsTo(Cliente::class); 
    }

    public function eds() 
    { 
        return $this->belongsTo(EDS::class); 
    }

    // NUEVA RELACIÓN: Una factura puede tener múltiples seguimientos (bitácora específica)
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class);
    }

    /**
     * SCOPES
     */
    public function scopePendientes($query) 
    {
        return $query->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
    }
}
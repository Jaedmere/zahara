<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prefijo', 'consecutivo', 'cliente_id', 'eds_id',
        'fecha_emision', 'fecha_vencimiento', 
        'corte_desde', 'corte_hasta', // <--- NUEVOS
        'valor_neto', 'descuento', 'valor_total', 'saldo_pendiente',
        'estado', 'notas'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'corte_desde' => 'date', // <--- NUEVO
        'corte_hasta' => 'date', // <--- NUEVO
        'valor_neto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    // ... (Relaciones y Scopes se mantienen igual) ...
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function eds() { return $this->belongsTo(EDS::class); }

    public function getDiasVencidosAttribute()
    {
        if ($this->estado === 'pagada') return 0;
        return Carbon::now()->diffInDays($this->fecha_vencimiento, false) * -1; 
    }

    public function scopePendientes($query)
    {
        return $query->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
    }
}
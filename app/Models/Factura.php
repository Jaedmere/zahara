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
        'valor_neto', 'descuento', 'valor_total', 'saldo_pendiente',
        'estado', 'notas'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'valor_neto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    // --- RELACIONES ---
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function eds() { return $this->belongsTo(EDS::class); }
    // public function abonos() { return $this->hasMany(Abono::class); } // Próximamente

    // --- COMPUTADOS (Accessors) ---
    
    // Calcula días de mora (o días faltantes si es negativo)
    public function getDiasVencidosAttribute()
    {
        if ($this->estado === 'pagada') return 0;
        return Carbon::now()->diffInDays($this->fecha_vencimiento, false) * -1; 
        // Si retorna positivo: Días de mora. Negativo: Días para vencer.
    }

    public function getNumeroCompletoAttribute()
    {
        return trim($this->prefijo . ' ' . $this->consecutivo);
    }

    // --- SCOPES (Consultas Rápidas) ---

    public function scopePendientes($query)
    {
        return $query->where('saldo_pendiente', '>', 0)->where('estado', '!=', 'anulada');
    }

    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', Carbon::now())
                     ->where('saldo_pendiente', '>', 0)
                     ->where('estado', '!=', 'anulada');
    }
}
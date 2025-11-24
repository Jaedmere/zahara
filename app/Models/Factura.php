<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    use SoftDeletes;

    protected $table = 'facturas';
    protected $fillable = [
        'prefijo','consecutivo','cliente_id','eds_id','fecha_emision','fecha_vencimiento',
        'subtotal','iva','retenciones','total','estado','notas'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function eds()     { return $this->belongsTo(EDS::class); }

    public function adjuntos(): HasMany { return $this->hasMany(FacturaAdjunto::class); }

    public function detallesAbono(): HasMany { return $this->hasMany(AbonoDetalle::class); }

    public function getSaldoAttribute(): float {
        $aplicado = $this->detallesAbono()->sum('valor_aplicado') + 0.0;
        return round((float)$this->total - (float)$aplicado, 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abono extends Model
{
    use SoftDeletes;

    protected $table = 'abonos';
    protected $fillable = [
        'cliente_id','eds_id','fecha','valor','medio_pago','referencia_bancaria','banco',
        'descuento','conciliado','fecha_conciliacion','observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_conciliacion' => 'date',
        'conciliado' => 'boolean',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function eds()     { return $this->belongsTo(EDS::class); }

    public function detalles(): HasMany { return $this->hasMany(AbonoDetalle::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonoDetalle extends Model
{
    protected $table = 'abono_detalle';
    protected $fillable = ['abono_id','factura_id','valor_aplicado','descuento_aplicado'];

    public function abono() { return $this->belongsTo(Abono::class); }
    public function factura() { return $this->belongsTo(Factura::class); }
}

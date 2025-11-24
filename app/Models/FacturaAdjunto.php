<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaAdjunto extends Model
{
    protected $table = 'factura_adjuntos';
    protected $fillable = ['factura_id','nombre','tipo_mime','ruta','size'];

    public function factura() { return $this->belongsTo(Factura::class); }
}

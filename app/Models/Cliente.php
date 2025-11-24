<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';
    protected $fillable = [
        'tipo_id','documento','razon_social','email','telefono','direccion','plazo_dias','lista_precios','estado','notas'
    ];

    public function eds() {
        return $this->belongsToMany(EDS::class, 'clientes_eds', 'cliente_id', 'eds_id');
    }

    public function facturas() {
        return $this->hasMany(Factura::class);
    }

    public function abonos() {
        return $this->hasMany(Abono::class);
    }
}

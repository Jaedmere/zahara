<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tipo_id',
        'documento',
        'razon_social',
        'email',
        'telefono',
        'direccion',
        'estado', // activo, bloqueado
        'notas'
    ];

    /**
     * MUTADORES
     */
    protected function razonSocial(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::upper($value),
        );
    }

    /**
     * RELACIONES
     */

    // Relación con Facturas (Un cliente tiene muchas facturas)
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    // Relación con Abonos (Un cliente tiene muchos abonos/recibos)
    public function abonos()
    {
        return $this->hasMany(Abono::class);
    }

    // Relación con EDS (Asignación muchos a muchos)
    public function eds()
    {
        return $this->belongsToMany(EDS::class, 'clientes_eds', 'cliente_id', 'eds_id');
    }

    // NUEVA RELACIÓN: SEGUIMIENTOS (CRM)
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class);
    }
}
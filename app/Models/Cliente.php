<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_id',
        'documento',
        'razon_social',
        'email',
        'telefono',
        'direccion',
        'estado', // 'activo', 'bloqueado'
        'notas'
    ];

    /**
     * MUTADORES
     */
    
    // Convertir Razón Social a Title Case automáticamente
    protected function razonSocial(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::title($value),
        );
    }

    // Convertir Email a minúsculas siempre
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::lower($value) : null,
        );
    }

    /**
     * RELACIONES
     */

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
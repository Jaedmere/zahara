<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EDS extends Model
{
    use SoftDeletes;

    protected $table = 'eds';

    protected $fillable = [
        'codigo',
        'nombre',
        'nit',
        'ciudad',
        'email_alertas',
        'direccion',
        'telefono',
        'activo'
    ];

    /**
     * MUTADORES
     * Estos métodos se ejecutan automáticamente antes de guardar en la BD.
     */

    // Intercepta el 'nombre' y lo convierte a Title Case (Nombre Propio)
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::title($value),
        );
    }

    // Intercepta el 'codigo' y lo fuerza a MAYÚSCULAS (Recomendado)
    protected function codigo(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::upper($value),
        );
    }
        protected function ciudad(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::title($value),
        );
    }

    /**
     * RELACIONES
     */

    public function usuarios() {
        return $this->belongsToMany(User::class, 'user_eds', 'eds_id', 'user_id');
    }

    public function clientes() {
        return $this->belongsToMany(Cliente::class, 'clientes_eds', 'eds_id', 'cliente_id');
    }
}
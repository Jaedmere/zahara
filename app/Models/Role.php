<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'permisos_json'];

    // Convertir JSON a Array automáticamente
    protected $casts = [
        'permisos_json' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
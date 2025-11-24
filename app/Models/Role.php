<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['nombre', 'permisos_json'];

    protected $casts = [
        'permisos_json' => 'array',
    ];

    public function users() {
        return $this->hasMany(User::class, 'rol_id');
    }
}

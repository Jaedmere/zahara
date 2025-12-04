<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens; // <--- COMENTADO O ELIMINADO

class User extends Authenticatable
{
    use HasFactory, Notifiable; // <--- ELIMINADO HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id', // Relación con Rol
        'activo', // Estado del usuario
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    /**
     * RELACIONES
     */

    // Un usuario pertenece a un Rol
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Un usuario puede tener acceso a muchas EDS
    public function eds()
    {
        return $this->belongsToMany(EDS::class, 'user_eds', 'user_id', 'eds_id');
    }

    // Un usuario realiza muchos seguimientos (Gestiones)
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'user_id');
    }
}
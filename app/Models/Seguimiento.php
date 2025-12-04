<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seguimiento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id', 'user_id', 
        'fecha_gestion', 'observacion',
        'fecha_compromiso', 'monto_compromiso',
        'estado'
    ];

    protected $casts = [
        'fecha_gestion' => 'date',
        'fecha_compromiso' => 'date',
        'monto_compromiso' => 'decimal:2'
    ];

    // Relaciones
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    
    // RELACIÓN MUCHOS A MUCHOS
    public function facturas() { 
        // CORRECCIÓN: ->withTimestamps() para que llene created_at y updated_at en la pivote
        return $this->belongsToMany(Factura::class, 'seguimiento_factura')->withTimestamps(); 
    }

    // Scopes
    public function scopePendientes($query) {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDelDia($query) {
        return $query->whereDate('fecha_compromiso', now());
    }
    
    public function scopeVencidos($query) {
        return $query->where('estado', 'pendiente')->whereDate('fecha_compromiso', '<', now());
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abono extends Model
{
    use SoftDeletes;

    protected $table = 'abonos';
    
    protected $fillable = [
        'cliente_id', 'eds_id', 'fecha', 'valor', 
        'medio_pago', 'referencia_bancaria', 'banco',
        'descuento', 'conciliado', 'fecha_conciliacion', 
        'observaciones', 'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    // Relaciones
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function eds(): BelongsTo { return $this->belongsTo(EDS::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(AbonoDetalle::class, 'abono_id'); }

    // --- EFECTO CASCADA ---
    protected static function booted() {
        // Cuando se elimina el Abono Padre...
        static::deleting(function ($abono) {
            // Recorremos cada detalle y lo borramos individualmente
            // para que se dispare el evento 'deleted' en el hijo y restaure el saldo.
            foreach ($abono->detalles as $detalle) {
                $detalle->delete(); 
            }
        });
    }
}
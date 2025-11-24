<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromesaPago extends Model
{
    protected $table = 'promesas_pago';
    protected $fillable = ['cliente_id','eds_id','fecha_compromiso','valor','estado','notas'];

    protected $casts = [
        'fecha_compromiso' => 'date',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function eds() { return $this->belongsTo(EDS::class); }
}

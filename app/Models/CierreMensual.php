<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CierreMensual extends Model
{
    protected $table = 'cierres_mensuales';
    protected $fillable = ['mes','eds_id','totales_json'];

    protected $casts = [
        'totales_json' => 'array',
    ];
}

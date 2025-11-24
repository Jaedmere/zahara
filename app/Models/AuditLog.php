<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $table = 'audit_log';
    protected $fillable = [
        'user_id','rol','eds_context','accion','tabla','registro_id',
        'antes_json','despues_json','ip','user_agent','created_at'
    ];

    protected $casts = [
        'antes_json' => 'array',
        'despues_json' => 'array',
        'created_at' => 'datetime',
    ];
}

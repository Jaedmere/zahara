<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->storeAudit('created', null, $model->getAttributes());
        });
        static::updated(function ($model) {
            $model->storeAudit('updated', $model->getOriginal(), $model->getAttributes());
        });
        static::deleted(function ($model) {
            $model->storeAudit('deleted', $model->getOriginal(), null);
        });
    }

    protected function storeAudit(string $accion, $antes, $despues)
    {
        $user = Auth::user();
        AuditLog::create([
            'user_id' => $user?->id,
            'rol' => $user?->role?->nombre ?? null,
            'eds_context' => null,
            'accion' => $accion,
            'tabla' => $this->getTable(),
            'registro_id' => $this->getKey(),
            'antes_json' => $antes ? json_encode($antes) : null,
            'despues_json' => $despues ? json_encode($despues) : null,
            'ip' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $r) {
        $q = AuditLog::query()->orderByDesc('created_at');
        if ($r->filled('usuario')) $q->where('user_id', $r->integer('usuario'));
        if ($r->filled('tabla')) $q->where('tabla', $r->string('tabla'));
        if ($r->filled('accion')) $q->where('accion', $r->string('accion'));
        return view('auditoria.index', ['logs'=>$q->paginate(30)]);
    }
}

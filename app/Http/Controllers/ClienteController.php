<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $r) {
        $q = Cliente::query();
        if ($r->filled('search')) {
            $s = '%'.$r->string('search')->toString().'%';
            $q->where(function($qq) use ($s) {
                $qq->where('documento','like',$s)->orWhere('razon_social','like',$s);
            });
        }
        return view('clientes.index', ['clientes' => $q->paginate(15)]);
    }

    public function create() {
        $eds = EDS::orderBy('nombre')->get();
        return view('clientes.create', compact('eds'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'tipo_id'=>'required|string',
            'documento'=>'required|string|unique:clientes,documento',
            'razon_social'=>'required|string',
            'email'=>'nullable|email',
            'telefono'=>'nullable|string',
            'direccion'=>'nullable|string',
            'plazo_dias'=>'nullable|integer|min:0',
            'lista_precios'=>'nullable|string',
            'estado'=>'required|in:activo,bloqueado',
            'notas'=>'nullable|string',
            'eds_ids'=>'array'
        ]);
        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);
        $c = Cliente::create($data);
        $c->eds()->sync($eds_ids);
        return redirect()->route('clientes.index')->with('ok','Cliente creado');
    }

    public function edit(Cliente $cliente) {
        $eds = EDS::orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente','eds'));
    }

    public function update(Request $r, Cliente $cliente) {
        $data = $r->validate([
            'tipo_id'=>'required|string',
            'documento'=>'required|string|unique:clientes,documento,'.$cliente->id,
            'razon_social'=>'required|string',
            'email'=>'nullable|email',
            'telefono'=>'nullable|string',
            'direccion'=>'nullable|string',
            'plazo_dias'=>'nullable|integer|min:0',
            'lista_precios'=>'nullable|string',
            'estado'=>'required|in:activo,bloqueado',
            'notas'=>'nullable|string',
            'eds_ids'=>'array'
        ]);
        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);
        $cliente->update($data);
        $cliente->eds()->sync($eds_ids);
        return redirect()->route('clientes.index')->with('ok','Cliente actualizado');
    }

    public function destroy(Cliente $cliente) {
        $cliente->delete();
        return back()->with('ok','Cliente eliminado');
    }
}

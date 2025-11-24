<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request) 
    {
        // 1. Filtros
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'activo'); // Por defecto 'activo'

        // 2. Consulta
        $clientes = Cliente::query()
            ->withCount('eds') // Para mostrar en la tabla cuántas EDS tiene asignadas
            // Filtro de Estado
            ->when($status === 'activo', fn($q) => $q->where('estado', 'activo'))
            ->when($status === 'bloqueado', fn($q) => $q->where('estado', 'bloqueado'))
            
            // Buscador
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('documento', 'like', $term)
                      ->orWhere('razon_social', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // 3. Respuesta AJAX (Partial)
        if ($request->ajax()) {
            return view('clientes.partials.table', compact('clientes'))->render();
        }

        return view('clientes.index', compact('clientes'));
    }

    public function create() {
        $eds = EDS::where('activo', true)->orderBy('nombre')->get(); // Solo EDS activas
        return view('clientes.create', compact('eds'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'tipo_id'     => 'required|string|max:5',
            'documento'   => 'required|string|unique:clientes,documento',
            'razon_social'=> 'required|string',
            'email'       => 'nullable|email',
            'telefono'    => 'nullable|string',
            'direccion'   => 'nullable|string',
            'estado'      => 'required|in:activo,bloqueado',
            'notas'       => 'nullable|string',
            'eds_ids'     => 'array'
        ]);

        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);

        $cliente = Cliente::create($data);
        $cliente->eds()->sync($eds_ids);

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado correctamente.');
    }

    public function edit(Cliente $cliente) {
        $eds = EDS::where('activo', true)->orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'eds'));
    }

    public function update(Request $r, Cliente $cliente) {
        $data = $r->validate([
            'tipo_id'     => 'required|string|max:5',
            'documento'   => 'required|string|unique:clientes,documento,'.$cliente->id,
            'razon_social'=> 'required|string',
            'email'       => 'nullable|email',
            'telefono'    => 'nullable|string',
            'direccion'   => 'nullable|string',
            'estado'      => 'required|in:activo,bloqueado',
            'notas'       => 'nullable|string',
            'eds_ids'     => 'array'
        ]);

        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);

        $cliente->update($data);
        $cliente->eds()->sync($eds_ids);

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente) {
        // Borrado Lógico: Cambiar estado a 'bloqueado'
        $cliente->update(['estado' => 'bloqueado']);
        
        return back()->with('ok', 'El cliente ha sido bloqueado. Puedes encontrarlo en la pestaña de Bloqueados.');
    }
}
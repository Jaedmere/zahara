<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EDS;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request) 
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'activo');

        // CONSULTA ULTRA-LIGERA
        // Solo traemos los campos que la tabla realmente muestra
        $clientes = Cliente::query()
            ->select('id', 'tipo_id', 'documento', 'razon_social', 'email', 'telefono', 'direccion', 'estado')
            ->withCount('eds') // Cuenta eficiente en SQL, no en PHP
            
            // Filtros Condicionales
            ->when($status === 'activo', fn($q) => $q->where('estado', 'activo'))
            ->when($status === 'bloqueado', fn($q) => $q->where('estado', 'bloqueado'))
            
            // Búsqueda Indexada (Asumiendo que tienes índices en DB)
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('documento', 'like', $term)
                      ->orWhere('razon_social', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15) // Paginación pequeña para carga rápida móvil
            ->withQueryString();

        if ($request->ajax()) {
            return view('clientes.partials.table', compact('clientes'))->render();
        }

        return view('clientes.index', compact('clientes'));
    }

    public function create() {
        // Traemos solo id, nombre y codigo para el selector (menos datos = más velocidad)
        $eds = EDS::where('activo', true)
            ->select('id', 'nombre', 'codigo')
            ->orderBy('nombre')
            ->get();
            
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
        
        // attach es más rápido que sync si es creación nueva
        if (!empty($eds_ids)) {
            $cliente->eds()->attach($eds_ids);
        }

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado correctamente.');
    }

    public function edit(Cliente $cliente) {
        // Carga ansiosa para evitar consultas en la vista
        $cliente->load('eds:id'); 
        
        $eds = EDS::where('activo', true)
            ->select('id', 'nombre', 'codigo')
            ->orderBy('nombre')
            ->get();
            
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
        $cliente->update(['estado' => 'bloqueado']);
        return back()->with('ok', 'Cliente bloqueado exitosamente.');
    }
}
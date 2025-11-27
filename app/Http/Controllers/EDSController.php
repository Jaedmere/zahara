<?php

namespace App\Http\Controllers;

use App\Models\EDS;
use Illuminate\Http\Request;

class EDSController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'active');

        // CONSULTA OPTIMIZADA
        $eds = EDS::query()
            // Traemos solo columnas usadas en la vista (Cards/Tabla)
            ->select('id', 'codigo', 'nombre', 'nit', 'ciudad', 'activo', 'telefono', 'direccion') 
            
            // Filtro Estado
            ->when($status === 'active', fn($q) => $q->where('activo', true))
            ->when($status === 'inactive', fn($q) => $q->where('activo', false))

            // Buscador Indexado
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('codigo', 'like', $term)
                      ->orWhere('nombre', 'like', $term)
                      ->orWhere('nit', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('eds.partials.table', compact('eds'))->render();
        }

        return view('eds.index', compact('eds'));
    }

    public function create()
    {
        return view('eds.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'        => 'required|string|unique:eds,codigo',
            'nombre'        => 'required|string',
            'nit'           => 'nullable|string',
            'ciudad'        => 'nullable|string',
            'email_alertas' => 'nullable|email',
            'direccion'     => 'nullable|string',
            'telefono'      => 'nullable|string',
            'activo'        => 'boolean',
        ]);

        EDS::create($data);

        return redirect()->route('eds.index')->with('ok', 'Estación creada exitosamente.');
    }

    public function edit(EDS $ed)
    {
        return view('eds.edit', compact('ed'));
    }

    public function update(Request $request, EDS $ed)
    {
        $data = $request->validate([
            'codigo'        => 'required|string|unique:eds,codigo,' . $ed->id,
            'nombre'        => 'required|string',
            'nit'           => 'nullable|string',
            'ciudad'        => 'nullable|string',
            'email_alertas' => 'nullable|email',
            'direccion'     => 'nullable|string',
            'telefono'      => 'nullable|string',
            'activo'        => 'boolean',
        ]);

        if (!$request->has('activo')) {
            $data['activo'] = false;
        }

        $ed->update($data);

        return redirect()->route('eds.index')->with('ok', 'Estación actualizada correctamente.');
    }

    public function destroy(EDS $ed)
    {
        $ed->update(['activo' => false]);
        return back()->with('ok', 'La EDS ha sido desactivada.');
    }
}
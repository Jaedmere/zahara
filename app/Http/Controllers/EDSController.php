<?php

namespace App\Http\Controllers;

use App\Models\EDS;
use Illuminate\Http\Request;

class EDSController extends Controller
{
    /**
     * Muestra el listado de EDS con filtros de estado y búsqueda.
     */
    public function index(Request $request)
    {
        // 1. Capturamos los filtros
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'active'); // Por defecto 'active'

        // 2. Construimos la consulta
        $eds = EDS::query()
            ->select('id', 'codigo', 'nombre', 'nit', 'ciudad', 'activo')
            
            // Filtro de Estado (Lógica de pestañas)
            ->when($status === 'active', function ($q) {
                $q->where('activo', true);
            })
            ->when($status === 'inactive', function ($q) {
                $q->where('activo', false);
            })

            // Filtro de Búsqueda (Buscador)
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('codigo', 'like', $term)
                      ->orWhere('nombre', 'like', $term)
                      ->orWhere('nit', 'like', $term);
                });
            })
            ->latest() // Ordenar por creación descendente
            ->paginate(15)
            ->withQueryString(); // Mantener filtros en paginación

        // 3. Respuesta AJAX (Para la búsqueda en vivo estilo 'Zahara')
        if ($request->ajax()) {
            return view('eds.partials.table', compact('eds'))->render();
        }

        // 4. Carga inicial normal
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

        // Nota: Si usaste los Mutadores en el Modelo, el nombre se guarda como Title Case automáticamente.
        EDS::create($data);

        return redirect()
            ->route('eds.index')
            ->with('ok', 'Estación de servicio creada exitosamente.');
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

        // Si desmarcan el checkbox, el navegador no envía 'activo', 
        // así que forzamos el false si no viene en el request.
        if (!$request->has('activo')) {
            $data['activo'] = false;
        }

        $ed->update($data);

        return redirect()
            ->route('eds.index')
            ->with('ok', 'Estación actualizada correctamente.');
    }

    /**
     * DESACTIVACIÓN LÓGICA (Soft Delete Manual)
     * En lugar de borrar el registro, lo marcamos como inactivo.
     */
    public function destroy(EDS $ed)
    {
        // Cambiamos el estado a Inactivo (0)
        $ed->update(['activo' => false]);

        return back()->with('ok', 'La EDS ha sido desactivada. Puedes verla en la pestaña "Inactivas".');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Definimos los permisos disponibles en el sistema
    private const PERMISOS = [
        'usuarios' => 'Gestión de Usuarios',
        'eds'      => 'Gestión de EDS',
        'clientes' => 'Gestión de Clientes',
        'facturas' => 'Facturación',
        'informes' => 'Ver Informes',
    ];

    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $roles = Role::query()
            ->withCount('users')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('roles.partials.table', compact('roles'))->render();
        }

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create', ['permisos' => self::PERMISOS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:50|unique:roles,nombre',
            'permisos' => 'nullable|array', // Recibimos array de checkbox
        ]);

        // Guardamos en la columna correcta mapeando 'permisos' a 'permisos_json'
        Role::create([
            'nombre' => $data['nombre'],
            'permisos_json' => $data['permisos'] ?? []
        ]);

        return redirect()->route('roles.index')->with('ok', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', [
            'role' => $role,
            'permisos' => self::PERMISOS
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:50|unique:roles,nombre,' . $role->id,
            'permisos' => 'nullable|array',
        ]);

        $role->update([
            'nombre' => $data['nombre'],
            'permisos_json' => $data['permisos'] ?? []
        ]);

        return redirect()->route('roles.index')->with('ok', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->withErrors(['msg' => 'No se puede eliminar porque tiene usuarios asignados.']);
        }

        $role->delete();

        return back()->with('ok', 'Rol eliminado correctamente.');
    }
}
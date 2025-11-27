<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role; // Asumiendo que tienes este modelo
use App\Models\EDS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->input('status', 'activo');

        $users = User::query()
            ->select('id', 'name', 'email', 'rol_id', 'activo', 'created_at')
            ->with(['role:id,nombre', 'eds:id']) // Eager loading optimizado (asumiendo campo 'nombre' en rol)
            ->withCount('eds')
            
            // Filtro Estado
            ->when($status === 'activo', fn($q) => $q->where('activo', true))
            ->when($status === 'inactivo', fn($q) => $q->where('activo', false))

            // Buscador
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('users.partials.table', compact('users'))->render();
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Traemos datos ligeros para los selectores
        $roles = Role::select('id', 'nombre')->orderBy('nombre')->get();
        $eds   = EDS::where('activo', true)->select('id', 'nombre', 'codigo')->orderBy('nombre')->get();
        
        return view('users.create', compact('roles', 'eds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'rol_id'   => 'required|exists:roles,id',
            'activo'   => 'boolean',
            'eds_ids'  => 'array',     // Array de IDs de estaciones
            'eds_ids.*'=> 'exists:eds,id'
        ]);

        // Encriptar password
        $data['password'] = Hash::make($data['password']);
        
        // Separar relaciones
        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);

        // Crear User
        $user = User::create($data);
        
        // Asignar EDS (Pivot)
        if (!empty($eds_ids)) {
            $user->eds()->sync($eds_ids);
        }

        return redirect()->route('users.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $user->load('eds:id'); // Cargar IDs para el checkbox
        $roles = Role::select('id', 'nombre')->orderBy('nombre')->get();
        $eds   = EDS::where('activo', true)->select('id', 'nombre', 'codigo')->orderBy('nombre')->get();

        return view('users.edit', compact('user', 'roles', 'eds'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8', // Opcional al editar
            'rol_id'   => 'required|exists:roles,id',
            'activo'   => 'boolean',
            'eds_ids'  => 'array'
        ]);

        // Gestión inteligente de password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // No la tocamos si viene vacía
        }

        if (!$request->has('activo')) {
            $data['activo'] = false;
        }

        $eds_ids = $data['eds_ids'] ?? [];
        unset($data['eds_ids']);

        $user->update($data);
        $user->eds()->sync($eds_ids);

        return redirect()->route('users.index')->with('ok', 'Usuario actualizado correctamente.');
    }

    /**
     * INACTIVACIÓN (Soft Delete Lógico)
     */
    public function destroy(User $user)
    {
        // Evitar auto-bloqueo (opcional pero recomendado)
        if ($user->id === auth()->id()) {
            return back()->withErrors(['msg' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->update(['activo' => false]);
        return back()->with('ok', 'Usuario desactivado. El historial se conserva intacto.');
    }
}
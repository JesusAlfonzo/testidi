<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreUpdateUserRequest;

class UserController extends Controller
{
    // 🎯 Constructor: Aplicar el Middleware de Permisos
    public function __construct()
    {
        // Esta línea asegura que el usuario debe tener el permiso específico para cada acción
        $this->middleware('permission:usuarios_ver')->only('index', 'show');
        $this->middleware('permission:usuarios_crear')->only('create', 'store');
        $this->middleware('permission:usuarios_editar')->only('edit', 'update');
        $this->middleware('permission:usuarios_eliminar')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        // Obtiene todos los usuarios paginados, excluyendo al Super-Admin (por seguridad)
        $query = User::with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Super-Admin');
            });

        if ($request->get('view_all') === 'true') {
            $users = $query->paginate($query->count())->appends($request->except('page'));
        } else {
            $users = $query->paginate($perPage);
        }

        return view('admin.users.index', compact('users', 'perPage'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Super-Admin')->pluck('name', 'id');

        // La vista es 'admin.users.create'
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario.
     */
    public function store(StoreUpdateUserRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // 🎯 CORRECCIÓN CLAVE: Buscar el objeto Role por ID.
        // El ID viene del campo 'role_id' del formulario.
        $role = Role::findById($validatedData['role_id']);

        // Asignar el rol seleccionado (pasando el objeto Role)
        if ($role) {
            $user->syncRoles($role);
        } else {
             // Opcional: registrar o manejar si el rol no existe (aunque el Request lo impide)
        }

        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario creado y rol asignado con éxito.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(User $user)
    {
        // Seguridad: Evitar que se edite a sí mismo o a otro Super-Admin
        if ($user->hasRole('Super-Admin')) {
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No puedes editar al Super Administrador desde esta interfaz.');
        }

        $roles = Role::where('name', '!=', 'Super-Admin')->pluck('name', 'id');

        // Obtener el ID del rol actual
        $currentRole = $user->roles->first() ? $user->roles->first()->id : null;

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Actualiza el usuario.
     */
    public function update(StoreUpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        // 1. Actualizar datos básicos (sin cambios)
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }
        $user->save();

        // 🎯 CORRECCIÓN CLAVE:
        // 2. Actualizar rol: Busca el Rol por el ID y se lo pasamos al método.
        // Usamos first() o find() ya que role_id viene del select (es un ID)
        $role = Role::findById($validatedData['role_id']);

        if ($role) {
            $user->syncRoles($role); // syncRoles acepta el objeto Role o su nombre/ID en un array
        }
        // Nota: Si el rol_id es requerido por el request, siempre existirá.

        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario y rol actualizados con éxito.');
    }

    /**
     * Elimina el usuario.
     */
    public function destroy(User $user)
    {
        // Seguridad: Prohibir la eliminación del Super-Admin
        if ($user->hasRole('Super-Admin')) {
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No puedes eliminar al Super Administrador.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario eliminado con éxito.');
    }
}

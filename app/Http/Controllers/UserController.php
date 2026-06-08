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

        $query = User::with('roles');

        // Restringir visualización de Superadmin a no-Superadmins
        if (!auth()->user()->hasRole('Superadmin')) {
            $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Superadmin');
            });
        }

        // Filtro por Rol
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        // Filtro por Estado
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'active' || $request->is_active == 1);
        }

        $users = $query->paginate($perPage)->appends($request->all());

        // Obtener roles para el selector de filtros
        if (auth()->user()->hasRole('Superadmin')) {
            $roles = Role::pluck('name', 'id');
        } else {
            $roles = Role::where('name', '!=', 'Superadmin')->pluck('name', 'id');
        }

        return view('admin.users.index', compact('users', 'perPage', 'roles'));
    }

    /**
     * Muestra el perfil o detalle del usuario.
     */
    public function show(User $user)
    {
        if ($user->hasRole('Superadmin') && !auth()->user()->hasRole('Superadmin')) {
            abort(403, 'Acción no autorizada.');
        }

        $user->load('roles');
        
        // Cargar solicitudes de inventario asociadas
        $requests = \App\Models\InventoryRequest::where('requester_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.users.show', compact('user', 'requests'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Superadmin')->pluck('name', 'id');

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
            'is_active' => $validatedData['is_active'] ?? true,
        ]);

        $role = Role::findById($validatedData['role_id']);

        if ($role) {
            $user->syncRoles($role);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario creado y rol asignado con éxito.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(User $user)
    {
        if ($user->hasRole('Superadmin')) {
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No puedes editar al Superadmin desde esta interfaz.');
        }

        $roles = Role::where('name', '!=', 'Superadmin')->pluck('name', 'id');
        $currentRole = $user->roles->first() ? $user->roles->first()->id : null;

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Actualiza el usuario.
     */
    public function update(StoreUpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->is_active = $validatedData['is_active'] ?? true;

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }
        $user->save();

        $role = Role::findById($validatedData['role_id']);

        if ($role) {
            $user->syncRoles($role);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario y rol actualizados con éxito.');
    }

    /**
     * Elimina el usuario.
     */
    public function destroy(User $user)
    {
        // Seguridad: Prohibir la auto-eliminación
        if ($user->id === auth()->id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '🛑 No puedes eliminarte a ti mismo.'
                ], 422);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No puedes eliminarte a ti mismo.');
        }

        // Seguridad: Prohibir la eliminación del Superadmin
        if ($user->hasRole('Superadmin')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '🛑 No puedes eliminar al Superadmin.'
                ], 422);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No puedes eliminar al Superadmin.');
        }

        // Verificar datos asociados
        $requestsCount = \App\Models\InventoryRequest::where('requester_id', $user->id)->count();
        if ($requestsCount > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '🛑 No se puede eliminar el usuario porque tiene ' . $requestsCount . ' solicitud(es) asociada(s).'
                ], 422);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', '🛑 No se puede eliminar el usuario porque tiene ' . $requestsCount . ' solicitud(es) asociada(s).');
        }

        $user->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado con éxito.'
            ]);
        }
        return redirect()->route('admin.users.index')
                         ->with('success', '✅ Usuario eliminado con éxito.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles_ver')->only(['index', 'show']);
        $this->middleware('permission:roles_crear')->only(['create', 'store']);
        $this->middleware('permission:roles_editar')->only(['edit', 'update']);
        $this->middleware('permission:roles_eliminar')->only('destroy');
    }

    public function index()
    {
        // 🎯 Carga optimizada con withCount para evitar consulta N+1 al contar permisos
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'other';
        });

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(StoreUpdateRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        // Sincronizar permisos si fueron seleccionados
        $role->syncPermissions($request->get('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', '✅ Rol creado correctamente.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $users = User::role($role->name)->get();

        return view('admin.roles.show', compact('role', 'users'));
    }

    public function edit(Role $role)
    {
        // Seguridad: El rol Superadmin no debe ser editado desde la interfaz general
        if ($role->name === 'Superadmin' || $role->id === 1) {
            return redirect()->route('admin.roles.index')
                ->with('error', '🛑 El rol Superadmin no puede ser modificado.');
        }

        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'other';
        });

        $selectedPermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'selectedPermissions'));
    }

    public function update(StoreUpdateRoleRequest $request, Role $role)
    {
        // Seguridad: Prohibir modificaciones al rol Superadmin
        if ($role->name === 'Superadmin' || $role->id === 1) {
            return redirect()->route('admin.roles.index')
                ->with('error', '🛑 No se puede modificar el rol Superadmin.');
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->get('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', '✅ Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        // Seguridad: Impedir la eliminación del rol Superadmin
        if ($role->name === 'Superadmin' || $role->id === 1) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '🛑 No se puede eliminar el rol Superadmin.'
                ], 422);
            }
            return redirect()->route('admin.roles.index')
                ->with('error', '🛑 No se puede eliminar el rol Superadmin.');
        }

        // Seguridad: Impedir eliminación si hay usuarios con este rol asignado
        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "🛑 No se puede eliminar el rol porque tiene {$usersWithRole} usuario(s) asignado(s)."
                ], 422);
            }
            return redirect()->route('admin.roles.index')
                ->with('error', "🛑 No se puede eliminar el rol porque tiene {$usersWithRole} usuario(s) asignado(s).");
        }

        $role->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Rol eliminado con éxito.'
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', '✅ Rol eliminado correctamente.');
    }
}

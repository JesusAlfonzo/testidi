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
        $roles = Role::with(['permissions', 'users'])->orderBy('name')->get();
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

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $users = User::role($role->name)->get();

        return view('admin.roles.show', compact('role', 'users'));
    }

    public function edit(Role $role)
    {
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
        if ($role->name === 'Superadmin') {
            return redirect()->back()->with('error', 'No se puede modificar el rol Superadmin.');
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Superadmin') {
            return redirect()->back()->with('error', 'No se puede eliminar el rol Superadmin.');
        }

        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return redirect()->back()->with('error', "No se puede eliminar el rol. Tiene {$usersWithRole} usuario(s) asignado(s).");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}

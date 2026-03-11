<?php

namespace App\Policies;

use App\Models\InventoryRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('solicitudes_ver');
    }

    public function view(User $user, InventoryRequest $inventoryRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('solicitudes_ver');
        }
        return $user->can('solicitudes_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('solicitudes_crear');
    }

    public function update(User $user, InventoryRequest $inventoryRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('solicitudes_editar');
        }
        if ($inventoryRequest->status !== 'Pending') {
            return false;
        }
        return $user->can('solicitudes_crear');
    }

    public function delete(User $user, InventoryRequest $inventoryRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('solicitudes_eliminar');
        }
        if ($inventoryRequest->status !== 'Pending') {
            return false;
        }
        return $user->can('solicitudes_crear');
    }

    public function process(User $user, InventoryRequest $inventoryRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('solicitudes_aprobar');
        }
        return $user->can('solicitudes_aprobar');
    }
}

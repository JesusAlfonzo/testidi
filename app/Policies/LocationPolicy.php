<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ubicaciones_ver');
    }

    public function view(User $user, Location $location): bool
    {
        return $user->can('ubicaciones_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ubicaciones_crear');
    }

    public function update(User $user, Location $location): bool
    {
        return $user->can('ubicaciones_editar');
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->can('ubicaciones_eliminar');
    }
}

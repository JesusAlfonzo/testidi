<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('marcas_ver');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('marcas_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('marcas_crear');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('marcas_editar');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('marcas_eliminar');
    }
}

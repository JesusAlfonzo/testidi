<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('productos_ver');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('productos_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('productos_crear');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('productos_editar');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('productos_eliminar');
    }
}

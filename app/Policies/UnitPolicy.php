<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('unidades_ver');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->can('unidades_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('unidades_crear');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->can('unidades_editar');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('unidades_eliminar');
    }
}

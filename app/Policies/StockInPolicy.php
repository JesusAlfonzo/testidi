<?php

namespace App\Policies;

use App\Models\StockIn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockInPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('entradas_ver');
    }

    public function view(User $user, StockIn $stockIn): bool
    {
        return $user->can('entradas_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('entradas_crear');
    }

    public function update(User $user, StockIn $stockIn): bool
    {
        return $user->can('entradas_editar');
    }

    public function delete(User $user, StockIn $stockIn): bool
    {
        return $user->can('entradas_eliminar');
    }
}

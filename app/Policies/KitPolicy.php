<?php

namespace App\Policies;

use App\Models\Kit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KitPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('kits_ver');
    }

    public function view(User $user, Kit $kit): bool
    {
        return $user->can('kits_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('kits_crear');
    }

    public function update(User $user, Kit $kit): bool
    {
        return $user->can('kits_editar');
    }

    public function delete(User $user, Kit $kit): bool
    {
        return $user->can('kits_eliminar');
    }
}

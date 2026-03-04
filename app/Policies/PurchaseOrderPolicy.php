<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ordenes_compra_ver');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ordenes_compra_crear');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_editar');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_eliminar');
    }

    public function issue(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_aprobar');
    }

    public function complete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_aprobar');
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('ordenes_compra_anular');
    }
}

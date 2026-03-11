<?php

namespace App\Policies;

use App\Models\PurchaseQuote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseQuotePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('cotizaciones_ver');
    }

    public function view(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_ver');
        }
        return $user->can('cotizaciones_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('cotizaciones_crear');
    }

    public function update(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_editar');
        }
        return $user->can('cotizaciones_editar');
    }

    public function delete(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_eliminar');
        }
        return $user->can('cotizaciones_eliminar');
    }

    public function select(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_aprobar');
        }
        return $user->can('cotizaciones_aprobar');
    }

    public function approve(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_aprobar');
        }
        return $user->can('cotizaciones_aprobar');
    }

    public function reject(User $user, PurchaseQuote $purchaseQuote): bool
    {
        if ($user->isSuperAdmin()) {
            return $user->can('cotizaciones_rechazar');
        }
        return $user->can('cotizaciones_rechazar');
    }
}

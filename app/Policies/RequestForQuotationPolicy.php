<?php

namespace App\Policies;

use App\Models\RequestForQuotation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestForQuotationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('rfq_ver');
    }

    public function view(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_ver');
    }

    public function create(User $user): bool
    {
        return $user->can('rfq_crear');
    }

    public function update(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_editar');
    }

    public function delete(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_eliminar');
    }

    public function markAsSent(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_enviar');
    }

    public function markAsClosed(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_enviar');
    }

    public function cancel(User $user, RequestForQuotation $rfq): bool
    {
        return $user->can('rfq_enviar');
    }
}

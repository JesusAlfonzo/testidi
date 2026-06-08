<?php
$user = auth()->user();
$actions = '';

$actions .= '<a href="' . route('admin.requests.show', $item->id) . '" class="btn btn-default text-info btn-sm" title="Ver detalles"><i class="fas fa-eye"></i></a> ';

$actions .= '<a href="' . route('admin.requests.pdf', $item->id) . '" class="btn btn-default text-secondary btn-sm" title="Ver PDF" target="_blank"><i class="fas fa-file-pdf"></i></a> ';

if ($item->status === 'Pending' && ($user->can('solicitudes_aprobar') || $user->isSuperAdmin())) {
    $actions .= '<button type="button" class="btn btn-default text-success btn-sm btn-approve-request" title="Aprobar" data-id="' . $item->id . '" data-url="' . route('admin.requests.approve', $item->id) . '"><i class="fas fa-check"></i></button> ';
    $actions .= '<button type="button" class="btn btn-default text-danger btn-sm btn-reject-request" title="Rechazar" data-id="' . $item->id . '" data-url="' . route('admin.requests.reject', $item->id) . '"><i class="fas fa-times"></i></button>';
}

echo $actions;

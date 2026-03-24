<?php
$user = auth()->user();
$actions = '';

$actions .= '<a href="' . route('admin.requests.show', $item->id) . '" class="btn btn-sm btn-info" title="Ver detalles"><i class="fas fa-eye"></i></a> ';

$actions .= '<a href="' . route('admin.requests.pdf', $item->id) . '" class="btn btn-sm btn-secondary" title="Ver PDF" target="_blank"><i class="fas fa-file-pdf"></i></a> ';

if ($item->status === 'Pending' && ($user->can('solicitudes_aprobar') || $user->isSuperAdmin())) {
    $processUrl = route('admin.requests.process', $item->id);
    $csrf = csrf_token();
    
    $actions .= '<form method="POST" action="' . $processUrl . '" style="display:inline;">';
    $actions .= '<input type="hidden" name="_token" value="' . $csrf . '">';
    $actions .= '<input type="hidden" name="action" value="approve">';
    $actions .= '<button type="submit" class="btn btn-sm btn-success" title="Aprobar" onclick="return confirm(\'¿Está seguro de APROBAR esta solicitud?\');">';
    $actions .= '<i class="fas fa-check"></i></button></form> ';

    $actions .= '<form method="POST" action="' . $processUrl . '" style="display:inline;">';
    $actions .= '<input type="hidden" name="_token" value="' . $csrf . '">';
    $actions .= '<input type="hidden" name="action" value="reject">';
    $actions .= '<button type="submit" class="btn btn-sm btn-danger" title="Rechazar" onclick="return confirm(\'¿Está seguro de RECHAZAR esta solicitud?\');">';
    $actions .= '<i class="fas fa-times"></i></button></form>';
}

echo $actions;

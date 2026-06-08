@extends('adminlte::page')

@section('title', 'Usuario | ' . $user->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-shield text-primary mr-2"></i> Perfil del Usuario
            </h1>
            <p class="text-muted mb-0">Detalle de registro, permisos activos e historial de actividad del usuario.</p>
        </div>
        <div class="d-flex">
            @can('usuarios_editar')
                @if(!$user->hasRole('Superadmin'))
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning font-weight-bold px-3 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                @endif
            @endcan
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-3" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Tarjeta de Perfil Documental --}}
            <div class="card p-5 bg-white shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                
                {{-- Encabezado del Perfil --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle text-white d-flex align-items-center justify-content-center mr-3 font-weight-bold shadow-sm" 
                             style="width: 60px; height: 60px; font-size: 1.5rem; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <span class="text-uppercase text-xs font-weight-bold text-secondary tracking-wider" style="letter-spacing: 0.5px;">Ficha del Usuario</span>
                            <h3 class="font-weight-bold text-dark mb-1">{{ $user->name }}</h3>
                            <p class="text-muted mb-0"><i class="fas fa-envelope text-muted mr-1"></i> <a href="mailto:{{ $user->email }}" class="text-dark">{{ $user->email }}</a></p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($user->is_active)
                            <span class="badge px-3 py-2" style="font-size: 0.85rem; border-radius: 20px; background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;"><i class="fas fa-check-circle mr-1"></i> ACTIVO</span>
                        @else
                            <span class="badge px-3 py-2" style="font-size: 0.85rem; border-radius: 20px; background-color: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important;"><i class="fas fa-times-circle mr-1"></i> INACTIVO</span>
                        @endif
                        <p class="text-xs text-muted mt-2 mb-0">ID Usuario: #{{ $user->id }}</p>
                    </div>
                </div>

                {{-- Detalles del Usuario en Columnas --}}
                <div class="row mb-4">
                    {{-- Columna 1: Información de Registro --}}
                    <div class="col-md-6 border-right">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-id-card text-info mr-2"></i> Datos de Acceso
                        </h6>
                        <table class="table table-borderless table-sm" style="font-size: 0.875rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width: 40%;">Nombre Completo:</td>
                                <td class="font-weight-bold text-dark">{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Correo de Acceso:</td>
                                <td class="text-dark">{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Rol Asignado:</td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge badge-dark" style="background-color: #f3f4f6 !important; color: #374151 !important; border: 1px solid #e5e7eb !important;">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">Ningún rol</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Registrado en:</td>
                                <td class="text-dark">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Última Modificación:</td>
                                <td class="text-dark">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Columna 2: Permisos del Sistema --}}
                    <div class="col-md-6 pl-md-4">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-shield-alt text-info mr-2"></i> Permisos de Sistema Otorgados
                        </h6>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            @forelse($user->getAllPermissions() as $permission)
                                <span class="badge text-muted border bg-light font-weight-normal" 
                                      style="padding: 0.45em 0.85em; font-size: 0.75rem; border-radius: 6px;">
                                    <i class="fas fa-check text-success mr-1"></i> {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-muted text-sm">Este rol no posee permisos específicos.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <hr style="border-top: 1px solid #e5e7eb; margin: 2.5rem 0;">

                {{-- Sección de Actividad Reciente (Solicitudes de Salida) --}}
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-exchange-alt text-danger mr-2"></i> Actividad de Salidas / Despachos Recientes (Últimas 5)
                        </h6>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" style="font-size: 0.875rem;">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="border-0 rounded-left py-2" style="width: 15%">Código</th>
                                        <th class="border-0 py-2" style="width: 20%">Departamento</th>
                                        <th class="border-0 py-2" style="width: 35%">Justificación</th>
                                        <th class="border-0 py-2" style="width: 10%">Prioridad</th>
                                        <th class="border-0 py-2" style="width: 10%">Estado</th>
                                        <th class="border-0 rounded-right py-2 text-right" style="width: 10%">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                        @php
                                            $priorityLabel = 'Baja';
                                            $priorityClass = 'secondary';
                                            $displayJustification = $req->justification;

                                            if (preg_match('/^\[(ALTA|MEDIA|BAJA)\]\s*(.*)$/i', $req->justification, $matches)) {
                                                $priorityVal = strtolower($matches[1]);
                                                $displayJustification = $matches[2];
                                                if ($priorityVal === 'alta') {
                                                    $priorityLabel = 'Alta';
                                                    $priorityClass = 'danger';
                                                } elseif ($priorityVal === 'media') {
                                                    $priorityLabel = 'Media';
                                                    $priorityClass = 'info';
                                                }
                                            }

                                            $statusLabel = '';
                                            $statusClass = 'secondary';
                                            if ($req->status === 'Pending') {
                                                $statusLabel = 'Pendiente';
                                                $statusClass = 'warning';
                                            } elseif ($req->status === 'Approved') {
                                                $statusLabel = 'Aprobada';
                                                $statusClass = 'success';
                                            } elseif ($req->status === 'Rejected') {
                                                $statusLabel = 'Rechazada';
                                                $statusClass = 'danger';
                                            } elseif ($req->status === 'Draft') {
                                                $statusLabel = 'Borrador';
                                                $statusClass = 'secondary';
                                            } else {
                                                $statusLabel = $req->status;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="py-2">
                                                <a href="{{ route('admin.requests.show', $req) }}" class="font-weight-bold text-primary">#REQ-{{ $req->id }}</a>
                                            </td>
                                            <td class="py-2 text-muted">{{ $req->destination_area ?? 'N/A' }}</td>
                                            <td class="py-2" title="{{ $req->justification }}">{{ Str::limit($displayJustification, 60) }}</td>
                                            <td class="py-2">
                                                @if($priorityClass === 'danger')
                                                    <span class="badge badge-danger" style="background-color: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important;">{{ $priorityLabel }}</span>
                                                @elseif($priorityClass === 'info')
                                                    <span class="badge badge-info" style="background-color: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important;">{{ $priorityLabel }}</span>
                                                @else
                                                    <span class="badge badge-dark" style="background-color: #f3f4f6 !important; color: #374151 !important; border: 1px solid #e5e7eb !important;">{{ $priorityLabel }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                @if($statusClass === 'success')
                                                    <span class="badge badge-success" style="background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;">{{ $statusLabel }}</span>
                                                @elseif($statusClass === 'warning')
                                                    <span class="badge badge-warning" style="background-color: #fef9c3 !important; color: #a16207 !important; border: 1px solid #fef08a !important;">{{ $statusLabel }}</span>
                                                @elseif($statusClass === 'danger')
                                                    <span class="badge badge-danger" style="background-color: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important;">{{ $statusLabel }}</span>
                                                @else
                                                    <span class="badge badge-dark" style="background-color: #f3f4f6 !important; color: #374151 !important; border: 1px solid #e5e7eb !important;">{{ $statusLabel }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-right text-muted" style="font-size: 0.8rem;">{{ $req->requested_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No hay solicitudes de salida registradas por este usuario.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

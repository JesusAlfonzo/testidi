@extends('adminlte::page')

@section('title', 'Mi Panel')

@section('css')
<style>
    .kpi-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .kpi-value {
        font-size: 28px;
        font-weight: 700;
    }
    .kpi-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6c757d;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .quick-action-btn {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        border-color: #1a4a7a;
    }
    .quick-action-icon {
        font-size: 28px;
        margin-bottom: 8px;
    }
    .quick-action-label {
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-badge.approved {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.rejected {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="font-weight: 700; color: #1a4a7a;">
                <i class="fas fa-hand-paper mr-2"></i>Hola, {{ Auth::user()->name }}
            </h2>
            <p class="text-muted mb-0">Panel de Solicitudes</p>
        </div>
        <div class="text-right">
            <span class="text-muted d-block">Última actualización</span>
            <span class="text-info font-weight-bold">{{ now()->format('d M, Y - H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
{{-- KPIs PRINCIPALES --}}
<div class="row">
    <div class="col-md-4 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Pendientes</div>
                        <div class="kpi-value" style="color: #ffc107;">{{ $myPendingCount }}</div>
                        <div class="text-muted" style="font-size: 12px;">Esperando aprobación</div>
                    </div>
                    <div class="kpi-icon" style="background: #fff3cd; color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}?status=Pending" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver pendientes <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Aprobadas</div>
                        <div class="kpi-value" style="color: #28a745;">{{ $myApprovedCount }}</div>
                        <div class="text-muted" style="font-size: 12px;">Historial aprobado</div>
                    </div>
                    <div class="kpi-icon" style="background: #d4edda; color: #28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}?status=Approved" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver historial <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-4 col-sm-12">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Rechazadas</div>
                        <div class="kpi-value" style="color: #dc3545;">{{ $myRejectedCount }}</div>
                        <div class="text-muted" style="font-size: 12px;">Revisa los motivos</div>
                    </div>
                    <div class="kpi-icon" style="background: #f8d7da; color: #dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}?status=Rejected" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver rechazos <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

{{-- ACCIONES RÁPIDAS --}}
<div class="row mt-3">
    <div class="col-6 col-md-4 offset-md-2">
        <a href="{{ route('admin.requests.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #1a4a7a;"><i class="fas fa-plus-circle"></i></div>
            <div class="quick-action-label">Nueva Solicitud</div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.requests.index') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #6c757d;"><i class="fas fa-list"></i></div>
            <div class="quick-action-label">Mis Solicitudes</div>
        </a>
    </div>
</div>

{{-- CONTENIDO PRINCIPAL --}}
<div class="row mt-4">
    {{-- COLUMNA IZQUIERDA: CREAR SOLICITUD --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #1a4a7a;">
                <h6 class="font-weight-bold mb-0" style="color: #1a4a7a;">
                    <i class="fas fa-cart-plus mr-2"></i>¿Necesitas Insumos?
                </h6>
            </div>
            <div class="card-body text-center">
                <p class="text-muted mb-3">
                    Solicita productos para tus labores.
                </p>
                <div style="background: #e8f4fd; width: 70px; height: 70px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-box-open" style="font-size: 28px; color: #1a4a7a;"></i>
                </div>
                <br>
                <a href="{{ route('admin.requests.create') }}" class="btn btn-primary btn-block shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-plus-circle mr-2"></i> Crear Solicitud
                </a>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Justifica bien tu pedido.
                </small>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA: HISTORIAL --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #6c757d;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #6c757d;">
                        <i class="fas fa-history mr-2"></i>Tus Últimos Movimientos
                    </h6>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-outline-secondary">Ver Todos</a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-valign-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Justificación</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myRecentRequests as $req)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.requests.show', $req->id) }}" class="text-bold" style="color: #1a4a7a;">REQ-{{ $req->id }}</a>
                                </td>
                                <td>
                                    {{ $req->created_at->diffForHumans() }}
                                    <br>
                                    <small class="text-muted">{{ $req->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>{{ Str::limit($req->justification, 25) }}</td>
                                <td>
                                    @switch($req->status)
                                        @case('Pending')
                                            <span class="status-badge pending">
                                                <i class="fas fa-clock"></i> Pendiente
                                            </span>
                                            @break
                                        @case('Approved')
                                            <span class="status-badge approved">
                                                <i class="fas fa-check"></i> Aprobada
                                            </span>
                                            @break
                                        @case('Rejected')
                                            <span class="status-badge rejected">
                                                <i class="fas fa-times"></i> Rechazada
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('admin.requests.show', $req->id) }}" class="btn btn-xs btn-outline-secondary">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted mb-2">
                                        <i class="fas fa-inbox fa-2x"></i>
                                    </div>
                                    No has realizado solicitudes recientemente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

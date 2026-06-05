@extends('adminlte::page')

@section('title', 'Mi Dashboard | IAC')

@section('css')
<style>
    .action-request-card {
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .action-request-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.3);
    }
    .stat-box-mini {
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-box-mini:hover {
        transform: translateY(-2px);
    }
    .stat-number {
        font-size: 24px;
        font-weight: 700;
    }
    .stat-text {
        font-size: 11px;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 600;
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-user text-primary mr-2"></i>Mi Portal de Solicitudes</h1>
            <p class="text-muted mb-0 small">Consulte el estado de sus pedidos de almacén y gestione nuevos requerimientos.</p>
        </div>
        <div class="text-right d-none d-md-block">
            <span class="text-muted small d-block">Fecha Actual</span>
            <span class="text-primary font-weight-bold">{{ now()->format('d M, Y') }}</span>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- COLUMNA DE DETALLE (IZQUIERDA - 8/12) --}}
        <div class="col-lg-8 col-12">
            
            {{-- MIS SOLICITUDES RECIENTES --}}
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-info font-weight-bold mb-0">
                        <i class="fas fa-list-ul mr-1"></i> Estado de mis Solicitudes Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="small font-weight-bold text-muted text-uppercase">Nro. Solicitud</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Fecha Registro</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Referencia</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Estado</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRecentRequests as $req)
                                    <tr>
                                        <td class="align-middle font-weight-bold">#{{ $req->id }}</td>
                                        <td class="align-middle">{{ $req->requested_at ? $req->requested_at->format('d/m/Y H:i') : $req->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="align-middle text-muted">{{ $req->reference ?? 'Sin referencia' }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-{{ $req->status_badge_class }}">
                                                {{ $req->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('admin.requests.show', $req) }}" class="btn btn-xs btn-outline-info" title="Ver Detalle">
                                                <i class="fas fa-eye mr-1"></i> Consultar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox mb-2" style="font-size: 32px;"></i>
                                            <p class="mb-0 small">Aún no has registrado ninguna solicitud de materiales.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DE PANEL LATERAL (DERECHA - 4/12) --}}
        <div class="col-lg-4 col-12 mt-3 mt-lg-0">
            
            {{-- ACCIÓN DE CREAR SOLICITUD --}}
            <div class="action-request-card card shadow-sm text-center py-4 mb-3">
                <div class="card-body">
                    <div class="mb-3" style="font-size: 40px; opacity: 0.8;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h5 class="font-weight-bold text-white mb-2">¿Necesita Materiales?</h5>
                    <p class="text-white-50 small mb-4">Cree una nueva solicitud de salida para consumir o solicitar insumos de oficina y almacén.</p>
                    <a href="{{ route('admin.requests.create') }}" class="btn btn-light text-primary font-weight-bold btn-block py-3 shadow action-btn-dashboard" style="border-radius: 8px;">
                        <i class="fas fa-plus mr-1"></i> Crear Nueva Solicitud
                    </a>
                </div>
            </div>

            {{-- RESUMEN EN CIFRAS --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-muted small text-uppercase">Resumen de Gestión</h6>
                </div>
                <div class="card-body pt-0 pb-3">
                    <div class="row">
                        {{-- Totales --}}
                        <div class="col-6 mb-2">
                            <div class="stat-box-mini border text-dark">
                                <div class="stat-number">{{ $myRequestsCount }}</div>
                                <div class="stat-text">Totales</div>
                            </div>
                        </div>
                        {{-- Aprobadas --}}
                        <div class="col-6 mb-2">
                            <div class="stat-box-mini border text-success">
                                <div class="stat-number">{{ $myApprovedCount }}</div>
                                <div class="stat-text">Aprobadas</div>
                            </div>
                        </div>
                        {{-- Pendientes --}}
                        <div class="col-6 mb-0">
                            <div class="stat-box-mini border text-warning">
                                <div class="stat-number">{{ $myPendingCount }}</div>
                                <div class="stat-text">Pendientes</div>
                            </div>
                        </div>
                        {{-- Rechazadas --}}
                        <div class="col-6 mb-0">
                            <div class="stat-box-mini border text-danger">
                                <div class="stat-number">{{ $myRejectedCount }}</div>
                                <div class="stat-text">Rechazadas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

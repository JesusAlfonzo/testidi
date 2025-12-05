@extends('adminlte::page')

@section('title', 'Mi Panel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>👋 Hola, {{ Auth::user()->name }}</h1>
        <small class="text-muted">Panel de Solicitante</small>
    </div>
@stop

@section('content')
    
    {{-- 1. RESUMEN DE TUS SOLICITUDES (KPIs) --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            {{-- Pendientes --}}
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $myPendingCount }}</h3>
                    <p>Esperando Aprobación</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.requests.index') }}?status=Pending" class="small-box-footer">
                    Ver Pendientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            {{-- Aprobadas --}}
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $myApprovedCount }}</h3>
                    <p>Solicitudes Aprobadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.requests.index') }}?status=Approved" class="small-box-footer">
                    Historial Aprobado <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            {{-- Rechazadas --}}
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $myRejectedCount }}</h3>
                    <p>Solicitudes Rechazadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('admin.requests.index') }}?status=Rejected" class="small-box-footer">
                    Ver Motivos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        
        {{-- 2. COLUMNA IZQUIERDA: ACCIÓN PRINCIPAL --}}
        <div class="col-md-5">
            
            {{-- Tarjeta de Acción (Hero) --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cart-plus"></i> ¿Necesitas Insumos?</h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted">
                        Puedes solicitar productos individuales o kits predefinidos para tus labores.
                    </p>
                    <img src="https://cdn-icons-png.flaticon.com/512/2897/2897785.png" alt="Request Icon" style="width: 80px; opacity: 0.8;" class="mb-3">
                    <br>
                    <a href="{{ route('admin.requests.create') }}" class="btn btn-primary btn-lg btn-block shadow-sm">
                        <i class="fas fa-plus-circle mr-2"></i> Crear Nueva Solicitud
                    </a>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Recuerda justificar bien tu pedido para agilizar la aprobación.</small>
                </div>
            </div>

            {{-- Tarjeta Informativa (Estados) --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Guía de Estados</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-circle text-warning text-xs mr-2"></i> 
                            <strong>Pendiente:</strong> Tu solicitud está en revisión por Logística/Admin.
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-circle text-success text-xs mr-2"></i> 
                            <strong>Aprobada:</strong> Puedes pasar a retirar los insumos.
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-circle text-danger text-xs mr-2"></i> 
                            <strong>Rechazada:</strong> Revisa el motivo y crea una nueva si es necesario.
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        {{-- 3. COLUMNA DERECHA: HISTORIAL RECIENTE --}}
        <div class="col-md-7">
            <div class="card card-secondary card-outline">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-history"></i> Tus Últimos Movimientos</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-tool btn-sm">
                            <i class="fas fa-bars"></i> Ver Todos
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Justificación</th>
                                    <th>Estado</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRecentRequests as $req)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $req->id) }}" class="text-bold">REQ-{{ $req->id }}</a>
                                        </td>
                                        <td>
                                            {{ $req->created_at->diffForHumans() }}
                                            <br>
                                            <small class="text-muted">{{ $req->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>{{ Str::limit($req->justification, 30) }}</td>
                                        <td>
                                            @php
                                                $badge = match($req->status) { 'Pending'=>'warning', 'Approved'=>'success', 'Rejected'=>'danger' };
                                                $icon = match($req->status) { 'Pending'=>'clock', 'Approved'=>'check', 'Rejected'=>'times' };
                                                // Usamos traducción simple para la vista
                                                $label = match($req->status) { 'Pending'=>'Pendiente', 'Approved'=>'Aprobada', 'Rejected'=>'Rechazada' };
                                            @endphp
                                            <span class="badge badge-{{ $badge }}">
                                                <i class="fas fa-{{ $icon }}"></i> {{ $label }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $req->id) }}" class="text-muted">
                                                <i class="fas fa-search"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted mb-2"><i class="fas fa-inbox fa-2x"></i></div>
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
    </div>
@stop
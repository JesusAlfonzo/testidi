@extends('adminlte::page')

@section('title', 'Mi Panel')

@section('content_header')
    <h1>👋 Hola, {{ Auth::user()->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Bienvenido al sistema de gestión de insumos. Desde aquí puedes realizar nuevas solicitudes y ver el estado de las anteriores.
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Botón de Acción Principal --}}
        <div class="col-lg-3 col-6">
            <a href="{{ route('admin.requests.create') }}" class="btn btn-app bg-primary w-100" style="height: 100px; font-size: 1.2rem; padding-top: 30px;">
                <i class="fas fa-plus-circle"></i> Nueva Solicitud
            </a>
        </div>

        {{-- Resumen de Mis Solicitudes --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $myPendingCount }}" text="Mis Pendientes" theme="warning" icon="fas fa-clock"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $myApprovedCount }}" text="Aprobadas" theme="success" icon="fas fa-thumbs-up"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $myRejectedCount }}" text="Rechazadas" theme="danger" icon="fas fa-times-circle"/>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Mis Solicitudes Recientes</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead><tr><th>ID</th><th>Fecha</th><th>Justificación</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse($myRecentRequests as $req)
                                <tr>
                                    <td>REQ-{{ $req->id }}</td>
                                    <td>{{ $req->created_at->diffForHumans() }}</td>
                                    <td>{{ Str::limit($req->justification, 40) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $req->status == 'Approved' ? 'success' : ($req->status == 'Pending' ? 'warning' : 'danger') }}">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                    <td><a href="{{ route('admin.requests.show', $req->id) }}" class="btn btn-xs btn-default"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No has realizado solicitudes recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
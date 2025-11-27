@extends('adminlte::page')

@section('title', 'Dashboard General')
@section('plugins.Chartjs', true)

@section('content_header')
    <h1><i class="fas fa-tachometer-alt"></i> Visión General del Sistema</h1>
@stop

@section('content')
    {{-- 1. SALUD DEL SISTEMA (Exclusivo Superadmin) --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="far fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Usuarios</span>
                    <span class="info-box-number">{{ $usersCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-shield-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Roles</span>
                    <span class="info-box-number">{{ $rolesCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-box-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Productos</span>
                    <span class="info-box-number">{{ $totalProducts }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valorización</span>
                    <span class="info-box-number">${{ number_format($totalStockValue, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MONITOR DE OPERACIONES --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Flujo de Solicitudes (7 días)</h3>
                </div>
                <div class="card-body">
                    <canvas id="adminChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">Atención Requerida</h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-warning">
                        <h5>Solicitudes Pendientes</h5>
                        <p>Hay <strong>{{ $pendingRequests }}</strong> solicitudes esperando aprobación.</p>
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-warning">Ir a Solicitudes</a>
                    </div>
                    <div class="callout callout-danger">
                        <h5>Stock Crítico</h5>
                        <p><strong>{{ $lowStockCount }}</strong> productos requieren reabastecimiento.</p>
                        <a href="{{ route('admin.reports.stock') }}" class="btn btn-sm btn-danger">Ver Reporte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    var ctx = document.getElementById('adminChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line', // Lineal para ver tendencia
        data: {
            labels: ['Pendientes', 'Aprobadas', 'Rechazadas'], // Simplificado para el ejemplo
            datasets: [{
                label: 'Volumen',
                data: [{{ $chartPending }}, {{ $chartApproved }}, {{ $chartRejected }}],
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: false,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
@extends('adminlte::page')

@section('title', 'Dashboard General')
@section('plugins.Chartjs', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt text-dark"></i> Visión General del Sistema</h1>
        <small class="text-muted">Bienvenido, {{ Auth::user()->name }}</small>
    </div>
@stop

@section('content')
    {{-- 1. SALUD DEL SISTEMA (Infraestructura) --}}
    <h5 class="mb-2 text-secondary"><i class="fas fa-server"></i> Infraestructura y Datos</h5>
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="far fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Usuarios</span>
                    <span class="info-box-number">{{ $usersCount }}</span>
                </div>
                <a href="{{ route('admin.users.index') }}" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-shield-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Roles</span>
                    <span class="info-box-number">{{ $rolesCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-box-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Productos</span>
                    <span class="info-box-number">{{ $totalProducts }}</span>
                </div>
                <a href="{{ route('admin.products.index') }}" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valorización</span>
                    <span class="info-box-number">${{ number_format($totalStockValue, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SECCIÓN DE ACCIÓN INMEDIATA (LO NUEVO "JUGOSO") --}}
    <h5 class="mb-2 mt-3 text-secondary"><i class="fas fa-bolt"></i> Centro de Acción</h5>
    <div class="row">
        {{-- Tarjeta: Atención de Solicitudes --}}
        <div class="col-md-4">
            <div class="card card-outline card-warning h-100">
                <div class="card-header">
                    <h3 class="card-title">📋 Gestión de Solicitudes</h3>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-warning font-weight-bold">{{ $pendingRequests }}</h1>
                    <p class="lead">Solicitudes pendientes de revisión</p>
                    <p class="text-muted text-sm">Hay empleados esperando aprobación para retirar material.</p>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-block btn-warning font-weight-bold">
                        <i class="fas fa-clipboard-check mr-2"></i> Revisar y Aprobar
                    </a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: Salud de Inventario --}}
        <div class="col-md-4">
            <div class="card card-outline card-danger h-100">
                <div class="card-header">
                    <h3 class="card-title">🚨 Salud de Inventario</h3>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-danger font-weight-bold">{{ $lowStockCount }}</h1>
                    <p class="lead">Productos en nivel crítico</p>
                    <p class="text-muted text-sm">Es necesario gestionar una compra o entrada de stock urgente.</p>
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('admin.reports.stock') }}?stock_status=low" class="btn btn-block btn-outline-danger">
                                <i class="fas fa-list"></i> Ver Lista
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-block btn-danger">
                                <i class="fas fa-plus"></i> Reponer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta: Accesos Directos (App Buttons) --}}
        <div class="col-md-4">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title">🚀 Accesos Rápidos</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">¿Qué deseas hacer hoy?</p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="{{ route('admin.requests.create') }}" class="btn btn-app bg-light">
                            <i class="fas fa-hand-holding-box text-primary"></i> Solicitar
                        </a>
                        <a href="{{ route('admin.stock-in.create') }}" class="btn btn-app bg-light">
                            <i class="fas fa-truck-loading text-success"></i> Entrada
                        </a>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-app bg-light">
                            <i class="fas fa-plus-square text-info"></i> Producto
                        </a>
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-app bg-light">
                            <i class="fas fa-history text-secondary"></i> Auditoría
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-app bg-light">
                            <i class="fas fa-user-plus text-warning"></i> Usuario
                        </a>
                        <a href="{{ route('admin.reports.requests') }}" class="btn btn-app bg-light">
                            <i class="fas fa-file-pdf text-danger"></i> Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. ANÁLISIS DE DATOS (GRÁFICOS) --}}
    <h5 class="mb-2 mt-3 text-secondary"><i class="fas fa-chart-pie"></i> Análisis de Operaciones</h5>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Tendencia de Solicitudes (Última Semana)</h3>
                </div>
                <div class="card-body">
                    <canvas id="dailyRequestsLineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Estado Global de Solicitudes</h3>
                </div>
                <div class="card-body">
                    <canvas id="requestStatusDonutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(function() {
        'use strict'

        // --- GRÁFICO DE LÍNEAS ---
        var lineCtx = document.getElementById('dailyRequestsLineChart').getContext('2d');
        var lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($lineChartLabels),
                datasets: [{
                    label: 'Solicitudes Creadas',
                    data: @json($lineChartData),
                    backgroundColor: 'rgba(60,141,188,0.1)',
                    borderColor: '#3c8dbc',
                    pointRadius: 4,
                    pointBackgroundColor: '#3c8dbc',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    yAxes: [{ 
                        ticks: { beginAtZero: true, precision: 0 },
                        gridLines: { display: true, color: '#efefef' }
                    }],
                    xAxes: [{ gridLines: { display: false } }]
                },
                legend: { display: false }
            }
        });

        // --- GRÁFICO DONUT ---
        var donutCtx = document.getElementById('requestStatusDonutChart').getContext('2d');
        var donutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                datasets: [{
                    data: [{{ $chartPending }}, {{ $chartApproved }}, {{ $chartRejected }}],
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { position: 'left' }
            }
        });
    });
</script>
@endpush
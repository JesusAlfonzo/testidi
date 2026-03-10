@extends('adminlte::page')

@section('title', 'Panel de Supervisor')
@section('plugins.Chartjs', true)

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
    .mini-stat {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        transition: all 0.2s;
    }
    .mini-stat:hover {
        background: #e9ecef;
    }
    .mini-stat-value {
        font-size: 20px;
        font-weight: 700;
    }
    .mini-stat-label {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
    }
    .chart-container {
        position: relative;
        height: 220px;
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
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="font-weight: 700; color: #1a4a7a;">
                <i class="fas fa-user-shield mr-2"></i>Panel de Supervisión
            </h2>
            <p class="text-muted mb-0">Métricas y seguimiento operativo</p>
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
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Solicitudes</div>
                        <div class="kpi-value" style="color: #ffc107;">{{ $pendingRequests }}</div>
                        <div class="text-muted" style="font-size: 12px;">Pendientes</div>
                    </div>
                    <div class="kpi-icon" style="background: #fff3cd; color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver solicitudes <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Stock Bajo</div>
                        <div class="kpi-value" style="color: #dc3545;">{{ $lowStockCount }}</div>
                        <div class="text-muted" style="font-size: 12px;">Crítico</div>
                    </div>
                    <div class="kpi-icon" style="background: #f8d7da; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.reports.stock') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver reporte <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Productos</div>
                        <div class="kpi-value" style="color: #1a4a7a;">{{ $totalProducts }}</div>
                        <div class="text-muted" style="font-size: 12px;">En inventario</div>
                    </div>
                    <div class="kpi-icon" style="background: #e8f4fd; color: #1a4a7a;">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver inventario <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Salidas Hoy</div>
                        <div class="kpi-value" style="color: #28a745;">{{ $approvedRequestsToday }}</div>
                        <div class="text-muted" style="font-size: 12px;">Procesadas</div>
                    </div>
                    <div class="kpi-icon" style="background: #d4edda; color: #28a745;">
                        <i class="fas fa-dolly"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}?status=Approved" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver historial <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

{{-- ACCIONES RÁPIDAS --}}
<div class="row mt-3">
    <div class="col-12">
        <div class="section-title">
            <i class="fas fa-bolt text-warning"></i> Acciones Rápidas
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.requests.index') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #ffc107;"><i class="fas fa-clipboard-list"></i></div>
            <div class="quick-action-label">Ver Solicitudes</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.stock') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="quick-action-label">Stock Bajo</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.requests') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #17a2b8;"><i class="fas fa-chart-bar"></i></div>
            <div class="quick-action-label">Reportes</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.audit.index') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #6c757d;"><i class="fas fa-history"></i></div>
            <div class="quick-action-label">Auditoría</div>
        </a>
    </div>
</div>

{{-- ESTADÍSTICAS DE INVENTARIO --}}
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #1a4a7a;">
                <h6 class="font-weight-bold mb-0" style="color: #1a4a7a;">
                    <i class="fas fa-cubes mr-2"></i>Inventario
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #1a4a7a;">{{ $inventoryStats['products'] }}</div>
                            <div class="mini-stat-label">Productos</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #6c757d;">{{ $inventoryStats['categories'] }}</div>
                            <div class="mini-stat-label">Categorías</div>
                        </div>
                    </div>
                    <div class="col-6 mt-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #28a745;">{{ $inventoryStats['suppliers'] }}</div>
                            <div class="mini-stat-label">Proveedores</div>
                        </div>
                    </div>
                    <div class="col-6 mt-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #17a2b8;">{{ $inventoryStats['locations'] }}</div>
                            <div class="mini-stat-label">Ubicaciones</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-line mr-2 text-primary"></i>Actividad Semanal
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="supervisorLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GRÁFICOS --}}
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-pie mr-2 text-warning"></i>Estado de Solicitudes
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 180px;">
                    <canvas id="supervisorDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-bar mr-2 text-success"></i>Resumen Operativo
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-warning">{{ $chartPending }}</div>
                            <div class="mini-stat-label">Pendientes</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-success">{{ $chartApproved }}</div>
                            <div class="mini-stat-label">Aprobadas</div>
                        </div>
                    </div>
                    <div class="col-6 mt-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-danger">{{ $chartRejected }}</div>
                            <div class="mini-stat-label">Rechazadas</div>
                        </div>
                    </div>
                    <div class="col-6 mt-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #1a4a7a;">{{ $totalProducts }}</div>
                            <div class="mini-stat-label">Productos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function() {
    'use strict'

    var lineCtx = document.getElementById('supervisorLineChart').getContext('2d');
    var lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: @json($lineChartLabels),
            datasets: [{
                label: 'Solicitudes',
                data: @json($lineChartData),
                backgroundColor: 'rgba(26, 74, 122, 0.1)',
                borderColor: '#1a4a7a',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#1a4a7a',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    precision: 0,
                    grid: { color: '#f0f0f0' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    var donutCtx = document.getElementById('supervisorDonutChart').getContext('2d');
    var donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
            datasets: [{
                data: [{{ $chartPending }}, {{ $chartApproved }}, {{ $chartRejected }}],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                } 
            }
        }
    });
});
</script>
@endpush

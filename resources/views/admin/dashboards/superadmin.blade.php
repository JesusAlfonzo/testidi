@extends('adminlte::page')

@section('title', 'Dashboard | IAC')
@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)

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
    .stat-badge {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
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
    .alert-card {
        border-left: 4px solid;
        border-radius: 8px;
    }
    .alert-card.danger { border-left-color: #dc3545; }
    .alert-card.warning { border-left-color: #ffc107; }
    .alert-card.success { border-left-color: #28a745; }
    .alert-card.info { border-left-color: #17a2b8; }
    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
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
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.draft { background: #6c757d; }
    .status-dot.sent { background: #17a2b8; }
    .status-dot.approved { background: #28a745; }
    .status-dot.pending { background: #ffc107; }
    .status-dot.rejected { background: #dc3545; }
    .status-dot.issued { background: #007bff; }
    .status-dot.completed { background: #28a745; }
    .status-dot.cancelled { background: #dc3545; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="font-weight: 700; color: #1a4a7a;">
                <i class="fas fa-chart-line mr-2"></i>Dashboard
            </h2>
            <p class="text-muted mb-0">Resumen general del sistema de inventario</p>
        </div>
        <div class="text-right">
            <span class="text-muted d-block">Última actualización</span>
            <span class="text-info font-weight-bold">{{ now()->format('d M, Y - H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    {{-- KPI PRINCIPALES --}}
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-value card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Total Productos</div>
                        <div class="kpi-value" style="color: #1a4a7a;">{{ $inventoryStats['products'] }}</div>
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
                        <div class="kpi-label text-muted">Proveedores</div>
                        <div class="kpi-value" style="color: #28a745;">{{ $inventoryStats['suppliers'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Activos</div>
                    </div>
                    <div class="kpi-icon" style="background: #d4edda; color: #28a745;">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.suppliers.index') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver proveedores <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Órdenes Activas</div>
                        <div class="kpi-value" style="color: #007bff;">{{ $orderStats['issued'] + $orderStats['draft'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Pendientes</div>
                    </div>
                    <div class="kpi-icon" style="background: #cce5ff; color: #007bff;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.purchaseOrders.index') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver órdenes <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card card bg-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label text-muted">Solicitudes</div>
                        <div class="kpi-value" style="color: #ffc107;">{{ $chartPending }}</div>
                        <div class="text-muted" style="font-size: 12px;">Pendientes</div>
                    </div>
                    <div class="kpi-icon" style="background: #fff3cd; color: #ffc107;">
                        <i class="fas fa-hand-paper"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.requests.index') }}" class="card-footer bg-white border-0 text-muted" style="font-size: 12px;">
                Ver solicitudes <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

{{-- ALERTAS --}}
@if($lowStockCount > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-card danger bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger mr-3" style="font-size: 24px;"></i>
                    <div>
                        <strong>Alerta de Stock Bajo</strong>
                        <p class="mb-0 text-muted">{{ $lowStockCount }} productos tienen stock por debajo del mínimo</p>
                    </div>
                </div>
                <a href="{{ route('admin.reports.stock') }}?stock_status=low" class="btn btn-danger">
                    <i class="fas fa-eye mr-1"></i> Ver Productos
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ALERTA PRODUCTOS POR VENCER --}}
@if(isset($expiringProducts) && $expiringProducts->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-card warning bg-white shadow-sm" style="border-left: 4px solid #ffc107;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clock text-warning mr-3" style="font-size: 24px;"></i>
                    <div>
                        <strong>Alerta de Vencimiento</strong>
                        <p class="mb-0 text-muted">{{ $expiringProducts->count() }} lotes vencen en los próximos 30 días</p>
                    </div>
                </div>
                <button class="btn btn-warning" data-toggle="modal" data-target="#expiringProductsModal">
                    <i class="fas fa-eye mr-1"></i> Ver Productos
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ACCIONES RÁPIDAS --}}
<div class="row mt-3">
    <div class="col-12">
        <div class="section-title">
            <i class="fas fa-bolt text-warning"></i> Acciones Rápidas
        </div>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.products.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #1a4a7a;"><i class="fas fa-plus-circle"></i></div>
            <div class="quick-action-label">Nuevo Producto</div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.rfq.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #6c757d;"><i class="fas fa-file-alt"></i></div>
            <div class="quick-action-label">Nueva RFQ</div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.quotations.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #ffc107;"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="quick-action-label">Nueva Cotización</div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.purchaseOrders.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #28a745;"><i class="fas fa-shopping-cart"></i></div>
            <div class="quick-action-label">Nueva OC</div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.stock-in.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #17a2b8;"><i class="fas fa-truck-loading"></i></div>
            <div class="quick-action-label">Entrada Stock</div>
        </a>
    </div>
    <div class="col-6 col-md-2">
        <a href="{{ route('admin.requests.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #dc3545;"><i class="fas fa-hand-holding"></i></div>
            <div class="quick-action-label">Solicitar</div>
        </a>
    </div>
</div>

{{-- ESTADÍSTICAS DE MÓDULOS --}}
<div class="row mt-4">
    {{-- RFQ --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #6c757d;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #6c757d;">
                        <i class="fas fa-file-alt mr-2"></i>RFQ
                    </h6>
                    <a href="{{ route('admin.rfq.index') }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-secondary">{{ $rfqStats['draft'] }}</div>
                            <div class="mini-stat-label">Borrador</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #17a2b8;">{{ $rfqStats['sent'] }}</div>
                            <div class="mini-stat-label">Enviadas</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-success">{{ $rfqStats['completed'] }}</div>
                            <div class="mini-stat-label">Cerradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- COTIZACIONES --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #ffc107;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #ffc107;">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Cotizaciones
                    </h6>
                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-secondary">{{ $quoteStats['pending'] }}</div>
                            <div class="mini-stat-label">Pendientes</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #17a2b8;">{{ $quoteStats['selected'] }}</div>
                            <div class="mini-stat-label">Seleccionada</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-success">{{ $quoteStats['approved'] }}</div>
                            <div class="mini-stat-label">Aprobadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ÓRDENES DE COMPRA --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #28a745;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #28a745;">
                        <i class="fas fa-clipboard-list mr-2"></i>Órdenes de Compra
                    </h6>
                    <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-secondary">{{ $orderStats['draft'] }}</div>
                            <div class="mini-stat-label">Borrador</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value" style="color: #007bff;">{{ $orderStats['issued'] }}</div>
                            <div class="mini-stat-label">Emitidas</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-success">{{ $orderStats['received'] }}</div>
                            <div class="mini-stat-label">Recibidas</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-danger">{{ $orderStats['cancelled'] }}</div>
                            <div class="mini-stat-label">Canceladas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GRÁFICOS Y ACTIVIDAD --}}
<div class="row mt-4">
    {{-- GRÁFICO DE TENDENCIAS --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-line mr-2 text-primary"></i>Tendencia de Solicitudes
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyRequestsLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVIDAD RECIENTE --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0">
                        <i class="fas fa-history mr-2 text-info"></i>Actividad Reciente
                    </h6>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-link">Ver todo</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 260px; overflow-y: auto;">
                    @forelse($recentActivity as $activity)
                    <div class="activity-item px-3">
                        <div class="d-flex align-items-center">
                            @switch($activity->log_name)
                                @case('Product')
                                    <div class="activity-icon" style="background: #e8f4fd; color: #1a4a7a;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    @break
                                @case('Supplier')
                                    <div class="activity-icon" style="background: #d4edda; color: #28a745;">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    @break
                                @case('PurchaseQuote')
                                    <div class="activity-icon" style="background: #fff3cd; color: #ffc107;">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    @break
                                @case('PurchaseOrder')
                                    <div class="activity-icon" style="background: #cce5ff; color: #007bff;">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    @break
                                @case('InventoryRequest')
                                    <div class="activity-icon" style="background: #f8d7da; color: #dc3545;">
                                        <i class="fas fa-hand-holding"></i>
                                    </div>
                                    @break
                                @default
                                    <div class="activity-icon" style="background: #e2e3e5; color: #6c757d;">
                                        <i class="fas fa-cog"></i>
                                    </div>
                            @endswitch
                            <div>
                                <div style="font-size: 13px; font-weight: 500;">{{ $activity->description }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $activity->causer->name ?? 'Sistema' }} • {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox mb-2" style="font-size: 32px;"></i>
                        <p class="mb-0">Sin actividad reciente</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ESTADÍSTICAS GRÁFICAS --}}
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-pie mr-2 text-warning"></i>Estado de Cotizaciones
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 180px;">
                    <canvas id="quoteChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-bar mr-2 text-success"></i>Estado de Órdenes de Compra
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 180px;">
                    <canvas id="orderChart"></canvas>
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

    // Colores modernos
    const colors = {
        primary: '#1a4a7a',
        secondary: '#6c757d',
        success: '#28a745',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#17a2b8'
    };

    // GRÁFICO DE LÍNEAS
    var lineCtx = document.getElementById('dailyRequestsLineChart').getContext('2d');
    var lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: @json($lineChartLabels),
            datasets: [{
                label: 'Solicitudes',
                data: @json($lineChartData),
                backgroundColor: 'rgba(26, 74, 122, 0.1)',
                borderColor: colors.primary,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: colors.primary,
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

    // GRÁFICO DONUT COTIZACIONES
    var quoteCtx = document.getElementById('quoteChart').getContext('2d');
    var quoteChart = new Chart(quoteCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'Seleccionada', 'Aprobadas', 'Rechazadas', 'Convertida'],
            datasets: [{
                data: [
                    {{ $quoteStats['pending'] }},
                    {{ $quoteStats['selected'] }},
                    {{ $quoteStats['approved'] }},
                    {{ $quoteStats['rejected'] }},
                    {{ $quoteStats['converted'] }}
                ],
                backgroundColor: [colors.secondary, colors.info, colors.success, colors.danger, colors.primary],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { 
                legend: { 
                    position: 'right',
                    labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                } 
            }
        }
    });

    // GRÁFICO DE BARRAS ÓRDENES
    var orderCtx = document.getElementById('orderChart').getContext('2d');
    var orderChart = new Chart(orderCtx, {
        type: 'bar',
        data: {
            labels: ['Borrador', 'Emitida', 'Recibida', 'Cancelada'],
            datasets: [{
                data: [
                    {{ $orderStats['draft'] }},
                    {{ $orderStats['issued'] }},
                    {{ $orderStats['received'] }},
                    {{ $orderStats['cancelled'] }}
                ],
                backgroundColor: [colors.secondary, colors.info, colors.success, colors.danger],
                borderRadius: 6
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
});
</script>
@endpush

{{-- MODAL PRODUCTOS POR VENCER --}}
@if(isset($expiringProducts) && $expiringProducts->count() > 0)
<div class="modal fade" id="expiringProductsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-clock"></i> Productos por Vencer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Fecha Vencimiento</th>
                            <th>Cantidad</th>
                            <th>Días Restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringProducts as $batch)
                        <tr>
                            <td>{{ $batch->product->name ?? 'N/A' }}</td>
                            <td>{{ $batch->batch_number ?? 'Sin lote' }}</td>
                            <td>{{ $batch->expiry_date->format('d/m/Y') }}</td>
                            <td>{{ $batch->quantity }}</td>
                            <td>
                                @php $daysLeft = now()->diffInDays($batch->expiry_date, false); @endphp
                                @if($daysLeft <= 7)
                                    <span class="badge badge-danger">{{ $daysLeft }} días</span>
                                @elseif($daysLeft <= 15)
                                    <span class="badge badge-warning">{{ $daysLeft }} días</span>
                                @else
                                    <span class="badge badge-info">{{ $daysLeft }} días</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

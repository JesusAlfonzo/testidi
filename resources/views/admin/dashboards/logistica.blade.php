@extends('adminlte::page')

@section('title', 'Panel de Logística')
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
    .alert-card {
        border-left: 4px solid;
        border-radius: 8px;
    }
    .alert-card.danger { border-left-color: #dc3545; }
    .alert-card.warning { border-left-color: #ffc107; }
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
                <i class="fas fa-truck-loading mr-2"></i>Panel de Logística
            </h2>
            <p class="text-muted mb-0">Operaciones y gestión de inventario</p>
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
                        <div class="kpi-label text-muted">Total Productos</div>
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
                        <div class="kpi-label text-muted">Solicitudes</div>
                        <div class="kpi-value" style="color: #ffc107;">{{ $pendingRequests }}</div>
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
                        <div class="kpi-label text-muted">Salidas Hoy</div>
                        <div class="kpi-value" style="color: #28a745;">{{ $approvedRequestsToday }}</div>
                        <div class="text-muted" style="font-size: 12px;">Procesadas</div>
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
</div>

{{-- ALERTA STOCK BAJO --}}
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
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.stock-in.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #28a745;"><i class="fas fa-truck-loading"></i></div>
            <div class="quick-action-label">Entrada Stock</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.requests.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #dc3545;"><i class="fas fa-hand-holding"></i></div>
            <div class="quick-action-label">Nueva Solicitud</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.products.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #1a4a7a;"><i class="fas fa-plus-circle"></i></div>
            <div class="quick-action-label">Nuevo Producto</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.kits.create') }}" class="quick-action-btn d-block bg-white shadow-sm text-decoration-none">
            <div class="quick-action-icon" style="color: #6c757d;"><i class="fas fa-box-open"></i></div>
            <div class="quick-action-label">Nuevo Kit</div>
        </a>
    </div>
</div>

{{-- ESTADÍSTICAS Y GRÁFICOS --}}
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-line mr-2 text-primary"></i>Flujo de Solicitudes (7 días)
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="logisticsLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h6 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-pie mr-2 text-warning"></i>Estado de Solicitudes
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-warning">{{ $chartPending }}</div>
                            <div class="mini-stat-label">Pendientes</div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-success">{{ $chartApproved }}</div>
                            <div class="mini-stat-label">Aprobadas</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mini-stat">
                            <div class="mini-stat-value text-danger">{{ $chartRejected }}</div>
                            <div class="mini-stat-label">Rechazadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLA STOCK BAJO --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0" style="border-left: 4px solid #dc3545;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0" style="color: #dc3545;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Productos con Stock Bajo
                    </h6>
                    <a href="{{ route('admin.reports.stock') }}" class="btn btn-sm btn-outline-secondary">Ver Todo</a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Stock Actual</th>
                            <th class="text-center">Mínimo</th>
                            <th class="text-center">Categoría</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <br><small class="text-muted">{{ $product->code }}</small>
                            </td>
                            <td class="text-center text-danger font-weight-bold" style="font-size: 1.1em;">
                                {{ $product->stock }}
                            </td>
                            <td class="text-center">{{ $product->min_stock }}</td>
                            <td class="text-center">{{ $product->category->name ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.stock-in.create') }}" class="btn btn-xs btn-success shadow-sm">
                                    <i class="fas fa-plus-circle"></i> Reabastecer
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <p class="mb-0 text-muted">No hay productos en nivel crítico</p>
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

@push('js')
<script>
$(function() {
    'use strict'

    var lineCtx = document.getElementById('logisticsLineChart').getContext('2d');
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

@extends('adminlte::page')

@section('title', 'Dashboard | Sistema de Inventario')
@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt text-dark"></i> Dashboard General</h1>
        <div>
            <span class="text-muted">Última actualización: </span>
            <span class="text-info">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
{{-- 1. ESTADÍSTICAS DE INVENTARIO --}}
<h5 class="mb-2 text-secondary"><i class="fas fa-boxes"></i> Estadísticas de Inventario</h5>
<div class="row">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-box"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Productos</span>
                <span class="info-box-number">{{ $inventoryStats['products'] }}</span>
            </div>
            <a href="{{ route('admin.products.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-secondary"><i class="fas fa-tags"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Categorías</span>
                <span class="info-box-number">{{ $inventoryStats['categories'] }}</span>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-dark"><i class="fas fa-copyright"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Marcas</span>
                <span class="info-box-number">{{ $inventoryStats['brands'] }}</span>
            </div>
            <a href="{{ route('admin.brands.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-ruler"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Unidades</span>
                <span class="info-box-number">{{ $inventoryStats['units'] }}</span>
            </div>
            <a href="{{ route('admin.units.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ubicaciones</span>
                <span class="info-box-number">{{ $inventoryStats['locations'] }}</span>
            </div>
            <a href="{{ route('admin.locations.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Proveedores</span>
                <span class="info-box-number">{{ $inventoryStats['suppliers'] }}</span>
            </div>
            <a href="{{ route('admin.suppliers.index') }}" class="stretched-link"></a>
        </div>
    </div>
</div>

{{-- 2. ALERTAS DE STOCK BAJO --}}
@if($lowStockCount > 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Alerta de Stock Bajo</h5>
            <p class="mb-1"><strong>{{ $lowStockCount }}</strong> productos tienen stock por debajo del mínimo.</p>
            <a href="{{ route('admin.reports.stock') }}?stock_status=low" class="btn btn-danger btn-sm">Ver productos</a>
        </div>
    </div>
</div>
@endif

{{-- 3. MÓDULO DE COMPRAS (RFQs, Cotizaciones, Órdenes) --}}
<h5 class="mb-2 mt-3 text-secondary"><i class="fas fa-shopping-cart"></i> Módulo de Compras</h5>
<div class="row">
    {{-- RFQs --}}
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Solicitudes de Cotización (RFQ)</h3>
            </div>
            <div class="card-body p-2">
                <div class="row text-center">
                    <div class="col-3">
                        <span class="info-box-number text-info">{{ $rfqStats['draft'] }}</span>
                        <small class="text-muted d-block">Borrador</small>
                    </div>
                    <div class="col-3">
                        <span class="info-box-number text-primary">{{ $rfqStats['sent'] }}</span>
                        <small class="text-muted d-block">Enviadas</small>
                    </div>
                    <div class="col-3">
                        <span class="info-box-number text-warning">{{ $rfqStats['partial'] }}</span>
                        <small class="text-muted d-block">Parcial</small>
                    </div>
                    <div class="col-3">
                        <span class="info-box-number text-success">{{ $rfqStats['completed'] }}</span>
                        <small class="text-muted d-block">Completas</small>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.rfq.index') }}" class="btn btn-info btn-sm">Ver RFQs</a>
            </div>
        </div>
    </div>

    {{-- Cotizaciones --}}
    <div class="col-md-4">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Cotizaciones</h3>
            </div>
            <div class="card-body p-2">
                <div class="row text-center">
                    <div class="col-4">
                        <span class="info-box-number text-secondary">{{ $quoteStats['pending'] }}</span>
                        <small class="text-muted d-block">Pendientes</small>
                    </div>
                    <div class="col-4">
                        <span class="info-box-number text-info">{{ $quoteStats['selected'] }}</span>
                        <small class="text-muted d-block">Seleccionada</small>
                    </div>
                    <div class="col-4">
                        <span class="info-box-number text-success">{{ $quoteStats['approved'] }}</span>
                        <small class="text-muted d-block">Aprobadas</small>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-warning btn-sm">Ver Cotizaciones</a>
            </div>
        </div>
    </div>

    {{-- Órdenes de Compra --}}
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Órdenes de Compra</h3>
            </div>
            <div class="card-body p-2">
                <div class="row text-center">
                    <div class="col-4">
                        <span class="info-box-number text-secondary">{{ $orderStats['draft'] }}</span>
                        <small class="text-muted d-block">Borrador</small>
                    </div>
                    <div class="col-4">
                        <span class="info-box-number text-primary">{{ $orderStats['issued'] }}</span>
                        <small class="text-muted d-block">Emitidas</small>
                    </div>
                    <div class="col-4">
                        <span class="info-box-number text-success">{{ $orderStats['received'] }}</span>
                        <small class="text-muted d-block">Recibidas</small>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.purchaseOrders.index') }}" class="btn btn-success btn-sm">Ver Órdenes</a>
            </div>
        </div>
    </div>
</div>

{{-- 4. GRÁFICOS DE ANÁLISIS --}}
<h5 class="mb-2 mt-3 text-secondary"><i class="fas fa-chart-line"></i> Análisis de Operaciones</h5>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Estado de Cotizaciones</h3>
            </div>
            <div class="card-body">
                <canvas id="quoteChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Estado de Órdenes de Compra</h3>
            </div>
            <div class="card-body">
                <canvas id="orderChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Tendencia de Solicitudes (Última Semana)</h3>
            </div>
            <div class="card-body">
                <canvas id="dailyRequestsLineChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Estado de Solicitudes</h3>
            </div>
            <div class="card-body">
                <canvas id="requestStatusDonutChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- 5. PRODUCTOS CON STOCK BAJO --}}
@if($lowStockProducts->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Productos con Stock Bajo</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td><span class="badge badge-danger">{{ $product->stock }}</span></td>
                            <td>{{ $product->min_stock }}</td>
                            <td>{{ $product->location->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.stock-in.create') }}?product_id={{ $product->id }}" class="btn btn-success btn-sm" title="Entrada de stock">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 6. ACTIVIDAD RECIENTE --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Actividad Reciente del Sistema</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivity as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $activity->causer->name ?? 'Sistema' }}</td>
                            <td>
                                @switch($activity->log_name)
                                    @case('Product')
                                        <span class="badge badge-info">Producto</span>
                                        @break
                                    @case('Supplier')
                                        <span class="badge badge-success">Proveedor</span>
                                        @break
                                    @case('PurchaseQuote')
                                        <span class="badge badge-warning">Cotización</span>
                                        @break
                                    @case('PurchaseOrder')
                                        <span class="badge badge-primary">Orden Compra</span>
                                        @break
                                    @case('InventoryRequest')
                                        <span class="badge badge-secondary">Solicitud</span>
                                        @break
                                    @default
                                        <span class="badge badge-dark">{{ $activity->log_name ?? 'General' }}</span>
                                @endswitch
                            </td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay actividad reciente</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary btn-sm">Ver todas las actividades</a>
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

    // --- GRÁFICO DONUT SOLICITUDES ---
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

    // --- GRÁFICO DE COTIZACIONES ---
    var quoteCtx = document.getElementById('quoteChart').getContext('2d');
    var quoteChart = new Chart(quoteCtx, {
        type: 'bar',
        data: {
            labels: ['Pendientes', 'Seleccionada', 'Aprobadas', 'Rechazadas', 'Convertida'],
            datasets: [{
                label: 'Cotizaciones',
                data: [
                    {{ $quoteStats['pending'] }},
                    {{ $quoteStats['selected'] }},
                    {{ $quoteStats['approved'] }},
                    {{ $quoteStats['rejected'] }},
                    {{ $quoteStats['converted'] }}
                ],
                backgroundColor: ['#6c757d', '#17a2b8', '#28a745', '#dc3545', '#007bff'],
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { display: false },
            scales: {
                yAxes: [{ 
                    ticks: { beginAtZero: true, precision: 0 },
                    gridLines: { display: true, color: '#efefef' }
                }],
                xAxes: [{ gridLines: { display: false } }]
            }
        }
    });

    // --- GRÁFICO DE ÓRDENES DE COMPRA ---
    var orderCtx = document.getElementById('orderChart').getContext('2d');
    var orderChart = new Chart(orderCtx, {
        type: 'bar',
        data: {
            labels: ['Borrador', 'Emitida', 'Recibida', 'Cancelada'],
            datasets: [{
                label: 'Órdenes de Compra',
                data: [
                    {{ $orderStats['draft'] }},
                    {{ $orderStats['issued'] }},
                    {{ $orderStats['received'] }},
                    {{ $orderStats['cancelled'] }}
                ],
                backgroundColor: ['#6c757d', '#007bff', '#28a745', '#dc3545'],
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { display: false },
            scales: {
                yAxes: [{ 
                    ticks: { beginAtZero: true, precision: 0 },
                    gridLines: { display: true, color: '#efefef' }
                }],
                xAxes: [{ gridLines: { display: false } }]
            }
        }
    });
});
</script>
@endpush

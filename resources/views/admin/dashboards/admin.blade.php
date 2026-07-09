@extends('adminlte::page')

@section('title', 'Dashboard Administrador | IAC')
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
        font-size: 24px;
        font-weight: 700;
    }
    .kpi-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .activity-item {
        padding: 12px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }
    .activity-item:hover {
        background-color: #f8f9fa;
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
        height: 250px;
    }
    .currency-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .currency-badge.usd { background-color: #d4edda; color: #155724; }
    .currency-badge.ves { background-color: #f8d7da; color: #721c24; }
    .currency-badge.eur { background-color: #cce5ff; color: #004085; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-user-shield text-primary mr-2"></i>Dashboard Administrador General</h1>
            <p class="text-muted mb-0 small">Métricas monetarias de inventario, rendimiento de solicitudes e historial de auditoría global.</p>
        </div>
        <div class="text-right d-none d-md-block">
            <span class="text-muted small d-block">Última Actualización</span>
            <span class="text-primary font-weight-bold">{{ now()->format('d M, Y - H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
    {{-- ALERTAS DE STOCK BAJO --}}
    @if($lowStockCount > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger shadow-sm border-0 d-flex justify-content-between align-items-center mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle text-white mr-3" style="font-size: 24px;"></i>
                        <div>
                            <strong class="text-white">Alerta de Stock Crítico</strong>
                            <p class="mb-0 text-white-50 small">Hay {{ $lowStockCount }} productos cuya existencia se encuentra por debajo de su stock mínimo de seguridad.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.products.index', ['stock_status' => 'low']) }}" class="btn btn-light btn-sm text-danger font-weight-bold">
                        <i class="fas fa-eye mr-1"></i> Ver Productos
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- METRICAS OPERATIVAS GENERALES --}}
    <div class="row mb-3">
        {{-- Productos Activos --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card card bg-white shadow-sm h-100" style="border-top: 4px solid #0284c7;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label text-muted">Productos Activos</div>
                            <div class="kpi-value mt-2 text-info">{{ $totalProducts }}</div>
                            <span class="badge badge-light mt-2">Catálogo Activo</span>
                        </div>
                        <div class="kpi-icon bg-light text-info" style="color: #0284c7 !important;">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <a href="{{ route('admin.products.index') }}" class="text-info font-weight-bold small">
                        Ver Inventario <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Alertas Stock Bajo --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card card bg-white shadow-sm h-100" style="border-top: 4px solid #ef4444;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label text-muted">Stock Crítico</div>
                            <div class="kpi-value mt-2 text-danger">{{ $lowStockCount }}</div>
                            <span class="badge badge-danger-light mt-2">Bajo Mínimo</span>
                        </div>
                        <div class="kpi-icon bg-light text-danger" style="color: #ef4444 !important;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <a href="{{ route('admin.products.index', ['stock_status' => 'low']) }}" class="text-danger font-weight-bold small">
                        Ver Alertas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Solicitudes Pendientes --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card card bg-white shadow-sm h-100" style="border-top: 4px solid #eab308;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label text-muted">Por Aprobar</div>
                            <div class="kpi-value mt-2 text-warning">{{ $chartPending }}</div>
                            <span class="badge badge-warning-light mt-2">Solicitudes Pendientes</span>
                        </div>
                        <div class="kpi-icon bg-light text-warning" style="color: #eab308 !important;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <a href="{{ route('admin.requests.index', ['status' => 'Pending']) }}" class="text-warning font-weight-bold small">
                        Ver Solicitudes <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Lotes por Vencer --}}
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="kpi-card card bg-white shadow-sm h-100" style="border-top: 4px solid #f97316;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label text-muted">Lotes por Vencer</div>
                            <div class="kpi-value mt-2 text-orange" style="color: #f97316;">{{ $expiringCount }}</div>
                            <span class="badge badge-orange-light mt-2">Próximos 60 Días</span>
                        </div>
                        <div class="kpi-icon bg-light text-orange" style="color: #f97316 !important;">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <a href="{{ route('admin.reports.stock') }}" class="text-orange font-weight-bold small" style="color: #f97316;">
                        Control FEFO <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ACCESOS RÁPIDOS --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-secondary">
                        <i class="fas fa-bolt mr-2 text-warning"></i>Accesos Rápidos
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                            <a href="{{ route('admin.rfq.create') }}" class="btn btn-outline-primary btn-block text-left p-3 shadow-sm d-flex align-items-center justify-content-between rounded-lg">
                                <div>
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-file-invoice-dollar mr-2"></i>Crear RFQ</h6>
                                    <small class="text-muted d-block text-truncate" style="max-width: 180px;">Solicitar presupuesto a proveedores</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-info btn-block text-left p-3 shadow-sm d-flex align-items-center justify-content-between rounded-lg">
                                <div>
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-boxes mr-2"></i>Ver Inventario</h6>
                                    <small class="text-muted d-block text-truncate" style="max-width: 180px;">Consultar productos y kits</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                            <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-success btn-block text-left p-3 shadow-sm d-flex align-items-center justify-content-between rounded-lg">
                                <div>
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-truck-loading mr-2"></i>Aprobar Salidas</h6>
                                    <small class="text-muted d-block text-truncate" style="max-width: 180px;">Despachar solicitudes pendientes</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-outline-danger btn-block text-left p-3 shadow-sm d-flex align-items-center justify-content-between rounded-lg">
                                <div>
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-file-import mr-2"></i>Registrar Factura</h6>
                                    <small class="text-muted d-block text-truncate" style="max-width: 180px;">Ingresar mercancía (StockIn)</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RENDIMIENTO DE SOLICITUDES Y FLUJO DE STOCK --}}
    <div class="row mb-3">
        {{-- Gráfico Flujo Mensual --}}
        <div class="col-md-7 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-secondary">
                        <i class="fas fa-exchange-alt mr-2 text-success"></i>Flujo Mensual: Entradas vs Salidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container w-100" style="height: 250px;">
                        <canvas id="flowChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rendimiento Gráfico Solicitudes --}}
        <div class="col-md-5 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-secondary">
                        <i class="fas fa-chart-pie mr-2 text-primary"></i>Rendimiento de Solicitudes
                    </h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    @if($chartApproved == 0 && $chartRejected == 0 && $chartPending == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-bar mb-2" style="font-size: 32px;"></i>
                            <p class="mb-0 small">No hay solicitudes registradas para graficar.</p>
                        </div>
                    @else
                        <div class="chart-container w-100" style="height: 250px;">
                            <canvas id="requestsChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DETALLES OPERATIVOS --}}
    <div class="row">
        {{-- Lotes Próximos a Vencer --}}
        <div class="col-md-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-secondary">
                        <i class="fas fa-calendar-times mr-2 text-warning"></i>Lotes Próximos a Vencer (FEFO)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 290px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="small font-weight-bold text-muted text-uppercase py-2">Producto</th>
                                    <th class="small font-weight-bold text-muted text-uppercase py-2">Lote</th>
                                    <th class="small font-weight-bold text-muted text-uppercase py-2">Vencimiento</th>
                                    <th class="small font-weight-bold text-muted text-uppercase py-2 text-center">Stock</th>
                                    <th class="small font-weight-bold text-muted text-uppercase py-2 text-center">Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringProducts as $batch)
                                    <tr>
                                        <td class="align-middle py-2 small font-weight-bold text-truncate" style="max-width: 150px;" title="{{ $batch->product->name ?? 'N/A' }}">
                                            {{ $batch->product->name ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle py-2 small">{{ $batch->batch_number ?? '-' }}</td>
                                        <td class="align-middle py-2 small">{{ $batch->expiration_date ? $batch->expiration_date->format('d/m/Y') : '-' }}</td>
                                        <td class="text-center align-middle py-2">
                                            <span class="badge badge-info">{{ $batch->quantity }}</span>
                                        </td>
                                        <td class="text-center align-middle py-2">
                                            @php $daysLeft = $batch->getDaysUntilExpiry(); @endphp
                                            @if($daysLeft === 0)
                                                <span class="badge badge-warning">Hoy</span>
                                            @elseif($daysLeft < 0)
                                                <span class="badge badge-danger">Vencido</span>
                                            @elseif($daysLeft <= 7)
                                                <span class="badge badge-danger">{{ $daysLeft }}d</span>
                                            @elseif($daysLeft <= 30)
                                                <span class="badge badge-warning">{{ $daysLeft }}d</span>
                                            @else
                                                <span class="badge badge-success">{{ $daysLeft }}d</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-calendar-check text-success mb-2" style="font-size: 24px;"></i>
                                            <p class="mb-0 small">No hay lotes próximos a vencer en los próximos 60 días.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feed de Auditoría --}}
        <div class="col-md-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0 text-secondary">
                        <i class="fas fa-history mr-2 text-info"></i>Bitácora de Auditoría Reciente
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 290px; overflow-y: auto;">
                        @forelse($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon bg-secondary text-white" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 13px; font-weight: 600;" class="text-dark">
                                            @php
                                                // Traducción del evento
                                                $action = $activity->description;
                                                $translatedAction = $action;
                                                if ($action === 'created') $translatedAction = 'creó';
                                                elseif ($action === 'updated') $translatedAction = 'actualizó';
                                                elseif ($action === 'deleted') $translatedAction = 'eliminó';

                                                // Traducción del módulo
                                                $modelClass = $activity->subject_type ? class_basename($activity->subject_type) : '';
                                                $translatedModel = 'un registro';
                                                switch ($modelClass) {
                                                    case 'PurchaseOrder': $translatedModel = 'una Orden de Compra'; break;
                                                    case 'RequestForQuotation': $translatedModel = 'una SDC'; break;
                                                    case 'Product': $translatedModel = 'un Producto'; break;
                                                    case 'Supplier': $translatedModel = 'un Proveedor'; break;
                                                    case 'InventoryRequest': $translatedModel = 'una Solicitud'; break;
                                                    case 'StockIn': $translatedModel = 'una Entrada de Inventario'; break;
                                                    case 'User': $translatedModel = 'un Usuario'; break;
                                                    case 'Kit': $translatedModel = 'un Kit'; break;
                                                    case 'Category': $translatedModel = 'una Categoría'; break;
                                                    case 'Brand': $translatedModel = 'una Marca'; break;
                                                    case 'Location': $translatedModel = 'una Ubicación'; break;
                                                }

                                                // Identificador
                                                $identifier = $activity->subject?->code ?? $activity->subject?->name ?? 'el registro #' . $activity->subject_id;
                                                
                                                // Usuario
                                                $userName = $activity->causer?->name ?? 'El Sistema';
                                            @endphp
                                            <span class="text-primary">{{ $userName }}</span> {{ $translatedAction }} {{ $translatedModel }} ({{ $identifier }})
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="far fa-clock mr-1"></i> {{ $activity->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0 small">No se registran acciones de auditoría recientes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(function() {
        // 1. Gráfico de Flujo Mensual (Entradas vs Salidas)
        var flowCtx = document.getElementById('flowChart').getContext('2d');
        var stats = @json($monthlyStats);
        var labels = stats.map(function(item) { return item.month; });
        var entriesData = stats.map(function(item) { return item.entries; });
        var exitsData = stats.map(function(item) { return item.exits; });

        var flowChart = new Chart(flowCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Entradas (StockIn)',
                        data: entriesData,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'Salidas (Despachos)',
                        data: exitsData,
                        backgroundColor: '#007bff',
                        borderColor: '#007bff',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 } }
                    }
                }
            }
        });

        // 2. Gráfico Rendimiento de Solicitudes (Donut)
        @if($chartApproved > 0 || $chartRejected > 0 || $chartPending > 0)
        var reqCtx = document.getElementById('requestsChart').getContext('2d');
        var requestsChart = new Chart(reqCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aprobadas', 'Rechazadas', 'Pendientes'],
                datasets: [{
                    data: [{{ $chartApproved }}, {{ $chartRejected }}, {{ $chartPending }}],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 } }
                    }
                }
            }
        });
        @endif
    });
</script>
@endpush


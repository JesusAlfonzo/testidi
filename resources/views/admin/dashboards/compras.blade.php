@extends('adminlte::page')

@section('title', 'Dashboard Compras | IAC')

@section('css')
<style>
    .kpi-procurement-card {
        border: none;
        border-radius: 12px;
        color: white;
        transition: transform 0.2s;
    }
    .kpi-procurement-card:hover {
        transform: translateY(-3px);
    }
    .kpi-value-proc {
        font-size: 38px;
        font-weight: 800;
    }
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    }
    .bg-gradient-teal {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    }
    .action-btn-dashboard {
        transition: all 0.2s;
        border-radius: 8px;
    }
    .action-btn-dashboard:hover {
        transform: translateY(-1px);
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-shopping-cart text-info mr-2"></i>Dashboard Compras y Procura</h1>
            <p class="text-muted mb-0 small">Seguimiento de cotizaciones con proveedores (RFQs), órdenes de compra en tránsito y alertas de reabastecimiento.</p>
        </div>
        <div class="text-right d-none d-md-block">
            <span class="text-muted small d-block">Última Actualización</span>
            <span class="text-info font-weight-bold">{{ now()->format('d M, Y - H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
    {{-- METRICAS DE PROCUREMENT --}}
    <div class="row">
        {{-- RFQs Activas --}}
        <div class="col-md-6 col-12 mb-3">
            <div class="kpi-procurement-card card bg-gradient-purple shadow-sm">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-uppercase font-weight-bold small text-white-50 mb-1">Cotizaciones Activas (RFQs)</h5>
                            <div class="kpi-value-proc mb-2">{{ $activeRfqsCount }}</div>
                            <p class="text-white-50 small mb-0">Cotizaciones enviadas a proveedores pendientes de respuesta.</p>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.rfq.index', ['status' => 'sent']) }}" class="btn btn-light btn-sm font-weight-bold text-purple action-btn-dashboard px-3">
                            <i class="fas fa-eye mr-1"></i> Ver RFQs Activas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- POs Pendientes --}}
        <div class="col-md-6 col-12 mb-3">
            <div class="kpi-procurement-card card bg-gradient-teal shadow-sm">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-uppercase font-weight-bold small text-white-50 mb-1">Órdenes en Tránsito (POs)</h5>
                            <div class="kpi-value-proc mb-2">{{ $pendingPosCount }}</div>
                            <p class="text-white-50 small mb-0">Órdenes emitidas en espera de recepción de mercancía física.</p>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.purchaseOrders.index', ['status' => 'issued']) }}" class="btn btn-light btn-sm font-weight-bold text-teal action-btn-dashboard px-3">
                            <i class="fas fa-eye mr-1"></i> Ver Órdenes Emitidas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DETALLE DE REABASTECIMIENTO Y ACCIONES RÁPIDAS --}}
    <div class="row mt-2">
        {{-- Productos en Stock Mínimo (8/12) --}}
        <div class="col-lg-8 col-12 mb-3">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-danger font-weight-bold mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Alerta de Reabastecimiento (Stock Bajo Mínimo)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="small font-weight-bold text-muted text-uppercase">Producto</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Código/SKU</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Stock Actual</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Stock Mínimo</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Marca</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center" style="width: 100px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $prod)
                                    <tr>
                                        <td class="align-middle font-weight-bold">{{ $prod->name }}</td>
                                        <td class="align-middle text-muted">{{ $prod->code ?? 'N/A' }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-danger">{{ $prod->stock }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary">{{ $prod->min_stock }}</span>
                                        </td>
                                        <td class="align-middle">{{ $prod->brand->name ?? 'N/A' }}</td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('admin.rfq.create') }}" class="btn btn-xs btn-outline-primary action-btn-dashboard" title="Generar Solicitud de Cotización">
                                                <i class="fas fa-file-invoice-dollar mr-1"></i> Reordenar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 24px;"></i>
                                            <p class="mb-0 small">Todos los productos se encuentran por encima de su nivel de stock de seguridad.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel de Acciones de Procura (4/12) --}}
        <div class="col-lg-4 col-12 mb-3">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-secondary font-weight-bold mb-0">
                        <i class="fas fa-shopping-basket mr-1"></i> Operaciones de Procura
                    </h5>
                </div>
                <div class="card-body p-2">
                    <a href="{{ route('admin.rfq.create') }}" class="btn btn-light btn-block text-left p-3 mb-2 border action-btn-dashboard">
                        <i class="fas fa-file-invoice text-purple mr-2" style="font-size: 16px;"></i> Nueva Solicitud de Cotización (RFQ)
                    </a>
                    <a href="{{ route('admin.purchaseOrders.create') }}" class="btn btn-light btn-block text-left p-3 mb-2 border action-btn-dashboard">
                        <i class="fas fa-shopping-cart text-success mr-2" style="font-size: 16px;"></i> Nueva Orden de Compra (PO)
                    </a>
                    <a href="{{ route('admin.stock-in.create') }}" class="btn btn-light btn-block text-left p-3 mb-0 border action-btn-dashboard">
                        <i class="fas fa-truck-loading text-info mr-2" style="font-size: 16px;"></i> Registrar Entrada de Stock (StockIn)
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

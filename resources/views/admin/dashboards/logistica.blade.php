@extends('adminlte::page')

@section('title', 'Panel de Logística')
@section('plugins.Chartjs', true) {{-- Necesario para el gráfico --}}

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck-loading text-info"></i> Panel de Control Logístico</h1>
        <small class="text-muted">Operaciones Diarias</small>
    </div>
@stop

@section('content')
    {{-- 1. TARJETAS DE ESTADO (KPIs) --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            {{-- Solicitudes Pendientes --}}
            <x-adminlte-small-box title="{{ $pendingRequests }}" text="Solicitudes por Aprobar" theme="warning" icon="fas fa-clipboard-list" url="{{ route('admin.requests.index') }}"/>
        </div>
        <div class="col-lg-4 col-6">
            {{-- Stock Crítico --}}
            <x-adminlte-small-box title="{{ $lowStockCount }}" text="Productos en Stock Crítico" theme="danger" icon="fas fa-exclamation-triangle" url="{{ route('admin.reports.stock') }}"/>
        </div>
        <div class="col-lg-4 col-12">
            {{-- Salidas Hoy --}}
            <x-adminlte-small-box title="{{ $approvedRequestsToday }}" text="Salidas Procesadas Hoy" theme="success" icon="fas fa-check-circle" url="#"/>
        </div>
    </div>

    {{-- 2. SECCIÓN CENTRAL: TABLA CRÍTICA Y ACCIONES RÁPIDAS --}}
    <div class="row">
        {{-- TABLA DE STOCK BAJO (Prioridad Alta) --}}
        <div class="col-md-8">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Alertas de Reabastecimiento (Top 5)</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reports.stock') }}" class="btn btn-tool btn-sm">Ver Todo</a>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Stock Actual</th>
                                <th class="text-center">Mínimo</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $product)
                            <tr>
                                <td>
                                    {{ $product->name }}
                                    <br><small class="text-muted">{{ $product->code }}</small>
                                </td>
                                <td class="text-center text-danger font-weight-bold" style="font-size: 1.1em;">
                                    {{ $product->stock }}
                                </td>
                                <td class="text-center">{{ $product->min_stock }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.stock-in.create') }}" class="btn btn-xs btn-success shadow-sm">
                                        <i class="fas fa-plus-circle"></i> Reabastecer
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-success py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        ¡Excelente! No hay productos en nivel crítico.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- GRÁFICO DE MOVIMIENTOS (Opcional, si quieres mostrarlo) --}}
            <div class="card card-info card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Flujo de Salidas (7 días)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="logisticsLineChart" style="min-height: 200px; height: 200px; max-height: 200px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        {{-- COLUMNA LATERAL: RESUMEN Y ACCESOS --}}
        <div class="col-md-4">
            {{-- Resumen Financiero/Global --}}
            <div class="info-box mb-3 bg-info">
                <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Productos</span>
                    <span class="info-box-number">{{ $totalProducts }}</span>
                </div>
            </div>
            <div class="info-box mb-3 bg-secondary">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Inventario</span>
                    <span class="info-box-number">${{ number_format($totalStockValue, 2) }}</span>
                </div>
            </div>

            {{-- Accesos Rápidos (Botones estilo App) --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Gestión Rápida</h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Operaciones Frecuentes</p>
                    
                    <a href="{{ route('admin.stock-in.create') }}" class="btn btn-app bg-light border">
                        <i class="fas fa-truck-loading text-success"></i> Entrada
                    </a>
                    <a href="{{ route('admin.requests.create') }}" class="btn btn-app bg-light border">
                        <i class="fas fa-clipboard-list text-primary"></i> Salida
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-app bg-light border">
                        <i class="fas fa-plus-square text-info"></i> Producto
                    </a>
                    <a href="{{ route('admin.kits.create') }}" class="btn btn-app bg-light border">
                        <i class="fas fa-box-open text-purple"></i> Nuevo Kit
                    </a>
                    
                    <hr>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-block btn-default btn-sm">
                        <i class="fas fa-address-book mr-1"></i> Directorio Proveedores
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(function() {
        'use strict'
        
        // Gráfico de Líneas para Logística (Mismos datos que Admin pero enfocado en operación)
        var lineCtx = document.getElementById('logisticsLineChart').getContext('2d');
        var lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($lineChartLabels),
                datasets: [{
                    label: 'Solicitudes Creadas',
                    data: @json($lineChartData),
                    backgroundColor: 'rgba(23, 162, 184, 0.2)', // Info (Cyan claro)
                    borderColor: '#17a2b8', // Info (Cyan)
                    pointRadius: 3,
                    pointBackgroundColor: '#17a2b8',
                    fill: true,
                    tension: 0.4
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
    });
</script>
@endpush
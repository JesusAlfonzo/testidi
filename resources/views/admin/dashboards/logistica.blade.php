@extends('adminlte::page')

@section('title', 'Panel de Logística')

@section('content_header')
    <h1><i class="fas fa-truck-loading"></i> Panel de Control Logístico</h1>
@stop

@section('content')
    {{-- 1. TARJETAS OPERATIVAS --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            {{-- Prioridad 1: Qué debo atender ya --}}
            <x-adminlte-small-box title="{{ $pendingRequests }}" text="Solicitudes por Aprobar" theme="warning" icon="fas fa-clipboard-list" url="{{ route('admin.requests.index') }}"/>
        </div>
        <div class="col-lg-4 col-6">
            {{-- Prioridad 2: Qué debo comprar ya --}}
            <x-adminlte-small-box title="{{ $lowStockCount }}" text="Productos en Stock Crítico" theme="danger" icon="fas fa-exclamation-triangle" url="{{ route('admin.reports.stock') }}"/>
        </div>
        <div class="col-lg-4 col-12">
            {{-- Prioridad 3: Rendimiento de hoy --}}
            <x-adminlte-small-box title="{{ $approvedRequestsToday }}" text="Salidas Procesadas Hoy" theme="success" icon="fas fa-check-circle" url="#"/>
        </div>
    </div>

    <div class="row">
        {{-- TABLA DE STOCK BAJO (Operativo) --}}
        <div class="col-md-8">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Alertas de Reabastecimiento</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead><tr><th>Producto</th><th>Stock Actual</th><th>Mínimo</th><th>Acción</th></tr></thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td class="text-danger font-weight-bold">{{ $product->stock }}</td>
                                <td>{{ $product->min_stock }}</td>
                                <td><a href="{{ route('admin.stock-in.create') }}" class="btn btn-xs btn-success">Reabastecer</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <x-adminlte-info-box title="Total Productos" text="{{ $totalProducts }}" icon="fas fa-cubes" theme="info"/>
            <x-adminlte-info-box title="Valor Inventario" text="${{ number_format($totalStockValue, 2) }}" icon="fas fa-dollar-sign" theme="secondary"/>
        </div>
    </div>
@stop
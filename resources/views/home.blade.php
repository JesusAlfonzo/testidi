@extends('adminlte::page')

{{-- Título de la página que aparecerá en el navegador --}}
@section('title', 'Dashboard de Inventario')

{{-- Contenido del header (generalmente vacío) --}}
@section('content_header')
    <h1 class="m-0 text-dark">Dashboard de Inventario</h1>
@stop

{{-- Contenido principal de la página --}}
@section('content')
    <div class="row">
        {{-- --------------------------------- TARJETAS DE ACCIÓN INMEDIATA --------------------------------- --}}

        {{-- 1. SOLICITUDES PENDIENTES --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $pendingRequests }}" text="Solicitudes Pendientes" theme="warning" icon="fas fa-fw fa-hourglass-half" url="{{ route('admin.requests.index') }}" url-text="Ver todas"/>
        </div>

        {{-- 2. PRODUCTOS CON STOCK BAJO --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $lowStockCount }}" text="Stock Crítico (Reordenar)" theme="danger" icon="fas fa-fw fa-exclamation-triangle" url="{{ route('admin.products.index') }}?stock=low" url-text="Ver Productos"/>
        </div>

        {{-- 3. VALOR TOTAL DEL INVENTARIO --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="Valor Inventario" text="${{ number_format($totalStockValue, 2) }}" icon="fas fa-fw fa-dollar-sign" theme="success" />
        </div>

        {{-- 4. SALIDAS APROBADAS HOY --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="Aprobadas Hoy" text="{{ $approvedRequestsToday }}" icon="fas fa-fw fa-check-circle" theme="info" />
        </div>
    </div>

    <hr>

    {{-- --------------------------------- SEGUNDA FILA: LISTADO Y GRÁFICO --------------------------------- --}}
    <div class="row">

        {{-- --------------------------------- PANEL DE GRÁFICO (COLUMNA IZQUIERDA) --------------------------------- --}}
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Estado de Solicitudes (Últimos 7 Días)</h3>
                </div>
                <div class="card-body">
                    <canvas id="requestStatusChart" style="height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        {{-- --------------------------------- LISTADO DE STOCK BAJO (COLUMNA DERECHA) --------------------------------- --}}
        <div class="col-md-6">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Top 5 Productos con Stock Bajo</h3>
                </div>
                <div class="card-body">
                    @php
                        // Nota: Cargamos los productos aquí para mantener el controlador más limpio, aunque idealmente iría en el controlador.
                        $lowStockProducts = App\Models\Product::where('is_active', true)
                                                                ->whereColumn('stock', '<=', 'min_stock')
                                                                ->orderBy('stock', 'asc')
                                                                ->limit(5)
                                                                ->get();
                    @endphp

                    @if($lowStockProducts->isNotEmpty())
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Producto</th><th>Stock Actual</th><th>Mínimo</th></tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td><span class="badge badge-danger">{{ $product->stock }}</span></td>
                                    <td>{{ $product->min_stock }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-success">
                            ¡Buen trabajo! No hay productos en stock crítico.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

{{-- --------------------------------- SECCIÓN DE SCRIPTS (Chart.js) --------------------------------- --}}
@push('js')
<script>
    // 1. Obtener el contexto del lienzo
    var ctx = document.getElementById('requestStatusChart').getContext('2d');

    // 2. Definir datos DINÁMICOS del gráfico
    var chartData = {
        labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
        datasets: [{
            label: 'Solicitudes (7 Días)',
            data: [
                {{ $chartPending }},
                {{ $chartApproved }},
                {{ $chartRejected }}
            ],
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',  // Warning (Pendientes)
                'rgba(40, 167, 69, 0.8)',  // Success (Aprobadas)
                'rgba(220, 53, 69, 0.8)'   // Danger (Rechazadas)
            ],
            borderColor: [
                'rgba(255, 193, 7, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
        }]
    };

    // 3. Configuración y Creación del Gráfico
    var requestChart = new Chart(ctx, {
        type: 'bar', // Tipo de gráfico: barras
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush

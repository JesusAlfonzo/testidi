@extends('adminlte::page')

{{-- Título de la página --}}
@section('title', 'Dashboard de Inventario')

{{-- Header (Contenido) --}}
@section('content_header')
    <h1 class="m-0 text-dark">Dashboard de Inventario</h1>
@stop

{{-- Contenido Principal --}}
@section('content')
    {{-- --------------------------------- FILA 1: KPIs PRINCIPALES --------------------------------- --}}
    <div class="row">
        {{-- 1. SOLICITUDES PENDIENTES --}}
        <div class="col-lg-3 col-6">
            {{-- Asumiendo que tu ruta se llama 'admin.inventory_requests.index' --}}
            <x-adminlte-small-box title="{{ $pendingRequests }}" text="Solicitudes Pendientes" theme="warning"
                icon="fas fa-fw fa-hourglass-half" url="{{ route('admin.requests.index') }}" url-text="Ver todas" />
        </div>

        {{-- 2. PRODUCTOS CON STOCK BAJO --}}
        <div class="col-lg-3 col-6">
            {{-- Asumiendo que tu ruta se llama 'admin.products.index' --}}
            <x-adminlte-small-box title="{{ $lowStockCount }}" text="Stock Crítico (Reordenar)" theme="danger"
                icon="fas fa-fw fa-exclamation-triangle" url="{{ route('admin.products.index') }}?stock=low"
                url-text="Ver Productos" />
        </div>

        {{-- 3. VALOR TOTAL DEL INVENTARIO --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="Valor Inventario" text="${{ number_format($totalStockValue, 2) }}"
                icon="fas fa-fw fa-dollar-sign" theme="success" />
        </div>

        {{-- 4. SALIDAS APROBADAS HOY --}}
        <div class="col-lg-3 col-6">
            <x-adminlte-info-box title="Aprobadas Hoy" text="{{ $approvedRequestsToday }}" icon="fas fa-fw fa-check-circle"
                theme="info" />
        </div>
    </div>

    {{-- --------------------------------- FILA 2: GRÁFICO DE ESTADO Y STOCK BAJO --------------------------------- --}}
    <div class="row">

        {{-- COLUMNA IZQUIERDA: GRÁFICO DE ESTADO (DONUT) --}}
        <div class="col-md-5">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Estado de Solicitudes (Últimos 7 Días)</h3>
                </div>
                <div class="card-body">
                    <canvas id="requestStatusDonutChart"
                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: LISTADO DE STOCK BAJO --}}
        <div class="col-md-7">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Top 5 Productos con Stock Bajo</h3>
                </div>
                <div class="card-body p-0">
                    @if ($lowStockProducts->isNotEmpty())
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 100px;">Stock Actual</th>
                                    <th style="width: 100px;">Mínimo</th>
                                    <th style="width: 80px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td><span class="badge badge-danger">{{ $product->stock }}</span></td>
                                        <td><span class="badge badge-secondary">{{ $product->min_stock }}</span></td>
                                        <td>
                                            {{-- Asumiendo que tu ruta se llama 'admin.products.edit' --}}
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="btn btn-xs btn-default">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-success m-3">
                            ¡Buen trabajo! No hay productos en stock crítico.
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- --------------------------------- FILA 3: GRÁFICO DE TENDENCIA Y ACTIVIDAD --------------------------------- --}}
    <div class="row">
        {{-- COLUMNA IZQUIERDA: GRÁFICO DE LÍNEAS (TENDENCIA) --}}
        <div class="col-md-7">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Movimiento de Solicitudes (Últimos 7 Días)</h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="dailyRequestsLineChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: ACTIVIDAD RECIENTE --}}
        <div class="col-md-5">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Últimas Solicitudes Procesadas</h3>
                </div>
                <div class="card-body p-0">
                    @if ($recentProcessedRequests->isNotEmpty())
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentProcessedRequests as $request)
                                    <tr>
                                        <td>#{{ $request->id }}</td>
                                        
                                        <td>{{ $request->requester->name ?? 'N/A' }}</td> 
                                        
                                        <td>
                                            @if ($request->status == 'Approved')
                                                <span class="badge badge-success">Aprobada</span>
                                            @elseif($request->status == 'Rejected')
                                                <span class="badge badge-danger">Rechazada</span>
                                            @endif
                                        </td>
                                        {{-- Muestra hace cuánto tiempo fue procesada --}}
                                        <td>{{ $request->processed_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-light m-3">
                            No hay actividad procesada recientemente.
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
        $(function() {
            'use strict'

            // ------------------------------------
            // GRÁFICO DE DONUT - ESTADO DE SOLICITUDES
            // ------------------------------------
            var donutChartCanvas = $('#requestStatusDonutChart').get(0).getContext('2d');
            var donutData = {
                labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                datasets: [{
                    // Usamos las variables que vienen del HomeController
                    data: [
                        {{ $chartPending }},
                        {{ $chartApproved }},
                        {{ $chartRejected }}
                    ],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)', // Warning (Pendientes)
                        'rgba(40, 167, 69, 0.8)', // Success (Aprobadas)
                        'rgba(220, 53, 69, 0.8)' // Danger (Rechazadas)
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                }]
            };
            var donutOptions = {
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    position: 'bottom' // Mover la leyenda abajo
                }
            }
            // Crear el gráfico de Donut
            new Chart(donutChartCanvas, {
                type: 'doughnut',
                data: donutData,
                options: donutOptions
            });

            // ------------------------------------
            // GRÁFICO DE LÍNEAS - SOLICITUDES POR DÍA
            // ------------------------------------
            var lineChartCanvas = $('#dailyRequestsLineChart').get(0).getContext('2d');

            // Estos datos vienen del HomeController
            var lineLabels = @json($lineChartLabels);
            var lineData = @json($lineChartData);

            var lineChartData = {
                labels: lineLabels,
                datasets: [{
                    label: 'Solicitudes Creadas',
                    data: lineData,
                    backgroundColor: 'rgba(0, 123, 255, 0.1)', // Info (Azul)
                    borderColor: 'rgba(0, 123, 255, 1)',
                    pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                    fill: true,
                    tension: 0.1 // Curva suave
                }]
            };

            var lineChartOptions = {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Asegurarse de que solo haya enteros en el eje Y
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Ocultar leyenda para un look más limpio
                    }
                }
            }

            // Crear el gráfico de Líneas
            new Chart(lineChartCanvas, {
                type: 'line',
                data: lineChartData,
                options: lineChartOptions
            });
        });
    </script>
@endpush
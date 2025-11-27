@extends('adminlte::page')

@section('title', 'Supervisión de Inventario')
@section('plugins.Chartjs', true)

@section('content_header')
    <h1><i class="fas fa-eye"></i> Supervisión Operativa</h1>
@stop

@section('content')
    {{-- 1. KPIS OPERATIVOS --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $pendingRequests }}" text="Pendientes" theme="warning" icon="fas fa-clock" url="{{ route('admin.requests.index') }}"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $lowStockCount }}" text="Stock Crítico" theme="danger" icon="fas fa-exclamation-triangle" url="{{ route('admin.reports.stock') }}"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $totalProducts }}" text="Total Productos" theme="info" icon="fas fa-boxes"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="{{ $approvedRequestsToday }}" text="Salidas Hoy" theme="success" icon="fas fa-dolly"/>
        </div>
    </div>

    {{-- 2. GRÁFICOS --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Actividad Semanal</h3>
                </div>
                <div class="card-body">
                    <canvas id="supervisorLineChart" style="height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Distribución de Estado</h3>
                </div>
                <div class="card-body">
                    <canvas id="supervisorDonutChart" style="height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(function() {
        // Gráfico de Línea (Igual al admin pero con ID único para evitar conflictos si se cachea)
        var lineCtx = $('#supervisorLineChart').get(0).getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($lineChartLabels),
                datasets: [{
                    label: 'Solicitudes',
                    data: @json($lineChartData),
                    borderColor: '#17a2b8',
                    fill: false
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
        });

        // Gráfico Donut
        var donutCtx = $('#supervisorDonutChart').get(0).getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                datasets: [{
                    data: [{{ $chartPending }}, {{ $chartApproved }}, {{ $chartRejected }}],
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'bottom' } }
        });
    });
</script>
@endpush
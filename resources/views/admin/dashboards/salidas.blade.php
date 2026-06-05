@extends('adminlte::page')

@section('title', 'Dashboard Salidas | IAC')

@section('css')
<style>
    .kpi-pending-card {
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        transition: transform 0.2s;
    }
    .kpi-pending-card:hover {
        transform: translateY(-3px);
    }
    .kpi-pending-value {
        font-size: 48px;
        font-weight: 800;
        line-height: 1;
    }
    .action-btn-dashboard {
        transition: all 0.2s;
        border-radius: 8px;
    }
    .action-btn-dashboard:hover {
        transform: translateY(-1px);
    }
    .text-orange {
        color: #d97706;
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-arrow-alt-circle-down text-warning mr-2"></i>Dashboard Administrador de Salidas</h1>
            <p class="text-muted mb-0 small">Aprobación de despachos, control FIFO de vencimientos de lotes e identificación de stock de componentes para descomposición de kits.</p>
        </div>
        <div class="text-right d-none d-md-block">
            <span class="text-muted small d-block">Última Actualización</span>
            <span class="text-warning font-weight-bold">{{ now()->format('d M, Y - H:i') }}</span>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- COLUMNA DE DETALLE (IZQUIERDA - 8/12) --}}
        <div class="col-lg-8 col-12">
            
            {{-- 1. SOLICITUDES CRÍTICAS QUE REQUIEREN DESCOMPOSICIÓN DE KITS --}}
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-danger font-weight-bold mb-0">
                        <i class="fas fa-boxes mr-1"></i> Solicitudes Críticas (Falta de Stock Individual)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="small font-weight-bold text-muted text-uppercase">Solicitud</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Solicitante</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Ítem Crítico</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Disponible</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Kits Disponibles</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center" style="width: 100px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($criticalRequests as $request)
                                    @foreach($request->items as $item)
                                        @if($item->product && $item->product->stock < $item->quantity_requested && $item->product->parentKits->isNotEmpty())
                                            <tr>
                                                <td class="align-middle">
                                                    <span class="font-weight-bold">#{{ $request->id }}</span>
                                                    <br><small class="text-muted">{{ $request->created_at->format('d/m/Y') }}</small>
                                                </td>
                                                <td class="align-middle small font-weight-bold">{{ $request->requester->name ?? 'N/A' }}</td>
                                                <td class="align-middle">
                                                    <span class="text-danger font-weight-bold">{{ $item->product->name }}</span>
                                                    <br><small class="text-muted">Requerido: <strong>{{ $item->quantity_requested }}</strong></small>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-danger">{{ $item->product->stock }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    @foreach($item->product->parentKits as $kit)
                                                        @if($kit->stock > 0)
                                                            <span class="badge badge-purple" title="Kit: {{ $kit->name }}">
                                                                <i class="fas fa-cubes"></i> {{ $kit->name }} (Stock: {{ $kit->stock }})
                                                            </span><br>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-sm btn-outline-danger action-btn-dashboard" title="Ver Solicitud y Descomponer">
                                                        <i class="fas fa-tools"></i> Descomponer
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 24px;"></i>
                                            <p class="mb-0 small">No hay solicitudes críticas que requieran descomposición en este momento.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 2. ALERTA DE LOTES PRÓXIMOS A VENCER (FEFO) --}}
            <div class="card card-outline card-warning shadow-sm mt-3">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-orange font-weight-bold mb-0">
                        <i class="fas fa-clock mr-1"></i> Control FIFO/FEFO: Lotes Próximos a Vencer
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="small font-weight-bold text-muted text-uppercase">Producto</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Nro. Lote</th>
                                    <th class="small font-weight-bold text-muted text-uppercase">Fecha Vencimiento</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Cantidad</th>
                                    <th class="small font-weight-bold text-muted text-uppercase text-center">Estado/Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringProducts as $batch)
                                    <tr>
                                        <td class="align-middle font-weight-bold">{{ $batch->product->name ?? 'N/A' }}</td>
                                        <td class="align-middle">{{ $batch->batch_number ?? 'Sin lote' }}</td>
                                        <td class="align-middle">{{ $batch->expiration_date ? $batch->expiration_date->format('d/m/Y') : '-' }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-info">{{ $batch->quantity }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @php $daysLeft = $batch->getDaysUntilExpiry(); @endphp
                                            @if($daysLeft === 0)
                                                <span class="badge badge-warning">Vence Hoy</span>
                                            @elseif($daysLeft < 0)
                                                <span class="badge badge-danger">Vencido</span>
                                            @elseif($daysLeft <= 7)
                                                <span class="badge badge-danger">{{ $daysLeft }} días</span>
                                            @elseif($daysLeft <= 15)
                                                <span class="badge badge-warning">{{ $daysLeft }} días</span>
                                            @else
                                                <span class="badge badge-info">{{ $daysLeft }} días</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-check text-success mb-2" style="font-size: 24px;"></i>
                                            <p class="mb-0 small">No hay lotes con alerta de vencimiento en los próximos 30 días.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DE PANEL LATERAL (DERECHA - 4/12) --}}
        <div class="col-lg-4 col-12 mt-3 mt-lg-0">
            
            {{-- KPI: SOLICITUDES PENDIENTES DE APROBACIÓN --}}
            <div class="kpi-pending-card card shadow-sm text-center py-4">
                <div class="card-body">
                    <h5 class="text-uppercase font-weight-bold small text-white-50 mb-2">Por Aprobar</h5>
                    <div class="kpi-pending-value mb-2">{{ $pendingRequestsCount }}</div>
                    <p class="text-white-50 small mb-3">Solicitudes pendientes de revisión de stock y aprobación.</p>
                    <a href="{{ route('admin.requests.index', ['status' => 'Pending']) }}" class="btn btn-light text-orange font-weight-bold btn-sm action-btn-dashboard py-2 px-3">
                        <i class="fas fa-clipboard-check mr-1"></i> Ir a Solicitudes
                    </a>
                </div>
            </div>

            {{-- ACCIONES RÁPIDAS --}}
            <div class="card card-outline card-secondary shadow-sm mt-3">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-secondary font-weight-bold mb-0">
                        <i class="fas fa-bolt mr-1"></i> Acciones del Supervisor
                    </h5>
                </div>
                <div class="card-body p-2">
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-light btn-block text-left p-3 mb-2 border action-btn-dashboard">
                        <i class="fas fa-list text-primary mr-2"></i> Ver Historial de Salidas
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-block text-left p-3 mb-2 border action-btn-dashboard">
                        <i class="fas fa-history text-success mr-2"></i> Consultar Kardex por Producto
                    </a>
                    <a href="{{ route('admin.reports.stock') }}" class="btn btn-light btn-block text-left p-3 mb-0 border action-btn-dashboard">
                        <i class="fas fa-cubes text-info mr-2"></i> Reporte de Stock Actual
                    </a>
                </div>
            </div>

        </div>
    </div>
@stop

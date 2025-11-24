@extends('adminlte::page')

@section('title', 'Reporte de Solicitudes')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1><i class="fas fa-list-alt"></i> Reporte de Solicitudes</h1>
@stop

@section('css')
    <style>
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
    </style>
@stop

@section('content')
    
    {{-- 🔎 FILTROS DE FECHA Y ESTADO --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.requests') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Desde</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Aprobada</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rechazada</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar Resultados</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Resultados ({{ count($requests) }} registros encontrados)</h3>
            <div class="card-tools">
                {{-- Botones de Exportación: Mantienen los filtros actuales --}}
                <a href="{{ route('admin.reports.requests.excel', request()->query()) }}" class="btn btn-success btn-sm" title="Descargar Excel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.reports.requests.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank" title="Ver PDF">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="requestsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th> {{-- Prio 4 --}}
                            <th>Estado</th> {{-- Prio 1 --}}
                            <th>Fecha Solicitud</th> {{-- Prio 3 --}}
                            <th>Detalle</th> {{-- Prio 2 --}}
                            <th>Solicitante</th> {{-- Ocultable --}}
                            <th>Justificación</th> {{-- Ocultable --}}
                            <th>Procesado por</th> {{-- Ocultable --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            @php
                                $badge = match($request->status) { 
                                    'Pending' => 'warning', 
                                    'Approved' => 'success', 
                                    'Rejected' => 'danger', 
                                    default => 'secondary' 
                                };
                            @endphp
                            <tr>
                                <td>REQ-{{ $request->id }}</td>
                                <td><span class="badge badge-{{ $badge }}">{{ $request->status }}</span></td>
                                <td data-order="{{ $request->requested_at ? $request->requested_at->timestamp : 0 }}">
                                    {{ $request->requested_at ? $request->requested_at->format('Y-m-d H:i') : '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                </td>
                                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($request->justification, 50) }}</td>
                                <td>{{ $request->approver->name ?? 'Pendiente' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const requestsTable = $('#requestsTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false, 
                "order": [[ 2, "desc" ]], // Ordenar por Fecha (índice 2) descendente
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, // Columna Detalle no ordenable
                    { "type": "date", "targets": 2 }, // Columna Fecha
                    // Prioridades Responsive
                    { "responsivePriority": 1, "targets": 1 }, // Estado
                    { "responsivePriority": 2, "targets": 3 }, // Botón Detalle
                    { "responsivePriority": 3, "targets": 2 }, // Fecha
                    { "responsivePriority": 4, "targets": 0 }, // ID
                    { "responsivePriority": 100, "targets": [4, 5, 6] } // Resto ocultable
                ]
            });
            
            // Redibujo para AdminLTE
            setTimeout(function() { requestsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection
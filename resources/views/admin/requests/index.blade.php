@extends('adminlte::page')

@section('title', 'Inventario | Solicitudes de Salida')

@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-clipboard-list"></i> Solicitudes de Salida</h1>
        @can('solicitudes_crear')
            <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Solicitud
            </a>
        @endcan
    </div>
@stop

@section('css')
    <style>
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
    </style>
@stop

@section('content')
    {{-- FILTROS --}}
    <div class="card card-outline card-info collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.requests.index') }}">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Solicitante</label>
                            <select name="requester_id" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach($requesters as $id => $name)
                                    <option value="{{ $id }}" {{ request('requester_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 25%">Solicitante</th>
                                    <th style="width: 15%">Estado</th>
                                    <th style="width: 10%">Acciones</th>
                                    <th>Justificación</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Aprobador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $badgeColors = ['Pending' => 'warning', 'Approved' => 'success', 'Rejected' => 'danger']; @endphp
                                @forelse ($requests as $request)
                                    <tr>
                                        <td>REQ-{{ $request->id }}</td>
                                        <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-{{ $badgeColors[$request->status] ?? 'secondary' }}">{{ $request->status }}</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-default text-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                                @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
                                                    <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-default text-warning" title="Aprobar/Rechazar"><i class="fas fa-check-circle"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($request->justification, 50) }}</td>
                                        <td data-order="{{ optional($request->requested_at)->timestamp }}">{{ optional($request->requested_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $request->approver->name ?? '---' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">No se encontraron solicitudes.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4' });
            
            const requestsTable = $('#requestsTable').DataTable({
                "responsive": true, "paging": true, "lengthChange": true, "searching": true, "ordering": true, "info": true, "autoWidth": false,
                "order": [[ 5, "desc" ]], // Ordenar por fecha (índice 5)
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, { "type": "date", "targets": 5 },
                    { "responsivePriority": 1, "targets": 0 }, { "responsivePriority": 2, "targets": 1 }, { "responsivePriority": 3, "targets": 3 }, { "responsivePriority": 4, "targets": 2 }, { "responsivePriority": 100, "targets": [4, 5, 6] }
                ]
            });
            setTimeout(function() { requestsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection
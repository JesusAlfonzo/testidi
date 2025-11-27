@extends('adminlte::page')

@section('title', 'Inventario | Solicitudes de Salida')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true) {{-- Necesario para el filtro de solicitantes --}}

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
        /* Ajustes para DataTables Responsive */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            left: 4px;
        }

        .table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child {
            padding-left: 10px !important;
        }

        /* Ajuste visual para Select2 en los filtros */
        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@stop

@section('content')

    {{-- 🔎 FILTROS DE BÚSQUEDA --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
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
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pendiente
                                </option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Aprobada
                                </option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rechazada
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Solicitante</label>
                            <select name="requester_id" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach ($requesters as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ request('requester_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-default mr-2">Limpiar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar
                            Resultados</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped table-bordered display nowrap"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 20%">Solicitante</th>
                                    <th style="width: 10%">Estado</th>
                                    <th style="width: 10%">Acciones</th>
                                    <th>Justificación</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Aprobador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $badgeColors = [
                                        'Pending' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                    ];
                                @endphp

                                @forelse ($requests as $request)
                                    <tr>
                                        <td>REQ-{{ $request->id }}</td>
                                        <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $badgeColors[$request->status] ?? 'secondary' }}">
                                                {{-- Usamos el accesor status_label si existe en el modelo, sino el valor raw --}}
                                                {{ $request->status_label ?? $request->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.requests.show', $request) }}"
                                                    class="btn btn-default text-info" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if ($request->status === 'Pending')
                                                    @can('solicitudes_aprobar')
                                                        <a href="{{ route('admin.requests.show', $request) }}"
                                                            class="btn btn-default text-warning"
                                                            title="Revisar y Aprobar/Rechazar">
                                                            <i class="fas fa-check-circle"></i>
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($request->justification, 50) }}</td>
                                        <td data-order="{{ optional($request->requested_at)->timestamp }}">
                                            {{ optional($request->requested_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>{{ $request->approver->name ?? '---' }}</td>
                                    </tr>
                                @empty
                                    {{-- DataTables manejará el vacío, pero dejamos esto por estructura --}}
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
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Inicializar DataTables
            const requestsTable = $('#requestsTable').DataTable({
                "responsive": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [
                    [5, "desc"]
                ], // Ordenar por Fecha Solicitud (índice 5) descendente

                // Traducción Nativa
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay información disponible",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },

                "columnDefs": [{
                        "orderable": false,
                        "targets": [3]
                    }, // Acciones no ordenables
                    {
                        "type": "date",
                        "targets": 5
                    }, // Tipo fecha

                    // Prioridades Responsive para Móvil
                    {
                        "responsivePriority": 1,
                        "targets": 0
                    }, // ID
                    {
                        "responsivePriority": 2,
                        "targets": 2
                    }, // Estado
                    {
                        "responsivePriority": 3,
                        "targets": 3
                    }, // Acciones
                    {
                        "responsivePriority": 4,
                        "targets": 1
                    }, // Solicitante

                    // Ocultar primero
                    {
                        "responsivePriority": 100,
                        "targets": [4, 5, 6]
                    }
                ]
            });

            // Ajuste de renderizado para AdminLTE
            setTimeout(function() {
                requestsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection

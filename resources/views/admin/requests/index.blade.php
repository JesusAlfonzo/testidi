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

@section('content')
    
    {{-- FILTROS --}}
    <div class="card card-outline card-info collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm">
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
                            <select name="requester_id" class="form-control select2" style="width: 100%">
                                <option value="">Todos</option>
                                @foreach($requesters as $id => $name)
                                    <option value="{{ $id }}" {{ request('requester_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar Resultados</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-primary">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Solicitante</th>
                                    <th>Justificación</th>
                                    <th>Estado</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Aprobador</th>
                                    <th>Fecha Procesado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
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

            var table = $('#requestsTable').DataTable({
                responsive: true, 
                processing: true,
                serverSide: true,
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                order: [[4, 'desc']],

                ajax: {
                    url: "{{ route('admin.requests.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.date_from = $('input[name="date_from"]').val();
                        d.date_to = $('input[name="date_to"]').val();
                        d.status = $('select[name="status"]').val();
                        d.requester_id = $('select[name="requester_id"]').val();
                    }
                },

                columns: [
                    { data: 'id', name: 'id', orderable: true },
                    { data: 'requester', name: 'requester_id', orderable: true },
                    { data: 'justification', name: 'justification', orderable: true },
                    { data: 'status', name: 'status', orderable: true },
                    { data: 'date', name: 'requested_at', orderable: true },
                    { data: 'approver', name: 'approver_id', orderable: true },
                    { data: 'processed', name: 'processed_at', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],

                language: {
                    decimal: "",
                    emptyTable: "No hay información disponible",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(Filtrado de _MAX_ total registros)",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "Sin resultados encontrados",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                },

                columnDefs: [
                    { orderable: false, targets: [2, 7] },
                    {
                        targets: [1, 3, 5],
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'type') {
                                var $div = $('<div>');
                                $div.html(data);
                                return $div.text().trim();
                            }
                            return data;
                        }
                    }
                ]
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            addClearFiltersButton('requestsTable', 'filterForm');
            
            setTimeout(function() { 
                table.columns.adjust().responsive.recalc(); 
            }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@endsection

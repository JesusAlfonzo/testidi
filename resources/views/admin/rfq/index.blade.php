@extends('adminlte::page')

@section('title', 'Compras | Solicitudes de Cotización')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-invoice"></i> Solicitudes de Cotización (RFQ)</h1>
        @can('rfq_crear')
            <a href="{{ route('admin.rfq.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva RFQ
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Filtros</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrada</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                            <button type="button" class="btn btn-secondary" id="clearFilters"><i class="fas fa-eraser"></i> Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-info mt-3">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="rfqTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Código</th>
                                    <th style="width: 30%">Título</th>
                                    <th style="width: 15%">Estado</th>
                                    <th style="width: 15%">Fecha Límite</th>
                                    <th style="width: 10%">Items</th>
                                    <th style="width: 15%">Acciones</th>
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
            var table = $('#rfqTable').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 0, "desc" ]],
                "pageLength": 15,
                "lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],

                "ajax": {
                    "url": "{{ route('admin.rfq.index') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.status = $('select[name="status"]').val();
                    }
                },

                "columns": [
                    { "data": "code", "name": "code" },
                    { "data": "title", "name": "title" },
                    { "data": "status", "name": "status" },
                    { "data": "date_required", "name": "date_required" },
                    { "data": "items_count", "name": "items_count" },
                    { "data": "actions", "name": "actions", "orderable": false, "searchable": false }
                ],

                "language": {
                    "decimal": "",
                    "emptyTable": "No hay RFQ registradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
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
                "columnDefs": [
                    { "orderable": false, "targets": [5] }
                ]
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                table.draw();
            });
        });
    </script>
@endsection

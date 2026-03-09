@extends('adminlte::page')

@section('title', 'Compras | Cotizaciones de Proveedores')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-file-alt"></i> Cotizaciones de Proveedores</h1>
        @can('cotizaciones_crear')
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Cotización
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
                        <div class="col-md-3">
                            <select name="rfq_id" class="form-control">
                                <option value="">Todas las RFQ</option>
                                @foreach(\App\Models\RequestForQuotation::orderBy('code')->get() as $rfq)
                                    <option value="{{ $rfq->id }}" {{ request('rfq_id') == $rfq->id ? 'selected' : '' }}>
                                        {{ $rfq->code }} - {{ $rfq->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="supplier_id" class="form-control">
                                <option value="">Todos los proveedores</option>
                                @foreach(\App\Models\Supplier::orderBy('name')->get() as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="selected" {{ request('status') == 'selected' ? 'selected' : '' }}>Seleccionada</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
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
                        <table id="quotationsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Proveedor</th>
                                    <th>RFQ</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
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
            var table = $('#quotationsTable').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 5, "desc" ]],
                "pageLength": 15,
                "lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],

                "ajax": {
                    "url": "{{ route('admin.quotations.index') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.rfq_id = $('select[name="rfq_id"]').val();
                        d.supplier_id = $('select[name="supplier_id"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },

                "columns": [
                    { "data": "code", "name": "code" },
                    { "data": "supplier", "name": "supplier_id" },
                    { "data": "rfq", "name": "rfq_id" },
                    { "data": "total", "name": "total" },
                    { "data": "status", "name": "status" },
                    { "data": "date", "name": "date_issued" },
                    { "data": "actions", "name": "actions", "orderable": false, "searchable": false }
                ],

                "language": {
                    "emptyTable": "No hay cotizaciones registradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "lengthMenu": "Mostrar _MENU_ registros",
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
                    { "orderable": false, "targets": [2, 3] }
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
    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@endsection

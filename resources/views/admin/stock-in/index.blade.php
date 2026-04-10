@extends('adminlte::page')

@section('title', 'Entradas de Stock')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-arrow-alt-circle-up"></i> Entradas de Stock</h1>
        @can('entradas_crear')
            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Entrada
            </a>
        @endcan
    </div>
@stop

@section('content')
    
    {{-- FILTROS DE BÚSQUEDA --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET">
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
                            <label>Producto</label>
                            <select name="product_id" class="form-control select2" style="width: 100%">
                                <option value="">Todos</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-success">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="stockInTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Referencia</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Total</th>
                                    <th>Proveedor</th>
                                    <th>Documento</th>
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

            var table = $('#stockInTable').DataTable({
                responsive: true, 
                processing: true,
                serverSide: true,
                paging: true, 
                lengthChange: true, 
                searching: true, 
                ordering: true, 
                info: true, 
                autoWidth: false,
                order: [[0, 'desc']],

                ajax: {
                    url: "{{ route('admin.stock-in.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.date_from = $('input[name="date_from"]').val();
                        d.date_to = $('input[name="date_to"]').val();
                        d.product_id = $('select[name="product_id"]').val();
                    }
                },

                columns: [
                    { data: 'date', name: 'entry_date', orderable: true },
                    { data: 'reference', name: 'purchase_order_id', orderable: true },
                    { data: 'quantity', name: 'quantity', orderable: false },
                    { data: 'unit_cost', name: 'unit_cost', orderable: false },
                    { data: 'total', name: 'total', orderable: false },
                    { data: 'supplier', name: 'supplier_id', orderable: true },
                    { data: 'document', name: 'document_type', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],

                language: {
                    decimal: "",
                    emptyTable: "No hay entradas registradas",
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
                    { orderable: false, targets: [2, 4, 6, 7] },
                    {
                        targets: [5],
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

            addClearFiltersButton('stockInTable', 'filterForm');
            
            setTimeout(function() { 
                table.columns.adjust().responsive.recalc(); 
            }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection

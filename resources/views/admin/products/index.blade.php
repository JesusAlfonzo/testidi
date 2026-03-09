@extends('adminlte::page')

@section('title', 'Inventario | Productos')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-box-open"></i> Listado de Productos</h1>
        @can('productos_crear')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Agregar Producto
            </a>
        @endcan
    </div>
@stop

@section('content')
    
    {{-- FILTROS AVANZADOS --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros Avanzados</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('admin.products.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="category_id" class="form-control select2">
                                <option value="">Todas</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ubicación</label>
                            <select name="location_id" class="form-control select2">
                                <option value="">Todas</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Alerta Stock</label>
                            <select name="stock_status" class="form-control">
                                <option value="">Cualquiera</option>
                                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Bajo Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Origen</label>
                            <select name="created_on_the_fly" class="form-control">
                                <option value="">Todos</option>
                                <option value="yes" {{ request('created_on_the_fly') == 'yes' ? 'selected' : '' }}>Creado sobre la marcha</option>
                                <option value="no" {{ request('created_on_the_fly') == 'no' ? 'selected' : '' }}>Registrado normalmente</option>
                            </select>
                        </div>
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
                        <table id="productsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Stock</th>
                                    <th>Acciones</th> 
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Ubicación</th>
                                    <th>Costo/Precio</th>
                                    <th>Estado</th>
                                    <th>Origen</th>
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
            $('.select2').select2({ theme: 'bootstrap4' });

            var table = $('#productsTable').DataTable({
                "responsive": true, 
                "processing": true,
                "serverSide": true,
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 0, "asc" ]],
                "pageLength": 15,
                "lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],

                "ajax": {
                    "url": "{{ route('admin.products.index') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.category_id = $('select[name="category_id"]').val();
                        d.location_id = $('select[name="location_id"]').val();
                        d.status = $('select[name="status"]').val();
                        d.stock_status = $('select[name="stock_status"]').val();
                        d.created_on_the_fly = $('select[name="created_on_the_fly"]').val();
                    }
                },

                "columns": [
                    { "data": "name", "name": "name" },
                    { 
                        "data": "stock", 
                        "name": "stock",
                        "render": function(data, type, row) {
                            return '<span class="badge ' + row.stock_class + '">' + data + ' ' + row.unit + '</span>';
                        }
                    },
                    { "data": "actions", "name": "actions", "orderable": false, "searchable": false },
                    { "data": "code", "name": "code" },
                    { "data": "category", "name": "category" },
                    { "data": "location", "name": "location" },
                    { 
                        "data": "cost", 
                        "name": "cost",
                        "render": function(data, type, row) {
                            return '<small class="d-block text-muted">C: $' + data + '</small>P: <strong>$' + row.price + '</strong>';
                        }
                    },
                    { 
                        "data": "is_active", 
                        "name": "is_active",
                        "render": function(data) {
                            return data 
                                ? '<span class="badge badge-info">Activo</span>' 
                                : '<span class="badge badge-secondary">Inactivo</span>';
                        }
                    },
                    { 
                        "data": "created_on_the_fly", 
                        "name": "created_on_the_fly",
                        "render": function(data) {
                            return data 
                                ? '<span class="badge badge-warning" title="Creado desde Cotización/RFQ/OC"><i class="fas fa-bolt"></i> Rápido</span>' 
                                : '<span class="badge badge-secondary">Normal</span>';
                        }
                    }
                ],

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

                "columnDefs": [
                    { "orderable": false, "targets": [2, 6] },
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 2 },
                    { "responsivePriority": 3, "targets": 1 },
                    { "responsivePriority": 100, "targets": [3, 4, 5, 6, 7, 8] }
                ]
            });

            // Aplicar filtros cuando cambien los selects
            $('#filterForm select').on('change', function() {
                table.draw();
            });
            
            setTimeout(function() { 
                table.columns.adjust().responsive.recalc(); 
            }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection

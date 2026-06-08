@extends('adminlte::page')

@section('title', 'Inventario | Productos')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        /* Card & Filter Header Styles */
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: none;
            margin-bottom: 2rem;
        }
        
        .filter-section {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Datatable Styling */
        #productsTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #productsTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #productsTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #productsTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #productsTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #productsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #productsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Premium Badge Styles */
        .badge {
            padding: 0.5em 0.85em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: inline-block;
        }

        .badge-purple {
            background-color: #f3e8ff !important;
            color: #7e22ce !important;
            border: 1px solid #e9d5ff !important;
        }

        .badge-secondary {
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
            border: 1px solid #e5e7eb !important;
        }

        .badge-info {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd !important;
        }

        .badge-warning {
            background-color: #ffedd5 !important;
            color: #ea580c !important;
            border: 1px solid #fed7aa !important;
        }

        .badge-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }

        .badge-danger {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5 !important;
        }
        
        .btn-show-components:hover {
            text-decoration: none;
            opacity: 0.85;
        }
        
        .btn-show-components {
            transition: all 0.2s ease;
        }
        
        /* Inner Table Styles */
        .card-inner {
            border-left: 4px solid #7e22ce !important;
            border-radius: 6px;
            margin: 4px 16px 12px 16px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-box-open text-primary mr-2"></i> Inventario de Productos
            </h1>
            <p class="text-muted mb-0">Gestión de stock, insumos y kits compuestos de Inmunología.</p>
        </div>
        @can('productos_crear')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Agregar Producto
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            {{-- FILTROS DE BÚSQUEDA --}}
            <div class="card filter-section p-3 mb-3">
                <form id="filterForm" method="GET" action="{{ route('admin.products.index') }}" class="row align-items-end">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-boxes mr-1"></i> Tipo de Ítem
                        </label>
                        <select name="type" class="form-control" style="border-radius: 8px;">
                            <option value="">Todos</option>
                            <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individuales</option>
                            <option value="composite_kit" {{ request('type') == 'composite_kit' ? 'selected' : '' }}>Kits / Compuestos</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-tags mr-1"></i> Categoría
                        </label>
                        <select name="category_id" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todas</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" {{ request('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-map-marker-alt mr-1"></i> Ubicación
                        </label>
                        <select name="location_id" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todas</option>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-toggle-on mr-1"></i> Estado
                        </label>
                        <select name="status" class="form-control" style="border-radius: 8px;">
                            <option value="">Todos</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Alerta Stock
                        </label>
                        <select name="stock_status" class="form-control" style="border-radius: 8px;">
                            <option value="">Cualquiera</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Bajo Stock</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-bolt mr-1"></i> Origen
                        </label>
                        <select name="created_on_the_fly" class="form-control" style="border-radius: 8px;">
                            <option value="">Todos</option>
                            <option value="yes" {{ request('created_on_the_fly') == 'yes' ? 'selected' : '' }}>Rápido</option>
                            <option value="no" {{ request('created_on_the_fly') == 'no' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-secondary font-weight-bold w-100" id="clearFilters" style="border-radius: 8px;">
                            <i class="fas fa-undo mr-1"></i> Resetear
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla Principal --}}
            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 25%">Nombre</th>
                                <th style="width: 15%">Tipo</th>
                                <th style="width: 10%">Stock</th>
                                <th style="width: 8%" class="text-right">Acciones</th>
                                <th style="width: 10%">Código</th>
                                <th style="width: 10%">Categoría</th>
                                <th style="width: 10%">Ubicación</th>
                                <th style="width: 8%">Estado</th>
                                <th style="width: 8%">Origen</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Configurar CSRF para todas las peticiones AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({ theme: 'bootstrap4' });
            
            $(document).on('select2:open', function() {
                setTimeout(function() {
                    var dropdown = document.querySelector('.select2-dropdown');
                    if (dropdown) {
                        dropdown.style.maxHeight = '350px';
                        dropdown.style.overflow = 'hidden';
                        var results = dropdown.querySelector('.select2-results');
                        if (results) {
                            results.style.maxHeight = '350px';
                            results.style.overflowY = 'auto';
                        }
                    }
                }, 10);
            });

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
                        d.type = $('select[name="type"]').val();
                        d.category_id = $('select[name="category_id"]').val();
                        d.location_id = $('select[name="location_id"]').val();
                        d.status = $('select[name="status"]').val();
                        d.stock_status = $('select[name="stock_status"]').val();
                        d.created_on_the_fly = $('select[name="created_on_the_fly"]').val();
                    }
                },

                "columns": [
                    { "data": "id", "name": "id" },
                    { 
                        "data": "name", 
                        "name": "name",
                        "render": function(data, type, row) {
                            var badges = '';
                            if (row.is_generic) {
                                badges += ' <span class="badge badge-secondary" title="Producto Genérico"><i class="fas fa-tags"></i> Genérico</span>';
                            }
                            if (row.type === 'composite_kit') {
                                return '<span class="text-primary font-weight-bold btn-show-components" style="cursor: pointer;" title="Haga clic para ver componentes"><i class="fas fa-cubes"></i> ' + data + '</span>' + badges;
                            }
                            return '<span>' + data + '</span>' + badges;
                        }
                    },
                    { 
                        "data": "type", 
                        "name": "type",
                        "render": function(data) {
                            return data === 'composite_kit' 
                                ? '<span class="badge badge-purple"><i class="fas fa-cubes"></i> Kit / Compuesto</span>' 
                                : '<span class="badge badge-secondary"><i class="fas fa-cube"></i> Individual</span>';
                        }
                    },
                    { 
                        "data": "stock", 
                        "name": "stock",
                        "render": function(data, type, row) {
                            return '<span class="badge ' + row.stock_class + '">' + data + '</span>';
                        }
                    },
                    { "data": "actions", "name": "actions", "orderable": false, "searchable": false },
                    { "data": "code", "name": "code" },
                    { "data": "category", "name": "category" },
                    { "data": "location", "name": "location" },
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
                    { "orderable": false, "targets": [4] },
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 3 }, // stock
                    { "responsivePriority": 3, "targets": 1 }, // name
                    { "responsivePriority": 100, "targets": [2, 4, 5, 6, 7, 8, 9] }
                ]
            });

            // Aplicar filtros cuando cambien los selects
            $('#filterForm select, #filterForm input').on('change', function() {
                table.draw();
            });

            // Resetear filtros
            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val('').trigger('change');
                table.draw();
            });

            // Manejador para expandir/colapsar fila secundaria del kit
            $('#productsTable tbody').on('click', '.btn-show-components', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(formatComponents(row.data())).show();
                    tr.addClass('shown');
                }
            });

            function formatComponents(d) {
                if (d.type !== 'composite_kit' || !d.components || d.components.length === 0) {
                    return '<div class="p-2 text-muted">Este kit no tiene componentes asociados.</div>';
                }
                
                var html = '<div class="p-3 bg-light rounded border card-inner">';
                html += '<strong class="text-secondary"><i class="fas fa-tools"></i> Componentes del Kit compuesto:</strong>';
                html += '<table class="table table-sm table-striped table-bordered mt-2 mb-0" style="background-color: white;">';
                html += '<thead class="thead-light"><tr><th>Componente</th><th>Código</th><th style="width: 20%;" class="text-center">Cantidad requerida</th></tr></thead>';
                html += '<tbody>';
                
                d.components.forEach(function(comp) {
                    html += '<tr>';
                    html += '<td>' + comp.name + '</td>';
                    html += '<td>' + (comp.code || 'N/A') + '</td>';
                    html += '<td class="text-center font-weight-bold">' + comp.quantity + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                return html;
            }
            
            setTimeout(function() {
                table.columns.adjust().responsive.recalc();
            }, 500);

            $(document).on('click', '.btn-delete-product', function(e) {
                e.preventDefault();
                let deleteUrl = $(this).data('url');

                Swal.fire({
                    title: '¿Está seguro de eliminar este producto?',
                    text: "Esta acción inhabilitará el ítem para proteger las auditorías.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result === true || result.value === true || result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire('¡Eliminado!', response.message || 'El producto ha sido inactivado.', 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el registro. Intente de nuevo.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

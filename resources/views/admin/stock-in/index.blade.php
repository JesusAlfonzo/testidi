@extends('adminlte::page')

@section('title', 'Entradas de Stock')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)

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
        #stockInTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #stockInTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #stockInTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #stockInTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #stockInTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #stockInTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #stockInTable tbody tr td:last-child {
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

        .badge-danger-light {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5 !important;
        }

        .toggle-child-row {
            width: 24px;
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        /* Inner Table Styles */
        .card-inner {
            border-left: 4px solid #10b981 !important;
            border-radius: 6px;
            margin: 4px 16px 12px 16px;
        }
        
        .text-xxs {
            font-size: 0.7rem;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-arrow-alt-circle-up text-primary mr-2"></i> Entradas de Stock
            </h1>
            <p class="text-muted mb-0">Gestión de ingresos de mercancía a almacén y trazabilidad de lotes.</p>
        </div>
        @can('entradas_crear')
            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Entrada
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
                <form id="filterForm" class="row align-items-end">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-calendar-alt mr-1"></i> Desde
                        </label>
                        <input type="date" name="date_from" class="form-control" style="border-radius: 8px;" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-calendar-alt mr-1"></i> Hasta
                        </label>
                        <input type="date" name="date_to" class="form-control" style="border-radius: 8px;" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-building mr-1"></i> Proveedor
                        </label>
                        <select name="supplier_id" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos los proveedores</option>
                            @foreach($suppliers as $id => $name)
                                <option value="{{ $id }}" {{ request('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-file-invoice mr-1"></i> Factura
                        </label>
                        <input type="text" name="invoice_number" class="form-control" placeholder="Número..." style="border-radius: 8px;" value="{{ request('invoice_number') }}">
                    </div>
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1 font-weight-bold mr-2" style="border-radius: 8px;">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-outline-secondary font-weight-bold" id="clearFilters" title="Limpiar Filtros" style="border-radius: 8px;">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla Principal --}}
            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="stockInTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%"></th>
                                <th style="width: 12%">Fecha</th>
                                <th style="width: 15%">Referencia</th>
                                <th style="width: 10%">Cantidad</th>
                                <th style="width: 12%">Costo Prom.</th>
                                <th style="width: 12%">Total</th>
                                <th style="width: 20%">Proveedor</th>
                                <th style="width: 10%">Documento</th>
                                <th style="width: 4%" class="text-right">Acciones</th>
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
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#stockInTable').DataTable({
                responsive: false, 
                processing: true,
                serverSide: true,
                paging: true, 
                lengthChange: true, 
                searching: true, 
                ordering: true, 
                info: true, 
                autoWidth: false,
                order: [[1, 'desc']],

                ajax: {
                    url: "{{ route('admin.stock-in.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.date_from = $('input[name="date_from"]').val();
                        d.date_to = $('input[name="date_to"]').val();
                        d.supplier_id = $('select[name="supplier_id"]').val();
                        d.invoice_number = $('input[name="invoice_number"]').val();
                    }
                },

                columns: [
                    {
                        className: 'text-center align-middle',
                        orderable: false,
                        data: null,
                        defaultContent: '<button class="btn btn-xs btn-outline-primary toggle-child-row" type="button"><i class="fas fa-plus"></i></button>'
                    },
                    { data: 'date', name: 'entry_date', orderable: true, className: 'align-middle' },
                    { data: 'reference', name: 'purchase_order_id', orderable: true, className: 'align-middle' },
                    { data: 'quantity', name: 'quantity', orderable: false, className: 'align-middle' },
                    { data: 'unit_cost', name: 'unit_cost', orderable: false, className: 'align-middle text-monospace' },
                    { data: 'total', name: 'total', orderable: false, className: 'align-middle text-monospace font-weight-bold' },
                    { data: 'supplier', name: 'supplier_id', orderable: true, className: 'align-middle' },
                    { data: 'document', name: 'document_type', orderable: true, className: 'align-middle' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right align-middle' }
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
                    { orderable: false, targets: [0, 3, 4, 5, 8] }
                ]
            });

            // Función para renderizar la subtabla de ítems recibidos
            function formatChildRow(d) {
                var html = '<div class="card card-inner shadow-sm p-3 bg-light">';
                html += '<h6 class="text-success font-weight-bold mb-2"><i class="fas fa-boxes"></i> Productos Recibidos en esta Entrada</h6>';
                html += '<table class="table table-sm bg-white m-0 rounded" style="border-collapse: collapse;">';
                html += '<thead class="thead-light"><tr>';
                html += '<th class="text-xxs uppercase p-2">Código</th>';
                html += '<th class="text-xxs uppercase p-2">Producto</th>';
                html += '<th class="text-xxs uppercase p-2 text-center">Cantidad</th>';
                html += '<th class="text-xxs uppercase p-2">Costo Unit.</th>';
                html += '<th class="text-xxs uppercase p-2">Lote</th>';
                html += '<th class="text-xxs uppercase p-2">Vencimiento</th>';
                html += '<th class="text-xxs uppercase p-2">Ubicación</th>';
                html += '<th class="text-xxs uppercase p-2 text-center">Estado</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                
                if (d.items_data && d.items_data.length > 0) {
                    d.items_data.forEach(function(item) {
                        var statusBadge = item.status === 'Recibido' 
                            ? '<span class="badge badge-success text-xxs">Recibido</span>' 
                            : '<span class="badge badge-danger text-xxs">Rechazado</span>';
                        html += '<tr>';
                        html += '<td class="p-2 align-middle"><code>' + item.product_code + '</code></td>';
                        html += '<td class="p-2 align-middle">' + item.product_name + '</td>';
                        html += '<td class="p-2 text-center align-middle"><strong>' + item.quantity + '</strong></td>';
                        html += '<td class="p-2 align-middle">' + item.unit_cost + '</td>';
                        html += '<td class="p-2 align-middle"><span class="badge badge-secondary text-xxs">' + item.batch_number + '</span></td>';
                        html += '<td class="p-2 align-middle">' + item.expiration_date + '</td>';
                        html += '<td class="p-2 align-middle"><i class="fas fa-map-marker-alt text-muted mr-1"></i>' + item.warehouse_location + '</td>';
                        html += '<td class="p-2 text-center align-middle">' + statusBadge + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="8" class="text-center text-muted p-2">No hay detalles de ítems registrados para esta entrada.</td></tr>';
                }
                
                html += '</tbody></table></div>';
                return html;
            }

            // Listener para la expansión de filas
            $('#stockInTable tbody').on('click', 'button.toggle-child-row', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
         
                if (row.child.isShown()) {
                    $(this).html('<i class="fas fa-plus"></i>').removeClass('btn-outline-danger').addClass('btn-outline-primary');
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    $(this).html('<i class="fas fa-minus"></i>').removeClass('btn-outline-primary').addClass('btn-outline-danger');
                    row.child(formatChildRow(row.data())).show();
                    tr.addClass('shown');
                }
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val('').trigger('change');
                table.draw();
            });
            
            setTimeout(function() { 
                table.columns.adjust(); 
            }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection

@extends('adminlte::page')

@section('title', 'Compras | Solicitudes de Cotización')

@section('plugins.Datatables', true)
@section('plugins.Responsive', true)

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
        #rfqTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #rfqTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #rfqTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #rfqTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #rfqTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #rfqTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #rfqTable tbody tr td:last-child {
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
            color: #6b21a8 !important;
            border: 1px solid #d8b4fe !important;
        }

        .badge-secondary {
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
            border: 1px solid #e5e7eb !important;
        }

        .badge-warning {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
            border: 1px solid #fde68a !important;
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

        .badge-info {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd !important;
        }

        /* Expand Button */
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
            border-left: 4px solid #3b82f6 !important;
            border-radius: 6px;
            margin: 4px 16px 12px 16px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-file-invoice mr-2 text-primary"></i> Solicitudes de Cotización (RFQ)
            </h1>
            <p class="text-muted mb-0">Gestión, seguimiento y comparativa de presupuestos con proveedores.</p>
        </div>
        @can('rfq_crear')
            <a href="{{ route('admin.rfq.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Nueva RFQ
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <!-- Filtros Superiores Reactivos -->
            <div class="card filter-section p-3 mb-3">
                <form id="filterForm" class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-filter mr-1"></i> Estado
                        </label>
                        <select name="status" class="form-control" style="border-radius: 8px;">
                            <option value="">Todos los estados</option>
                            <option value="draft">Borrador</option>
                            <option value="sent">Enviada</option>
                            <option value="closed">Cotizada</option>
                            <option value="po">Convertida a PO</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-signal mr-1"></i> Prioridad
                        </label>
                        <select name="priority" class="form-control" style="border-radius: 8px;">
                            <option value="">Todas las prioridades</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1 font-weight-bold mr-2" style="border-radius: 8px;">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-outline-secondary font-weight-bold" id="clearFilters" title="Limpiar Filtros" style="border-radius: 8px;">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla Principal -->
            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="rfqTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%"></th>
                                <th style="width: 15%">Código</th>
                                <th style="width: 25%">Título</th>
                                <th style="width: 15%">Estado</th>
                                <th style="width: 12%">Prioridad</th>
                                <th style="width: 13%">Fecha Límite</th>
                                <th style="width: 15%">Ofertas</th>
                                <th style="width: 10%" class="text-right">Acciones</th>
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
            var table = $('#rfqTable').DataTable({
                responsive: true,
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
                    url: "{{ route('admin.rfq.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = $('select[name="status"]').val();
                        d.priority = $('select[name="priority"]').val();
                    }
                },

                columns: [
                    { data: 'expand_btn', name: 'expand_btn', orderable: false, searchable: false, class: 'text-center' },
                    { data: 'code', name: 'code', orderable: true },
                    { data: 'title', name: 'title', orderable: true },
                    { data: 'status', name: 'status', orderable: true },
                    { data: 'priority', name: 'priority', orderable: true },
                    { data: 'date_required', name: 'date_required', orderable: true },
                    { data: 'supplier_offers_count', name: 'supplier_offers_count', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, class: 'text-right' }
                ],

                language: {
                    decimal: "",
                    emptyTable: "No hay solicitudes de cotización registradas",
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
                    { orderable: false, targets: [0, 7] },
                    {
                        targets: [3, 4, 6],
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

            // Filtros reactivos al cambiar los selectores
            $('select[name="status"], select[name="priority"]').on('change', function() {
                table.draw();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                table.draw();
            });

            // Renderizar la fila secundaria (Child Row)
            function format(d) {
                if (!d.items_list || d.items_list.length === 0) {
                    return '<div class="card card-inner bg-light shadow-none border-0">' +
                           '  <div class="card-body p-3 text-muted">' +
                           '    <i class="fas fa-exclamation-circle mr-1"></i> Sin ítems registrados en esta solicitud.' +
                           '  </div>' +
                           '</div>';
                }

                var html = '<div class="card card-inner bg-light shadow-none border-0">' +
                           '  <div class="card-body p-3">' +
                           '    <h6 class="text-xs font-weight-bold text-uppercase text-secondary mb-2">' +
                           '      <i class="fas fa-cubes mr-1 text-primary"></i> Productos Solicitados' +
                           '    </h6>' +
                           '    <div class="table-responsive bg-white rounded border" style="max-width: 600px;">' +
                           '      <table class="table table-sm table-hover mb-0 text-xs">' +
                           '        <thead class="thead-light">' +
                           '          <tr>' +
                           '            <th style="width: 25%; font-weight: 600; padding: 6px 12px;">Código</th>' +
                           '            <th style="width: 55%; font-weight: 600; padding: 6px 12px;">Descripción</th>' +
                           '            <th style="width: 20%; font-weight: 600; padding: 6px 12px;" class="text-right">Cantidad</th>' +
                           '          </tr>' +
                           '        </thead>' +
                           '        <tbody>';

                d.items_list.forEach(function(item) {
                    html += '          <tr>' +
                            '            <td class="font-weight-bold text-secondary" style="padding: 6px 12px;">' + item.code + '</td>' +
                            '            <td style="padding: 6px 12px;">' + item.name + '</td>' +
                            '            <td class="text-right font-weight-bold text-dark" style="padding: 6px 12px;">' + 
                                           item.quantity + ' <span class="text-muted font-weight-normal">' + item.unit + '</span>' + 
                            '            </td>' +
                            '          </tr>';
                });

                html += '        </tbody>' +
                        '      </table>' +
                        '    </div>' +
                        '  </div>' +
                        '</div>';

                return html;
            }

            // Click listener para abrir/cerrar fila secundaria
            $('#rfqTable tbody').on('click', 'button.toggle-child-row', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // Cierra la fila
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
                    $(this).removeClass('btn-outline-danger').addClass('btn-outline-primary');
                } else {
                    // Abre la fila
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                    $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
                    $(this).removeClass('btn-outline-primary').addClass('btn-outline-danger');
                }
            });
        });
    </script>
    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@endsection

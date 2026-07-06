@extends('adminlte::page')

@section('title', 'Inventario | Solicitudes de Salida')

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
        #requestsTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #requestsTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #requestsTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #requestsTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #requestsTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #requestsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #requestsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Premium Badges */
        .badge {
            padding: 0.5em 0.85em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: inline-block;
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
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-clipboard-list text-primary mr-2"></i> Solicitudes de Salida
            </h1>
            <p class="text-muted mb-0">Gestión y despacho de insumos para departamentos y proyectos.</p>
        </div>
        @can('solicitudes_crear')
            <a href="{{ route('admin.requests.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Solicitud
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
                <form id="filterForm" method="GET" action="{{ route('admin.requests.index') }}" class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-toggle-on mr-1"></i> Estado
                        </label>
                        <select name="status" class="form-control" style="border-radius: 8px;">
                            <option value="">Todos</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Aprobada</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rechazada</option>
                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Borrador</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-exclamation-circle mr-1"></i> Prioridad
                        </label>
                        <select name="priority" class="form-control" style="border-radius: 8px;">
                            <option value="">Todas</option>
                            <option value="alta" {{ request('priority') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="media" {{ request('priority') == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="baja" {{ request('priority') == 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-building mr-1"></i> Departamento
                        </label>
                        <select name="destination_area" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('destination_area') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-secondary font-weight-bold w-100" id="clearFilters" style="border-radius: 8px;">
                            <i class="fas fa-undo mr-1"></i> Resetear
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABLA PRINCIPAL --}}
            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="requestsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 15%">Solicitante</th>
                                <th style="width: 15%">Departamento</th>
                                <th style="width: 20%">Justificación</th>
                                <th style="width: 10%">Prioridad</th>
                                <th style="width: 10%">Estado</th>
                                <th style="width: 12%">Fecha</th>
                                <th style="width: 13%">Aprobador</th>
                                <th style="width: 10%">Fecha Proc.</th>
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

    {{-- MODALES DE ACCION RAPIDA --}}
    @can('solicitudes_aprobar')
        <!-- MODAL RECHAZAR -->
        <div class="modal fade" id="rejectRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 12px;">
                    <div class="modal-header bg-danger text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Rechazar Solicitud</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="modal_rejection_reason" class="font-weight-bold text-secondary">Motivo del Rechazo <span class="text-danger">*</span></label>
                            <textarea id="modal_rejection_reason" class="form-control" rows="4" style="border-radius: 8px;" placeholder="Escriba el motivo detallado del rechazo de la solicitud..."></textarea>
                            <div class="invalid-feedback" id="modal_rejection_reason_error">El motivo de rechazo es obligatorio.</div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                        <button type="button" id="btn-submit-reject" class="btn btn-danger font-weight-bold" style="border-radius: 8px;">Rechazar Solicitud</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Configurar CSRF para AJAX
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

            var table = $('#requestsTable').DataTable({
                "responsive": true, 
                "processing": true,
                "serverSide": true,
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 6, "desc" ]],
                "pageLength": 15,
                "lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],

                "ajax": {
                    "url": "{{ route('admin.requests.index') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.status = $('select[name="status"]').val();
                        d.priority = $('select[name="priority"]').val();
                        d.destination_area = $('select[name="destination_area"]').val();
                    }
                },

                "columns": [
                    { "data": "id", "name": "id" },
                    { "data": "requester", "name": "requester_id" },
                    { "data": "destination_area", "name": "destination_area" },
                    { "data": "justification", "name": "justification" },
                    { "data": "priority", "name": "priority", "orderable": false },
                    { "data": "status", "name": "status" },
                    { "data": "date", "name": "requested_at" },
                    { "data": "approver", "name": "approver_id" },
                    { "data": "processed", "name": "processed_at" },
                    { "data": "actions", "name": "actions", "orderable": false, "searchable": false }
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
                    { "responsivePriority": 1, "targets": 0 }, // ID
                    { "responsivePriority": 2, "targets": 5 }, // Estado
                    { "responsivePriority": 3, "targets": 9 }, // Acciones
                    { "responsivePriority": 100, "targets": [1, 2, 3, 4, 6, 7, 8] }
                ]
            });

            // Aplicar filtros reactivos
            $('#filterForm select, #filterForm input').on('change', function() {
                table.draw();
            });

            // Resetear filtros
            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val('').trigger('change');
                table.draw();
            });

            // Variables para urls activas de modals de rechazo
            var activeRejectUrl = '';

            // Delegación de eventos para botón Rechazar
            $('#requestsTable').on('click', '.btn-reject-request', function(e) {
                e.preventDefault();
                activeRejectUrl = $(this).data('url');
                
                $('#modal_rejection_reason').val('');
                $('#modal_rejection_reason').removeClass('is-invalid');
                $('#rejectRequestModal').modal('show');
            });

            // Confirmar Rechazo
            $('#btn-submit-reject').on('click', function() {
                var reason = $('#modal_rejection_reason').val().trim();
                if (!reason) {
                    $('#modal_rejection_reason').addClass('is-invalid');
                    return;
                }
                
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');
                
                $.ajax({
                    url: activeRejectUrl,
                    type: 'POST',
                    data: {
                        rejection_reason: reason
                    },
                    success: function(response) {
                        $('#rejectRequestModal').modal('hide');
                        btn.prop('disabled', false).text('Rechazar Solicitud');
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Rechazada',
                            text: response.message,
                            timer: 2500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        $('#rejectRequestModal').modal('hide');
                        btn.prop('disabled', false).text('Rechazar Solicitud');
                        
                        var msg = 'No se pudo rechazar la solicitud.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
            
            setTimeout(function() { 
                if (typeof table.columns.adjust().responsive !== 'undefined') {
                    table.columns.adjust().responsive.recalc(); 
                }
            }, 500);
        });
    </script>
@endsection

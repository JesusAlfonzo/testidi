@extends('adminlte::page')

@section('title', 'Maestros | Proveedores')

@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
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

        #suppliersTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #suppliersTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #suppliersTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #suppliersTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #suppliersTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #suppliersTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #suppliersTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .badge {
            padding: 0.5em 0.85em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: inline-block;
        }

        .badge-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }

        .badge-secondary {
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
            border: 1px solid #e5e7eb !important;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-truck text-primary mr-2"></i> Gestión de Proveedores
            </h1>
            <p class="text-muted mb-0">Administre el directorio de proveedores externos de la institución.</p>
        </div>
        @can('proveedores_crear')
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Crear Proveedor
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            {{-- 1. BARRA DE FILTROS MINIMALISTA --}}
            <div class="card filter-section p-3 mb-3">
                <form id="filterForm" class="row align-items-end">
                    <div class="col-md-10 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-filter mr-1"></i> Estado
                        </label>
                        <select name="status" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos</option>
                            <option value="active">Activos</option>
                            <option value="inactive">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary font-weight-bold w-100" id="clearFilters" style="border-radius: 8px;">
                            <i class="fas fa-undo mr-1"></i> Resetear
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. TABLA PRINCIPAL --}}
            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="suppliersTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 25%">Nombre</th>
                                <th style="width: 15%">Estado</th>
                                <th style="width: 20%">Contacto / Teléfono</th>
                                <th style="width: 15%">ID Fiscal</th>
                                <th style="width: 15%">Email</th>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({ theme: 'bootstrap4' });

            const table = $('#suppliersTable').DataTable({
                responsive: true, 
                processing: true,
                serverSide: true,
                paging: true, 
                lengthChange: true, 
                searching: true, 
                ordering: true, 
                info: true, 
                autoWidth: false,
                order: [[1, 'asc']],
                pageLength: 15,
                lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],

                ajax: {
                    url: "{{ route('admin.suppliers.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = $('select[name="status"]').val();
                    }
                },

                columns: [
                    { data: 'id', name: 'id', orderable: true },
                    { data: 'name', name: 'name', orderable: true },
                    { data: 'is_active', name: 'is_active', orderable: true },
                    { data: 'contact', name: 'contact_person', orderable: false },
                    { data: 'tax_id', name: 'tax_id', orderable: true },
                    { data: 'email', name: 'email', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],

                language: {
                    decimal: '',
                    emptyTable: 'No hay información disponible',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                    infoFiltered: '(Filtrado de _MAX_ total registros)',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'Sin resultados encontrados',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                },

                columnDefs: [
                    { "orderable": false, "targets": [3, 6] },
                    {
                        "targets": 1, // Nombre
                        "render": function(data, type, row) {
                            return '<span class="font-weight-bold text-dark">' + data + '</span>';
                        }
                    },
                    { 
                        "targets": 2, // Estado
                        "render": function(data, type, row) {
                            if (data == 1 || data === true) {
                                return '<span class="badge badge-success">Activo</span>';
                            } else {
                                return '<span class="badge badge-secondary">Inactivo</span>';
                            }
                        }
                    },
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 6 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    { "responsivePriority": 100, "targets": [2, 3, 4, 5] }
                ]
            });

            // Ajuste reactivo al cambiar filtros
            $('#filterForm select').on('change', function() {
                table.draw();
            });

            // Botón Resetear Filtros
            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val('').trigger('change');
                table.draw();
            });

            // 🔑 ELIMINACIÓN DELEGADA POR AJAX (RELOAD SERVER-SIDE)
            $('#suppliersTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'este proveedor';

                Swal.fire({
                    title: '¿Está seguro de eliminar?',
                    text: `Se eliminará "${itemName}" del sistema de forma definitiva.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value === true || result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.message || 'Proveedor eliminado con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                // Recarga Server-Side sin perder paginación
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar el proveedor. Verifique que no posea productos u órdenes asociadas.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });
            
            setTimeout(function() { table.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@stop
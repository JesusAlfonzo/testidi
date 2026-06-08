@extends('adminlte::page')

@section('title', 'Maestros | Ubicaciones')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Sweetalert2', true)

@section('css')
    <style>
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: none;
            margin-bottom: 2rem;
        }

        #locationsTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #locationsTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #locationsTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #locationsTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #locationsTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #locationsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #locationsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-warehouse text-primary mr-2"></i> Ubicaciones de Inventario
            </h1>
            <p class="text-muted mb-0">Administre las ubicaciones físicas, laboratorios y almacenes del inventario.</p>
        </div>
        @can('ubicaciones_crear')
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Ubicación
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-custom p-3 bg-white">
                <div class="table-responsive">
                    <table id="locationsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 30%">Nombre</th>
                                <th style="width: 30%">Detalles</th>
                                <th style="width: 15%">Creado por</th>
                                <th style="width: 15%" class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $location)
                                <tr>
                                    <td>{{ $location->id }}</td>
                                    <td class="font-weight-bold">{{ $location->name }}</td>
                                    <td class="text-muted">{{ Str::limit($location->details, 50) ?? 'N/A' }}</td>
                                    <td>{{ $location->user->name ?? 'N/A' }}</td>
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('ubicaciones_editar')
                                                <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('ubicaciones_eliminar')
                                                <button type="button" 
                                                        class="btn btn-default text-danger btn-delete-master" 
                                                        data-id="{{ $location->id }}" 
                                                        data-name="{{ $location->name }}" 
                                                        data-url="{{ route('admin.locations.destroy', $location) }}" 
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function removeAccents(str) {
            if (!str) return '';
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        // Sobrescribir función de búsqueda de DataTables
        var originalSearch = $.fn.dataTable.ext.type.search.string;
        $.fn.dataTable.ext.type.search.string = function(data) {
            if (typeof data === 'string') {
                return removeAccents(data);
            }
            return data;
        };

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#locationsTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false, 
                "order": [[ 1, "asc" ]],
                "pageLength": 15,
                "lengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay información disponible",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
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
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 4 }, // Acciones
                    { "responsivePriority": 100, "targets": [0, 2, 3] } 
                ]
            });

            // ELIMINACIÓN DELEGADA POR AJAX
            $('#locationsTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'esta ubicación';
                const row = btn.closest('tr');

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
                                    text: response.message || 'Ubicación eliminada con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.row(row).remove().draw(false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar la ubicación. Verifique que no tenga productos asociados.';
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
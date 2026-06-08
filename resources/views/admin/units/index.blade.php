@extends('adminlte::page')

@section('title', 'Maestros | Unidades de Medida')

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

        #unitsTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #unitsTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #unitsTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #unitsTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #unitsTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #unitsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #unitsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-ruler-combined text-primary mr-2"></i> Unidades de Medida
            </h1>
            <p class="text-muted mb-0">Administre las unidades de medida e indicaciones de empaque para productos.</p>
        </div>
        @can('unidades_crear')
            <a href="{{ route('admin.units.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Crear Unidad
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
                    <table id="unitsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 35%">Nombre</th>
                                <th style="width: 25%">Abreviatura</th>
                                <th style="width: 20%">Registrado Por</th>
                                <th style="width: 10%" class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td>{{ $unit->id }}</td>
                                    <td class="font-weight-bold">{{ $unit->name }}</td>
                                    <td><span class="badge badge-info">{{ $unit->abbreviation }}</span></td>
                                    <td>{{ $unit->user->name ?? 'Sistema' }}</td>
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('unidades_editar')
                                                <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('unidades_eliminar')
                                                <button type="button" 
                                                        class="btn btn-default text-danger btn-delete-master" 
                                                        data-id="{{ $unit->id }}" 
                                                        data-name="{{ $unit->name }} ({{ $unit->abbreviation }})" 
                                                        data-url="{{ route('admin.units.destroy', $unit) }}" 
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
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#unitsTable').DataTable({
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
            $('#unitsTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'esta unidad';
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
                                    text: response.message || 'Unidad de medida eliminada con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.row(row).remove().draw(false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar la unidad. Verifique que no tenga productos asociados.';
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
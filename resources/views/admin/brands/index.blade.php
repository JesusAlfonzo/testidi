@extends('adminlte::page')

@section('title', 'Maestros | Marcas')

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

        #brandsTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #brandsTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #brandsTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #brandsTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #brandsTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #brandsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #brandsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-tag text-primary mr-2"></i> Marcas de Insumos
            </h1>
            <p class="text-muted mb-0">Administre las marcas de los productos registrados en el sistema.</p>
        </div>
        @can('marcas_crear')
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Crear Marca
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
                    <table id="brandsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 30%">Nombre</th>
                                <th style="width: 25%">Sitio Web</th>
                                <th style="width: 15%">Registrado Por</th>
                                <th style="width: 10%">Fecha Creación</th>
                                <th style="width: 10%" class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td class="font-weight-bold">{{ $brand->name }}</td>
                                    <td>
                                        @if($brand->website)
                                            <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="text-primary font-weight-bold">
                                                <i class="fas fa-external-link-alt mr-1"></i> Visitar
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $brand->user->name ?? 'Sistema' }}</td>
                                    <td data-order="{{ $brand->created_at->timestamp }}">
                                        {{ $brand->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('marcas_editar')
                                                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('marcas_eliminar')
                                                <button type="button" 
                                                        class="btn btn-default text-danger btn-delete-master" 
                                                        data-id="{{ $brand->id }}" 
                                                        data-name="{{ $brand->name }}" 
                                                        data-url="{{ route('admin.brands.destroy', $brand) }}" 
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

            const brandsTable = $('#brandsTable').DataTable({
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
                    { "orderable": false, "targets": [5] },
                    { "type": "date", "targets": 4 },
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 5 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    { "responsivePriority": 100, "targets": [2, 3, 4] } 
                ]
            });
            
            // ELIMINACIÓN DELEGADA POR AJAX
            $('#brandsTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'esta marca';
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
                                    text: response.message || 'Marca eliminada con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                brandsTable.row(row).remove().draw(false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar la marca. Verifique que no tenga productos asociados.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });

            setTimeout(function() { brandsTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@stop
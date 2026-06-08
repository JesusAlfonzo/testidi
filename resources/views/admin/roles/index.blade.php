@extends('adminlte::page')

@section('title', 'Maestros | Roles')

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

        #rolesTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #rolesTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #rolesTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #rolesTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #rolesTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #rolesTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #rolesTable tbody tr td:last-child {
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

        .badge-info-custom {
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
                <i class="fas fa-user-shield text-primary mr-2"></i> Gestión de Roles
            </h1>
            <p class="text-muted mb-0">Administre los roles de acceso del personal y los permisos del sistema.</p>
        </div>
        @can('roles_crear')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Crear Rol
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
                    <table id="rolesTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 15%">ID</th>
                                <th style="width: 40%">Nombre del Rol</th>
                                <th style="width: 30%">Permisos Asignados</th>
                                <th style="width: 15%" class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $role->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info-custom">
                                            <i class="fas fa-key mr-1"></i> {{ $role->permissions_count }} {{ $role->permissions_count == 1 ? 'Permiso' : 'Permisos' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('roles_ver')
                                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-default text-info" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('roles_editar')
                                                @if($role->name === 'Superadmin' || $role->id === 1)
                                                    <button type="button" class="btn btn-default text-muted" title="Rol Protegido (No Editable)" disabled>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('roles_eliminar')
                                                @if($role->name === 'Superadmin' || $role->id === 1)
                                                    <button type="button" class="btn btn-default text-muted" title="Rol Protegido (No Eliminable)" disabled>
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="btn btn-default text-danger btn-delete-master" 
                                                            data-id="{{ $role->id }}" 
                                                            data-name="{{ $role->name }}" 
                                                            data-url="{{ route('admin.roles.destroy', $role) }}" 
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
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

            const rolesTable = $('#rolesTable').DataTable({
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
                    { "orderable": false, "targets": [3] },
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 3 }  // Acciones
                ]
            });

            // ELIMINACIÓN DELEGADA POR AJAX
            $('#rolesTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'este rol';
                const row = btn.closest('tr');

                Swal.fire({
                    title: '¿Está seguro de eliminar?',
                    text: `Se eliminará el rol "${itemName}" de forma definitiva.`,
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
                                    text: response.message || 'Rol eliminado con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                rolesTable.row(row).remove().draw(false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar el rol.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });

            setTimeout(function() { rolesTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection

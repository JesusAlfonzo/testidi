@extends('adminlte::page')

@section('title', 'Maestros | Usuarios')

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

        #usersTable {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: 100% !important;
        }

        #usersTable thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #4b5563;
            background-color: #f9fafb;
            border: none;
            padding: 12px 16px;
        }

        #usersTable tbody tr {
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        #usersTable tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #usersTable tbody td {
            padding: 14px 16px;
            border: none !important;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #1f2937;
        }

        #usersTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #usersTable tbody tr td:last-child {
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

        .badge-danger {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
        }

        .badge-dark {
            background-color: #f3f4f6 !important;
            color: #374151 !important;
            border: 1px solid #e5e7eb !important;
        }

        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 0.8rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-users-cog text-primary mr-2"></i> Gestión de Usuarios
            </h1>
            <p class="text-muted mb-0">Administre las cuentas de acceso, roles y estados de los usuarios del sistema.</p>
        </div>
        @can('usuarios_crear')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 8px;">
                <i class="fas fa-plus-circle mr-1"></i> Crear Usuario
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
                <form id="filterForm" method="GET" action="{{ route('admin.users.index') }}" class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-user-tag mr-1"></i> Rol
                        </label>
                        <select name="role_id" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos los Roles</option>
                            @foreach($roles as $id => $name)
                                <option value="{{ $id }}" {{ request('role_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-filter mr-1"></i> Estado
                        </label>
                        <select name="is_active" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos los Estados</option>
                            <option value="active" {{ request('is_active') === 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="inactive" {{ request('is_active') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
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
                    <table id="usersTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 30%">Nombre</th>
                                <th style="width: 25%">Email</th>
                                <th style="width: 15%">Roles</th>
                                <th style="width: 10%">Estado</th>
                                <th style="width: 10%" class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle text-white d-flex align-items-center justify-content-center mr-2 font-weight-bold">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <span class="font-weight-bold text-dark">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-dark">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @can('usuarios_ver')
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-default text-info" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('usuarios_editar')
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('usuarios_eliminar')
                                                <button type="button" 
                                                        class="btn btn-default text-danger btn-delete-master" 
                                                        data-id="{{ $user->id }}" 
                                                        data-name="{{ $user->name }}" 
                                                        data-url="{{ route('admin.users.destroy', $user) }}" 
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

            $('.select2').select2({ theme: 'bootstrap4' });

            const usersTable = $('#usersTable').DataTable({
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
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 5 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    { "responsivePriority": 100, "targets": [2, 3, 4] } 
                ]
            });
            
            // Ajuste reactivo al cambiar filtros
            $('#filterForm select').on('change', function() {
                $('#filterForm').submit();
            });

            // Botón Resetear Filtros
            $('#clearFilters').on('click', function() {
                window.location.href = "{{ route('admin.users.index') }}";
            });

            // ELIMINACIÓN DELEGADA POR AJAX
            $('#usersTable').on('click', '.btn-delete-master', function(e) {
                e.preventDefault();
                const btn = $(this);
                const deleteUrl = btn.data('url');
                const itemName = btn.data('name') || 'este usuario';
                const row = btn.closest('tr');

                Swal.fire({
                    title: '¿Está seguro de eliminar?',
                    text: `Se eliminará a "${itemName}" del sistema de forma definitiva.`,
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
                                    text: response.message || 'Usuario eliminado con éxito.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                usersTable.row(row).remove().draw(false);
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                let msg = 'No se pudo eliminar el usuario.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });

            setTimeout(function() { usersTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
@endsection
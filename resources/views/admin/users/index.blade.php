@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
@stop

{{-- 🔑 AJUSTE CRÍTICO EN EL CSS (Para eliminar el padding extra del Responsive en PC) --}}
@section('css')
    <style>
        /* Ajusta la posición del botón de expansión (+) */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            left: 4px; 
        }
        
        /* Elimina el padding izquierdo de la primera columna para evitar el scroll horizontal en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child, 
        .table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child {
            padding-left: 10px !important; 
        }
        
        /* Ajuste visual para que los badges de roles no se desborden */
        td .badge {
            margin-right: 3px;
            margin-bottom: 3px;
            display: inline-block;
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Listado de Usuarios del Sistema</h3>
                    <div class="card-tools">
                        {{-- Botón para crear nuevo usuario, protegido por permiso --}}
                        @can('usuarios_crear')
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> Crear Nuevo Usuario
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-striped table-bordered display nowrap">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 5%">ID</th>
                                    <th style="width: 25%">Nombre</th>
                                    <th style="width: 25%">Email</th>
                                    <th style="width: 30%">Roles</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            {{-- Mostrar los roles del usuario --}}
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Usuario">
                                                {{-- Botón de Edición --}}
                                                @can('usuarios_editar')
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                {{-- Botón de Eliminación --}}
                                                @can('usuarios_eliminar')
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" onclick="return confirm('¿Está seguro de eliminar a este usuario?')" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay usuarios registrados (aparte del Super Admin si fue excluido).</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>
                {{-- Eliminamos el card-footer con paginación de Laravel --}}
            </div>
        </div>
    </div>
@stop

{{-- ---------------------------------------------------- --}}
{{-- Sección de Scripts para Inicializar DataTables --}}
{{-- ---------------------------------------------------- --}}
@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const usersTable = $('#usersTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 1, "asc" ]], // Ordenar por la columna Nombre (índice 1) ascendente
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [4] }, // Acciones (índice 4)

                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 4 }, // Acciones
                    { "responsivePriority": 3, "targets": 2 }, // Email
                    { "responsivePriority": 4, "targets": 3 }, // Roles
                    { "responsivePriority": 5, "targets": 0 }  // ID (Se oculta al último)
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                usersTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection
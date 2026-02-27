@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-user-tag"></i> Gestión de Roles</h1>
        @can('roles_crear')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Rol
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Listado de Roles</h3>
                </div>
                <div class="card-body">
                    <table id="rolesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Permisos</th>
                                <th>Usuarios</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                    </td>
                                    <td>
                                        @forelse($role->permissions->take(5) as $permission)
                                            <span class="badge badge-info">{{ $permission->name }}</span>
                                        @empty
                                            <span class="text-muted">Sin permisos</span>
                                        @endforelse
                                        @if($role->permissions->count() > 5)
                                            <span class="badge badge-secondary">+{{ $role->permissions->count() - 5 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $role->users_count ?? $role->users->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @can('roles_ver')
                                                <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-info btn-sm" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('roles_editar')
                                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('roles_eliminar')
                                                @if($role->name !== 'Superadmin')
                                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar el rol {{ $role->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
        $(function () {
            $("#rolesTable").DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop

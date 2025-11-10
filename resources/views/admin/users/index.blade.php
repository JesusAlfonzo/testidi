@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
    <h1 class="m-0 text-dark">Gestión de Usuarios</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Usuarios del Sistema</h3>
                    <div class="card-tools">
                        {{-- Botón para crear nuevo usuario, protegido por permiso --}}
                        @can('usuarios_crear')
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Crear Nuevo Usuario
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        {{-- Mostrar los roles del usuario --}}
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            {{-- Botón de Edición --}}
                                            @can('usuarios_editar')
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- Botón de Eliminación --}}
                                            @can('usuarios_eliminar')
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('¿Está seguro de eliminar a este usuario?')" title="Eliminar">
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
                </div>

                <div class="card-footer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@stop

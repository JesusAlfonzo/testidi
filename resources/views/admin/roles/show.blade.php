@extends('adminlte::page')

@section('title', 'Ver Rol')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-user-tag"></i> Detalles del Rol</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Información del Rol</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8"><strong>{{ $role->name }}</strong></dd>

                        <dt class="col-sm-4">Usuarios:</dt>
                        <dd class="col-sm-8">{{ $users->count() }}</dd>

                        <dt class="col-sm-4">Creado:</dt>
                        <dd class="col-sm-8">{{ $role->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    @can('roles_editar')
                        @if($role->name !== 'Superadmin')
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Permisos Asignados</h3>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div class="row">
                            @foreach($role->permissions->groupBy(function ($permission) {
                                $parts = explode('_', $permission->name);
                                return $parts[0] ?? 'other';
                            }) as $group => $groupPermissions)
                                <div class="col-md-4 mb-3">
                                    <h5 class="bg-info px-2 py-1 rounded">{{ ucfirst($group) }}</h5>
                                    @foreach($groupPermissions as $permission)
                                        <span class="badge badge-success mb-1">{{ $permission->name }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No hay permisos asignados a este rol.</p>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Usuarios con este Rol ({{ $users->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Fecha de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No hay usuarios con este rol asignado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

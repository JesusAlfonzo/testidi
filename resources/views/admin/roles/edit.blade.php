@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-user-tag"></i> Editar Rol</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Información del Rol</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $role->name) }}" 
                                {{ $role->name === 'Superadmin' ? 'readonly' : '' }} required>
                            @if($role->name === 'Superadmin')
                                <small class="text-muted">El rol Superadmin no puede ser renombrado.</small>
                            @endif
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Permisos</label>
                            <div class="row">
                                @foreach($permissions as $group => $groupPermissions)
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card">
                                            <div class="card-header bg-info">
                                                <h5 class="card-title text-white">{{ ucfirst($group) }}</h5>
                                            </div>
                                            <div class="card-body">
                                                @foreach($groupPermissions as $permission)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="permissions[]" 
                                                            value="{{ $permission->name }}" 
                                                            id="perm_{{ $permission->id }}"
                                                            class="form-check-input"
                                                            {{ in_array($permission->name, $selectedPermissions) ? 'checked' : '' }}
                                                            {{ $role->name === 'Superadmin' ? 'disabled' : '' }}>
                                                        <label for="perm_{{ $permission->id }}" class="form-check-label">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($role->name === 'Superadmin')
                                <small class="text-muted">El Superadmin tiene todos los permisos automáticamente.</small>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" {{ $role->name === 'Superadmin' ? 'disabled' : '' }}>
                            <i class="fas fa-save"></i> Actualizar Rol
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

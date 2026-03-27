@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Usuario: <strong>{{ $user->name }}</strong></h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-user-edit"></i> Actualizar Datos del Usuario
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">ID: {{ $user->id }}</span>
                    </div>
                </div>

                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="card" style="border-left: 4px solid #3b82f6;">
                                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                                        <h3 class="card-title text-white">
                                            <i class="fas fa-info-circle"></i> Información Personal
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                            </div>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="role_id">Rol Asignado <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-user-shield"></i></span>
                                                </div>
                                                <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                                    <option value="">Seleccione un Rol</option>
                                                    @foreach($roles as $id => $name)
                                                        <option value="{{ $id }}" {{ old('role_id', $currentRole) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card" style="border-left: 4px solid #ef4444;">
                                    <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">
                                        <h3 class="card-title text-white">
                                            <i class="fas fa-key"></i> Actualizar Contraseña
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="password">Nueva Contraseña</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-danger text-white"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Dejar en blanco para no cambiar">
                                            </div>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-warning">Solo llenar si desea cambiar la contraseña.</small>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-danger text-white"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                            </div>
                                            <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Usuario
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nuevo Usuario</h1>
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

            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-user-plus"></i> Datos para Nuevo Usuario
                    </h3>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
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
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                            </div>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Será usado para el login y notificaciones.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card" style="border-left: 4px solid #f59e0b;">
                                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                        <h3 class="card-title text-white">
                                            <i class="fas fa-lock"></i> Credenciales y Permisos
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="role_id">Rol Asignado <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-user-shield"></i></span>
                                                </div>
                                                <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                                    <option value="">Seleccione el Rol</option>
                                                    @foreach($roles as $id => $name)
                                                        <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="password">Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-key"></i></span>
                                                </div>
                                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                            </div>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-warning text-dark"><i class="fas fa-lock"></i></span>
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
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Crear Usuario
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

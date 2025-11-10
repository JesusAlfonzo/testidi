@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nuevo Usuario</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Mensajes de feedback --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus"></i> Datos para Nuevo Usuario</h3>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{-- Columna 1: Información Básica --}}
                            <div class="col-md-6">
                                <h4><i class="fas fa-info-circle text-info"></i> Información Personal</h4>
                                <hr>

                                {{-- Nombre --}}
                                <div class="form-group">
                                    <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email --}}
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="form-text text-muted">Será usado para el login y notificaciones.</small>
                                </div>
                            </div>

                            {{-- Columna 2: Seguridad y Rol --}}
                            <div class="col-md-6">
                                <h4><i class="fas fa-lock text-warning"></i> Credenciales y Permisos</h4>
                                <hr>

                                {{-- Rol --}}
                                <div class="form-group">
                                    <label for="role_id">Rol Asignado <span class="text-danger">*</span></label>
                                    <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                        <option value="">Seleccione el Rol</option>
                                        @foreach($roles as $id => $name)
                                            <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Contraseña --}}
                                <div class="form-group">
                                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Confirmar Contraseña --}}
                                <div class="form-group">
                                    <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                    <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                                </div>
                            </div>
                        </div> {{-- Fin row --}}
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

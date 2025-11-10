@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Usuario: <strong>{{ $user->name }}</strong></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit"></i> Actualizar Datos del Usuario</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary">ID: {{ $user->id }}</span>
                    </div>
                </div>

                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">

                            {{-- Columna 1: Información Básica --}}
                            <div class="col-md-6">
                                <h4><i class="fas fa-info-circle text-info"></i> Información Personal</h4>
                                <hr>

                                {{-- Nombre --}}
                                <div class="form-group">
                                    <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Email --}}
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Rol --}}
                                <div class="form-group">
                                    <label for="role_id">Rol Asignado <span class="text-danger">*</span></label>
                                    <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un Rol</option>
                                        @foreach($roles as $id => $name)
                                            <option value="{{ $id }}" {{ old('role_id', $currentRole) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Columna 2: Actualización de Contraseña --}}
                            <div class="col-md-6">
                                <h4><i class="fas fa-key text-danger"></i> Actualizar Contraseña (Opcional)</h4>
                                <hr>

                                {{-- Contraseña --}}
                                <div class="form-group">
                                    <label for="password">Nueva Contraseña</label>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Dejar en blanco para no cambiar">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="form-text text-warning">Solo llenar si desea cambiar la contraseña.</small>
                                </div>

                                {{-- Confirmar Contraseña --}}
                                <div class="form-group">
                                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                    <small class="form-text text-muted">Mínimo 8 caracteres.</small>
                                </div>
                            </div>
                        </div> {{-- Fin row --}}
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
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

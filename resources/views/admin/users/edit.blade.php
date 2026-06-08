@extends('adminlte::page')

@section('title', 'Maestros | Editar Usuario')

@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-edit text-primary mr-2"></i> Editar Usuario
            </h1>
            <p class="text-muted mb-0">Modifique la información del usuario registrado en el sistema.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Columna Izquierda (70%): Información Personal y Acceso --}}
            <div class="col-lg-8 mb-3">
                <div class="card p-4 bg-white shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <h6 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-user-edit text-info mr-2"></i> Información Personal y de Acceso
                    </h6>
                    
                    <!-- Nombre Completo -->
                    <div class="form-group mb-3">
                        <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Nombre Completo <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-user text-muted"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" placeholder="Ej: Juan Pérez" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="form-group mb-3">
                        <label for="email" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Correo Electrónico <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-envelope text-muted"></i></span>
                            </div>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" placeholder="juan.perez@ejemplo.com" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('email')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group mb-3">
                        <label for="password" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Contraseña
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-key text-muted"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Dejar en blanco para conservar la contraseña actual" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                        <small class="text-muted d-block mt-1">Dejar en blanco para conservar la contraseña actual. Si se ingresa una, debe tener al menos 8 caracteres.</small>
                        @error('password')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group mb-0">
                        <label for="password_confirmation" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Confirmar Contraseña
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-lock text-muted"></i></span>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                   placeholder="Repita la nueva contraseña" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna Derecha (30%): Configuración de Cuenta --}}
            <div class="col-lg-4 mb-3">
                <div class="card p-4 bg-white shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <h6 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-cogs text-info mr-2"></i> Ajustes de Cuenta
                    </h6>

                    <!-- Rol -->
                    <div class="form-group mb-3">
                        <label for="role_id" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Rol Asignado <span class="text-danger">*</span>
                        </label>
                        <select name="role_id" id="role_id" class="form-control select2" style="width: 100%;" required>
                            <option value="">Seleccione un Rol</option>
                            @foreach($roles as $id => $name)
                                <option value="{{ $id }}" {{ old('role_id', $currentRole) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Estado (is_active) -->
                    <div class="form-group mb-3">
                        <label for="is_active" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Estado de Cuenta <span class="text-danger">*</span>
                        </label>
                        <select name="is_active" id="is_active" class="form-control" style="border-radius: 8px;" required>
                            <option value="1" {{ old('is_active', $user->is_active) == '1' || old('is_active', $user->is_active) === true ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('is_active', $user->is_active) == '0' || old('is_active', $user->is_active) === false ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('is_active')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        <small class="text-muted d-block mt-2">Los usuarios inactivos no pueden iniciar sesión en el sistema.</small>
                    </div>

                    {{-- Registro de Metadatos --}}
                    <div class="row mt-4 mb-0 bg-light p-3 rounded" style="font-size: 0.8rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <div class="col-12 mb-2">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Fecha Creación:</span>
                            <span class="text-dark">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Última Actualización:</span>
                            <span class="text-dark">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barra de Botones Inferior --}}
        <div class="row mt-2 mb-4">
            <div class="col-12 text-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2 mr-2" style="border-radius: 8px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                    <i class="fas fa-save mr-1"></i> Actualizar Usuario
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@stop

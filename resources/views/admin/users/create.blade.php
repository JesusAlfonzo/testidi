@extends('adminlte::page')

@section('title', 'Maestros | Crear Usuario')

@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-plus text-primary mr-2"></i> Crear Usuario
            </h1>
            <p class="text-muted mb-0">Registre un nuevo usuario con credenciales de acceso y rol definidos.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
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
                                   value="{{ old('name') }}" placeholder="Ej: Juan Pérez" 
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
                                   value="{{ old('email') }}" placeholder="juan.perez@ejemplo.com" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('email')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group mb-3">
                        <label for="password" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Contraseña <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-key text-muted"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Mínimo 8 caracteres" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('password')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group mb-0">
                        <label for="password_confirmation" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Confirmar Contraseña <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-lock text-muted"></i></span>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                   placeholder="Repita la contraseña" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
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
                                <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <!-- Estado (is_active) -->
                    <div class="form-group mb-0">
                        <label for="is_active" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Estado de Cuenta <span class="text-danger">*</span>
                        </label>
                        <select name="is_active" id="is_active" class="form-control" style="border-radius: 8px;" required>
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('is_active')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                        <small class="text-muted d-block mt-2">Los usuarios inactivos no pueden iniciar sesión en el sistema.</small>
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
                    <i class="fas fa-user-plus mr-1"></i> Crear Usuario
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

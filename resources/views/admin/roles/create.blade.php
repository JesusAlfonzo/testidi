@extends('adminlte::page')

@section('title', 'Maestros | Crear Rol')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-tag text-primary mr-2"></i> Crear Rol
            </h1>
            <p class="text-muted mb-0">Registre un nuevo rol en el sistema y defina su conjunto de permisos.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="row">
            {{-- Columna Izquierda (70%): Nombre del Rol y Cuadrícula de Permisos --}}
            <div class="col-lg-8 mb-3">
                <div class="card p-4 bg-white shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <h6 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-id-card text-info mr-2"></i> Datos Básicos del Rol
                    </h6>
                    
                    <!-- Nombre del Rol -->
                    <div class="form-group mb-0">
                        <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Nombre del Rol <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-user-tag text-muted"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Ej: Operador de Almacén" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="card p-4 bg-white shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <h6 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-key text-info mr-2"></i> Matriz de Permisos del Sistema
                    </h6>

                    <div class="row">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border shadow-sm" style="border-radius: 8px; border: 1px solid #e5e7eb !important;">
                                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center" style="border-top-left-radius: 8px; border-top-right-radius: 8px; border-bottom: 1px solid #e5e7eb;">
                                        <h6 class="font-weight-bold text-secondary text-uppercase mb-0" style="font-size: 0.725rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-folder text-primary mr-1"></i> {{ ucfirst($group) }}
                                        </h6>
                                        <button type="button" class="btn btn-xs btn-outline-primary select-all-group font-weight-bold" style="font-size: 0.65rem; padding: 1px 6px; border-radius: 4px;">
                                            Marcar Todos
                                        </button>
                                    </div>
                                    <div class="card-body p-3">
                                        @foreach($groupPermissions as $permission)
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" 
                                                       class="custom-control-input perm-checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->name }}" 
                                                       id="permission_{{ $permission->id }}"
                                                       {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-dark font-weight-normal" 
                                                       style="cursor: pointer; user-select: none; font-size: 0.85rem;" 
                                                       for="permission_{{ $permission->id }}">
                                                    {{ str_replace('_', ' ', $permission->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Columna Derecha (30%): Configuración y Guardado --}}
            <div class="col-lg-4 mb-3">
                <div class="card p-4 bg-white shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <h6 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-cog text-info mr-2"></i> Acciones y Políticas
                    </h6>

                    <div class="form-group mb-3">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Guardar Rol
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-block font-weight-bold py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                    </div>

                    <div class="alert alert-warning text-xs mb-0 mt-4 p-3" style="border-radius: 8px; line-height: 1.4; background-color: #fffbeb; border: 1px solid #fef3c7; color: #b45309;">
                        <strong class="d-block mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Políticas de Acceso:</strong>
                        <p class="mb-0 text-justify">La asignación de permisos otorga facultades de acceso inmediato sobre las rutas del sistema a todos los usuarios vinculados a este rol. Otorgue únicamente los privilegios indispensables para mitigar riesgos de seguridad.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Seleccionar/Deseleccionar todos en el grupo respectivo
            $('.select-all-group').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const card = btn.closest('.card');
                const checkboxes = card.find('.perm-checkbox');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                
                checkboxes.prop('checked', !allChecked);
                btn.text(allChecked ? 'Marcar Todos' : 'Desmarcar Todos');
                btn.toggleClass('btn-outline-primary btn-outline-secondary');
            });
        });
    </script>
@stop

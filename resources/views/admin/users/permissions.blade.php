@extends('adminlte::page')

@section('title', 'Maestros | Permisos de Usuario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-shield text-primary mr-2"></i> Permisos de Usuario
            </h1>
            <p class="text-muted mb-0">Gestione los permisos individuales directos asignados a <strong>{{ $user->name }}</strong>.</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Detalle
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        {{-- Ficha de Resumen del Usuario --}}
        <div class="col-12 mb-3">
            <div class="card bg-white p-3 shadow-sm border-0" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="bg-light-blue p-3 rounded-circle mr-3" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; background: #e8f0fe; color: #1a73e8;">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="font-weight-bold text-dark mb-0">{{ $user->name }}</h4>
                                <span class="text-muted text-sm">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <span class="text-muted text-xs text-uppercase font-weight-bold d-block mb-1">Roles Asignados</span>
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary px-3 py-2 text-sm shadow-xs" style="border-radius: 6px;">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="badge badge-secondary px-3 py-2 text-sm" style="border-radius: 6px;">Sin Rol</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerta Informativa --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info border-0 shadow-sm" style="border-radius: 12px; background-color: #e8f0fe; color: #1967d2; border-left: 5px solid #1a73e8 !important;">
                <h5><i class="icon fas fa-info-circle mr-2" style="color: #1a73e8;"></i> <strong>Permisos Directos vs. Roles</strong></h5>
                <p class="mb-0 text-sm">
                    Los permisos seleccionados a continuación se asignan <strong>directamente</strong> a este usuario. Esto le otorgará acceso a esas capacidades incluso si su rol no las contempla. Si un permiso ya está incluido en su rol, marcarlo aquí como directo no causa conflictos pero es redundante.
                </p>
            </div>
        </div>

        {{-- Matriz de Permisos --}}
        <div class="col-12">
            <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card bg-white shadow-sm border-0" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                    <div class="card-header bg-light-blue p-3 d-flex flex-wrap justify-content-between align-items-center" style="border-top-left-radius: 12px; border-top-right-radius: 12px; border-bottom: 1px solid #e5e7eb; background: #fafafa;">
                        <span class="font-weight-bold text-dark"><i class="fas fa-th mr-2 text-secondary"></i> Matriz de Permisos del Sistema</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary font-weight-bold px-3 btn-select-all" style="border-radius: 6px 0 0 6px;">
                                <i class="fas fa-check-double mr-1"></i> Seleccionar Todo
                            </button>
                            <button type="button" class="btn btn-outline-secondary font-weight-bold px-3 btn-deselect-all" style="border-radius: 0 6px 6px 0;">
                                <i class="fas fa-times mr-1"></i> Desmarcar Todo
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="bg-light text-secondary text-uppercase text-xs" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    <tr>
                                        <th style="width: 25%;" class="pl-4 py-3 border-0">Módulo / Grupo</th>
                                        <th style="width: 10%;" class="text-center py-3 border-0">Ver</th>
                                        <th style="width: 10%;" class="text-center py-3 border-0">Crear</th>
                                        <th style="width: 10%;" class="text-center py-3 border-0">Editar</th>
                                        <th style="width: 10%;" class="text-center py-3 border-0">Eliminar</th>
                                        <th style="width: 25%;" class="pl-3 py-3 border-0">Permisos Especiales</th>
                                        <th style="width: 10%;" class="text-center py-3 border-0">Seleccionar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($translatedGroups as $translatedName => $groupData)
                                        @php
                                            $permissions = $groupData['permissions'];
                                            
                                            // Clasificar los permisos de este grupo
                                            $verPerm = collect($permissions)->firstWhere('action', 'ver');
                                            $crearPerm = collect($permissions)->firstWhere('action', 'crear');
                                            $editarPerm = collect($permissions)->firstWhere('action', 'editar');
                                            $eliminarPerm = collect($permissions)->firstWhere('action', 'eliminar');
                                            
                                            // Otros permisos especiales (ej: aprobar, anular, etc.)
                                            $especiales = collect($permissions)->filter(function ($item) {
                                                return !in_array($item['action'], ['ver', 'crear', 'editar', 'eliminar']);
                                            });
                                        @endphp
                                        <tr class="permission-row" data-group="{{ $groupData['key'] }}">
                                            {{-- Módulo/Grupo --}}
                                            <td class="pl-4 py-3 align-middle font-weight-bold text-dark">
                                                {{ $translatedName }}
                                                <small class="d-block text-muted text-xs font-weight-normal">Prefijo: <code>{{ $groupData['key'] }}</code></small>
                                            </td>

                                            {{-- Ver --}}
                                            <td class="text-center py-3 align-middle">
                                                @if($verPerm)
                                                    <div class="custom-control custom-checkbox d-inline-block">
                                                        <input type="checkbox" name="permissions[]" value="{{ $verPerm['id'] }}" 
                                                               id="perm_{{ $verPerm['id'] }}" class="custom-control-input permission-checkbox"
                                                               {{ in_array($verPerm['id'], $userPermissionIds) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $verPerm['id'] }}"></label>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs">-</span>
                                                @endif
                                            </td>

                                            {{-- Crear --}}
                                            <td class="text-center py-3 align-middle">
                                                @if($crearPerm)
                                                    <div class="custom-control custom-checkbox d-inline-block">
                                                        <input type="checkbox" name="permissions[]" value="{{ $crearPerm['id'] }}" 
                                                               id="perm_{{ $crearPerm['id'] }}" class="custom-control-input permission-checkbox"
                                                               {{ in_array($crearPerm['id'], $userPermissionIds) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $crearPerm['id'] }}"></label>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs">-</span>
                                                @endif
                                            </td>

                                            {{-- Editar --}}
                                            <td class="text-center py-3 align-middle">
                                                @if($editarPerm)
                                                    <div class="custom-control custom-checkbox d-inline-block">
                                                        <input type="checkbox" name="permissions[]" value="{{ $editarPerm['id'] }}" 
                                                               id="perm_{{ $editarPerm['id'] }}" class="custom-control-input permission-checkbox"
                                                               {{ in_array($editarPerm['id'], $userPermissionIds) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $editarPerm['id'] }}"></label>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs">-</span>
                                                @endif
                                            </td>

                                            {{-- Eliminar --}}
                                            <td class="text-center py-3 align-middle">
                                                @if($eliminarPerm)
                                                    <div class="custom-control custom-checkbox d-inline-block">
                                                        <input type="checkbox" name="permissions[]" value="{{ $eliminarPerm['id'] }}" 
                                                               id="perm_{{ $eliminarPerm['id'] }}" class="custom-control-input permission-checkbox"
                                                               {{ in_array($eliminarPerm['id'], $userPermissionIds) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $eliminarPerm['id'] }}"></label>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs">-</span>
                                                @endif
                                            </td>

                                            {{-- Especiales --}}
                                            <td class="py-3 align-middle pr-3">
                                                @if($especiales->count() > 0)
                                                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px 15px;">
                                                        @foreach($especiales as $esp)
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" name="permissions[]" value="{{ $esp['id'] }}" 
                                                                       id="perm_{{ $esp['id'] }}" class="custom-control-input permission-checkbox"
                                                                       {{ in_array($esp['id'], $userPermissionIds) ? 'checked' : '' }}>
                                                                <label class="custom-control-label font-weight-normal text-xs text-dark" for="perm_{{ $esp['id'] }}">
                                                                    <strong>{{ $esp['label'] }}</strong>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs font-italic">Ninguno</span>
                                                @endif
                                            </td>

                                            {{-- Selección Masiva en Fila --}}
                                            <td class="text-center py-3 align-middle">
                                                <div class="custom-control custom-switch d-inline-block">
                                                    <input type="checkbox" class="custom-control-input row-select-all" 
                                                           id="select_all_{{ $groupData['key'] }}">
                                                    <label class="custom-control-label" for="select_all_{{ $groupData['key'] }}" title="Seleccionar/Desmarcar todos en este grupo"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-light p-3 d-flex justify-content-end align-items-center" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; border-top: 1px solid #e5e7eb;">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary px-4 py-2 font-weight-bold mr-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-xs" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Guardar Permisos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table tbody tr:hover {
            background-color: #fafbfd !important;
        }
        .custom-switch .custom-control-label::before {
            background-color: #e5e7eb;
            border-color: #d1d5db;
        }
        .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #1a73e8;
            border-color: #1a73e8;
        }
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #1a73e8;
            border-color: #1a73e8;
        }
        .badge {
            font-weight: 600;
        }
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Función para actualizar el estado del Switch 'Select All' de una fila basado en sus inputs
            function updateRowSwitchState($row) {
                var $checkboxes = $row.find('.permission-checkbox');
                var $switch = $row.find('.row-select-all');
                
                if ($checkboxes.length === 0) {
                    $switch.prop('disabled', true);
                    return;
                }
                
                var checkedCount = $checkboxes.filter(':checked').length;
                
                if (checkedCount === $checkboxes.length) {
                    $switch.prop('checked', true).prop('indeterminate', false);
                } else if (checkedCount > 0) {
                    $switch.prop('checked', false).prop('indeterminate', true);
                } else {
                    $switch.prop('checked', false).prop('indeterminate', false);
                }
            }

            // Inicializar el estado de los switches al cargar la página
            $('.permission-row').each(function() {
                updateRowSwitchState($(this));
            });

            // Manejador del Switch 'Select All' de la fila
            $('.row-select-all').on('change', function() {
                var $row = $(this).closest('.permission-row');
                var isChecked = $(this).prop('checked');
                
                $row.find('.permission-checkbox').prop('checked', isChecked);
                // Remover estado indeterminado si lo tuviera al cambiar directamente el switch
                $(this).prop('indeterminate', false);
            });

            // Manejador al cambiar cualquier checkbox individual
            $('.permission-checkbox').on('change', function() {
                var $row = $(this).closest('.permission-row');
                updateRowSwitchState($row);
            });

            // Botón global 'Seleccionar Todo'
            $('.btn-select-all').on('click', function(e) {
                e.preventDefault();
                $('.permission-checkbox').prop('checked', true);
                $('.row-select-all').prop('checked', true).prop('indeterminate', false);
            });

            // Botón global 'Desmarcar Todo'
            $('.btn-deselect-all').on('click', function(e) {
                e.preventDefault();
                $('.permission-checkbox').prop('checked', false);
                $('.row-select-all').prop('checked', false).prop('indeterminate', false);
            });
        });
    </script>
@stop

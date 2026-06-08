@extends('adminlte::page')

@section('title', 'Rol | ' . $role->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-user-tag text-primary mr-2"></i> Detalles del Rol
            </h1>
            <p class="text-muted mb-0">Visualice la ficha técnica del rol, los permisos concedidos y los usuarios asignados.</p>
        </div>
        <div class="d-flex">
            @can('roles_editar')
                @if($role->name !== 'Superadmin' && $role->id !== 1)
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning font-weight-bold px-3 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                @endif
            @endcan
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary px-3" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Tarjeta de Perfil Documental --}}
            <div class="card p-5 bg-white shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                
                {{-- Encabezado del Perfil --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                    <div>
                        <span class="text-uppercase text-xs font-weight-bold text-secondary tracking-wider" style="letter-spacing: 0.5px;">Ficha del Rol</span>
                        <h3 class="font-weight-bold text-dark mb-1">{{ $role->name }}</h3>
                        <p class="text-muted mb-0"><i class="fas fa-key text-muted mr-1"></i> Total de Permisos: <strong class="text-dark">{{ $role->permissions->count() }}</strong></p>
                    </div>
                    <div class="text-right">
                        @if($role->name === 'Superadmin' || $role->id === 1)
                            <span class="badge px-3 py-2" style="font-size: 0.85rem; border-radius: 20px; background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;"><i class="fas fa-lock mr-1"></i> ROL PROTEGIDO</span>
                        @else
                            <span class="badge px-3 py-2" style="font-size: 0.85rem; border-radius: 20px; background-color: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important;"><i class="fas fa-shield-alt mr-1"></i> PERSONALIZADO</span>
                        @endif
                        <p class="text-xs text-muted mt-2 mb-0">ID Rol: #{{ $role->id }}</p>
                    </div>
                </div>

                {{-- Detalles del Rol y Permisos en Columnas --}}
                <div class="row mb-4">
                    {{-- Columna Izquierda: Información de Registro --}}
                    <div class="col-md-4 border-right">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-info-circle text-info mr-2"></i> Datos Básicos
                        </h6>
                        <table class="table table-borderless table-sm mb-4" style="font-size: 0.875rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width: 45%;">Nombre del Rol:</td>
                                <td class="font-weight-bold text-dark">{{ $role->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Usuarios con el Rol:</td>
                                <td class="text-dark font-weight-bold">{{ $users->count() }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Fecha de Registro:</td>
                                <td class="text-dark">{{ $role->created_at ? $role->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>

                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-users text-info mr-2"></i> Usuarios Asignados ({{ $users->count() }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" style="font-size: 0.875rem;">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="border-0 rounded-left py-2">Nombre</th>
                                        <th class="border-0 rounded-right py-2 text-right">Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td class="py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle text-white d-flex align-items-center justify-content-center mr-2 font-weight-bold" 
                                                         style="width: 24px; height: 24px; font-size: 0.7rem; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <span class="font-weight-bold text-dark text-sm">{{ $user->name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2 text-right text-muted text-xs">{{ $user->email }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-2 text-xs">Sin usuarios asignados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Columna Derecha: Permisos Otorgados --}}
                    <div class="col-md-8 pl-md-4">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-shield-alt text-info mr-2"></i> Permisos de Sistema Otorgados
                        </h6>

                        @if($role->permissions->count() > 0)
                            <div class="row">
                                @foreach($role->permissions->groupBy(function ($permission) {
                                    $parts = explode('_', $permission->name);
                                    return $parts[0] ?? 'other';
                                }) as $group => $groupPermissions)
                                    <div class="col-12 mb-3">
                                        <h6 class="font-weight-bold text-dark mb-2 text-xs" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 4px;">
                                            <i class="fas fa-folder-open text-primary mr-1"></i> Módulo: {{ ucfirst($group) }}
                                        </h6>
                                        <div class="d-flex flex-wrap" style="gap: 6px;">
                                            @foreach($groupPermissions as $permission)
                                                <span class="badge text-muted border bg-light font-weight-normal" 
                                                      style="padding: 0.45em 0.85em; font-size: 0.75rem; border-radius: 6px;">
                                                    <i class="fas fa-check text-success mr-1"></i> {{ str_replace('_', ' ', $permission->name) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @if($role->name === 'Superadmin')
                                <div class="alert alert-success" style="border-radius: 8px;">
                                    <i class="fas fa-check-double mr-1"></i> <strong>Rol Superadmin:</strong> Este rol cuenta con acceso irrestricto y total a todos los módulos y funciones del sistema de forma automática.
                                </div>
                            @else
                                <p class="text-muted text-sm">Este rol no posee permisos específicos asignados.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

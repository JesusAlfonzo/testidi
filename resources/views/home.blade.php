@extends('adminlte::page')

@section('title', 'Bienvenido al Sistema')

@section('content_header')
    <h1 class="m-0 text-dark">Panel de Inicio</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-clock mr-2"></i> Estado de la Cuenta
                    </h3>
                </div>

                <div class="card-body text-center py-5">
                    <i class="fas fa-user-shield fa-4x text-warning mb-4"></i>
                    
                    <h3>¡Hola, {{ Auth::user()->name }}!</h3>
                    <p class="lead mt-3">
                        Tu cuenta ha sido creada exitosamente y estás dentro del sistema <strong>SGCI-IDI</strong>.
                    </p>
                    
                    <div class="alert alert-light border mt-4 text-left d-inline-block p-4">
                        <h5><i class="icon fas fa-info-circle text-info"></i> ¿Qué sucede ahora?</h5>
                        <p>
                            Actualmente tu usuario <strong>no tiene un rol asignado</strong> con permisos operativos.
                            <br>
                            Por motivos de seguridad, un <strong>Administrador</strong> debe activar tus permisos para que puedas acceder a los módulos de:
                        </p>
                        <ul class="mb-0">
                            <li>Solicitudes de Insumos</li>
                            <li>Gestión de Inventario</li>
                            <li>Reportes</li>
                        </ul>
                    </div>

                    <p class="mt-4 text-muted">
                        Si crees que esto es un error o necesitas acceso urgente, por favor contacta al departamento de sistemas o a tu supervisor.
                    </p>
                </div>
                
                <div class="card-footer text-center">
                    <small class="text-muted">Sistema de Gestión de Inventario - Instituto de Inmunología Dr. Nicolás Bianco</small>
                </div>
            </div>
        </div>
    </div>
@stop
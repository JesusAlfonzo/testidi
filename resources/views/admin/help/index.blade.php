@extends('adminlte::page')

@section('title', 'Ayuda - SGCI-IDI')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-question-circle"></i> Centro de Ayuda</h1>
        <span class="badge badge-lg badge-primary">{{ ucfirst($role) }}</span>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info" style="border-left: 4px solid #3b82f6;">
                <h5><i class="fas fa-info-circle"></i> Bienvenido al Centro de Ayuda</h5>
                <p class="mb-0">Aquí encontrará manuales y guías según su rol: <strong>{{ ucfirst($role) }}</strong></p>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($modules as $key => $module)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-outline" style="border-left: 4px solid #3b82f6; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <h3 class="card-title text-white">
                            <i class="{{ $module['icon'] }}"></i> {{ $module['title'] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $module['description'] }}</p>
                    </div>
                    <div class="card-footer" style="background-color: #f8f9fa;">
                        <a href="{{ route('admin.help.show', $module['section']) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-book-open"></i> Ver Guía
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-download"></i> Manual Completo
                    </h3>
                </div>
                <div class="card-body">
                    <p>¿Necesita el manual completo para imprimir o guardar?</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.help.download', 'general') }}" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </a>
                        <a href="{{ route('admin.help.download', $role) }}" class="btn btn-info">
                            <i class="fas fa-file-alt"></i> Descargar Guía de Rol
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12 text-center text-muted">
            <small>
                <i class="fas fa-question-circle"></i> ¿Necesita más ayuda? Contacte al administrador del sistema
            </small>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        .card .card-header {
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
    </style>
@stop

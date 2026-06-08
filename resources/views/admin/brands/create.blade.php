@extends('adminlte::page')

@section('title', 'Maestros | Crear Marca')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-tag text-primary mr-2"></i> Crear Marca
            </h1>
            <p class="text-muted mb-0">Registre una nueva marca comercial para catalogar productos.</p>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-7 col-md-9 mx-auto">
            <div class="card p-4 bg-white" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    
                    <h5 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-info-circle text-info mr-2"></i> Datos de la Marca
                    </h5>
                    
                    <div class="form-group mb-3">
                        <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Nombre de la Marca <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-tag text-muted"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Ej: Baxter, Dell, HP, Siemens" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="website" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Sitio Web <span class="text-muted">(Opcional)</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-link text-muted"></i></span>
                            </div>
                            <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" 
                                   value="{{ old('website') }}" placeholder="Ej: https://www.marca.com" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                        @error('website')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <hr style="border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Guardar Marca
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

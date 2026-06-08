@extends('adminlte::page')

@section('title', 'Maestros | Editar Categoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-layer-group text-primary mr-2"></i> Editar Categoría
            </h1>
            <p class="text-muted mb-0">Actualice la información de la categoría <strong>{{ $category->name }}</strong>.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-7 col-md-9 mx-auto">
            <div class="card p-4 bg-white" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-edit text-warning mr-2"></i> Actualizar Datos de la Categoría
                    </h5>
                    
                    <div class="form-group mb-3">
                        <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Nombre de la Categoría <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-tag text-muted"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $category->name) }}" placeholder="Ej: Reactivos de Inmunología, Insumos Generales" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Descripción <span class="text-muted">(Opcional)</span>
                        </label>
                        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="Escriba brevemente qué tipo de productos agrupa esta categoría y su propósito..." 
                                  style="border-radius: 8px;">{{ old('description', $category->description) }}</textarea>
                        @error('description')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <hr style="border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Actualizar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

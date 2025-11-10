@extends('adminlte::page')

@section('title', 'Maestros | Crear Categoría')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Categoría</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-folder-plus"></i> Detalle de la Categoría</h3>
                </div>

                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Nombre --}}
                        <div class="form-group">
                            <label for="name">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Insumos de Laboratorio, Equipos de Oficina" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="form-group">
                            <label for="description">Descripción (Opcional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Detalles sobre qué tipo de productos agrupa esta categoría.">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Categoría
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

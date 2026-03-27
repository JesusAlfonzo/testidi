@extends('adminlte::page')

@section('title', 'Crear Marca')

@section('content_header')
    <h1>Crear Nueva Marca</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-star"></i> Datos de la Marca
                    </h3>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Ej: Siemens, Baxter, Dell" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="website">Sitio Web (Opcional)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white"><i class="fas fa-globe"></i></span>
                                        </div>
                                        <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" id="website" placeholder="Ej: https://www.marca.com" value="{{ old('website') }}">
                                    </div>
                                    @error('website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Marca
                        </button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Editar Marca')

@section('content_header')
    <h1>Editar Marca: {{ $brand->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-edit"></i> Modificar Datos
                    </h3>
                </div>
                <form action="{{ route('admin.brands.update', $brand) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-dark"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Nombre" value="{{ old('name', $brand->name) }}" required>
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
                                            <span class="input-group-text bg-warning text-dark"><i class="fas fa-globe"></i></span>
                                        </div>
                                        <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" id="website" placeholder="Ej: https://www.marca.com" value="{{ old('website', $brand->website) }}">
                                    </div>
                                    @error('website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Marca
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

@extends('adminlte::page')

@section('title', 'Crear Marca')

@section('content_header')
    <h1>Crear Nueva Marca</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Marca</h3>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nombre (*)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Ej: Siemens, Baxter, Dell" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="website">Sitio Web (Opcional)</label>
                            <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" id="website" placeholder="Ej: https://www.marca.com" value="{{ old('website') }}">
                            @error('website')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Marca</button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

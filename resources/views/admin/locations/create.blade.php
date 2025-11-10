@extends('adminlte::page')

@section('title', 'Crear Ubicación')

@section('content_header')
    <h1>Crear Nueva Ubicación</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de la Ubicación</h3>
                </div>
                <form action="{{ route('admin.locations.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nombre (*)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Ej: Almacén Principal, Anaquel A1" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="details">Detalles</label>
                            <textarea name="details" class="form-control @error('details') is-invalid @enderror" id="details" rows="3" placeholder="Descripción o detalles específicos de la ubicación. (Ej: Zona de congelación)">{{ old('details') }}</textarea>
                            @error('details')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Ubicación</button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

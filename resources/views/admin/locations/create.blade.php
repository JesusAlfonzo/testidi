@extends('adminlte::page')

@section('title', 'Crear Ubicación')

@section('content_header')
    <h1>Crear Nueva Ubicación</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-map-marker-alt"></i> Datos de la Ubicación
                    </h3>
                </div>
                <form action="{{ route('admin.locations.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre (*)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-purple text-white"><i class="fas fa-warehouse"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Ej: Almacén Principal, Anaquel A1" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="details">Detalles</label>
                                    <textarea name="details" class="form-control @error('details') is-invalid @enderror" id="details" rows="3" placeholder="Descripción o detalles específicos de la ubicación. (Ej: Zona de congelación)">{{ old('details') }}</textarea>
                                    @error('details')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Ubicación
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Maestros | Crear Unidad')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Unidad de Medida</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #06b6d4;">
                <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-ruler"></i> Detalle de la Unidad
                    </h3>
                </div>

                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-weight-hanging"></i></span>
                                        </div>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Kilogramo, Litro, Unidad" required>
                                    </div>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="abbreviation">Abreviatura <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-compress-alt"></i></span>
                                        </div>
                                        <input type="text" name="abbreviation" id="abbreviation" class="form-control @error('abbreviation') is-invalid @enderror" value="{{ old('abbreviation') }}" placeholder="Ej: Kg, Lt, U" required>
                                    </div>
                                    @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> Guardar Unidad
                        </button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

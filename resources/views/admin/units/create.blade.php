@extends('adminlte::page')

@section('title', 'Maestros | Crear Unidad')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Nueva Unidad de Medida</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus-circle"></i> Detalle de la Unidad</h3>
                </div>

                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Nombre --}}
                        <div class="form-group">
                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Kilogramo, Litro, Unidad" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Abreviatura --}}
                        <div class="form-group">
                            <label for="abbreviation">Abreviatura <span class="text-danger">*</span></label>
                            <input type="text" name="abbreviation" id="abbreviation" class="form-control @error('abbreviation') is-invalid @enderror" value="{{ old('abbreviation') }}" placeholder="Ej: Kg, Lt, U" required>
                            @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
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

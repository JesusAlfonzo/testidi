@extends('adminlte::page')

@section('title', 'Maestros | Editar Unidad')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Unidad: <strong>{{ $unit->name }}</strong> ({{ $unit->abbreviation }})</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit"></i> Actualizar Datos de Unidad</h3>
                </div>

                <form action="{{ route('admin.units.update', $unit) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        {{-- Nombre --}}
                        <div class="form-group">
                            <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Abreviatura --}}
                        <div class="form-group">
                            <label for="abbreviation">Abreviatura <span class="text-danger">*</span></label>
                            <input type="text" name="abbreviation" id="abbreviation" class="form-control @error('abbreviation') is-invalid @enderror" value="{{ old('abbreviation', $unit->abbreviation) }}" required>
                            @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync-alt"></i> Actualizar Unidad
                        </button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
